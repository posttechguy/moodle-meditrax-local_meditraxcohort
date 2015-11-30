<?php
/**
 * Custom cohort enrolment for Meditrax Facility plugin
 *
 * Manage facility information
 *
 * @package    local_meditraxcohort
 * @author     Bevan Holman <bevan@pukunui.com>, Pukunui
 * @copyright  2015 onwards, Pukunui
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('./locallib.php');
require_once('./forms.php');
require_once($CFG->dirroot.'/lib/adminlib.php');

admin_externalpage_setup('local_meditraxcohort_topic');

$systemcontext = context_system::instance();
$strpluginname = get_string('pluginname', 'local_meditraxcohort');

require_capability('local/meditraxcohort:manage', $systemcontext);

$whatform   = optional_param('whatform', 'display', PARAM_TEXT);
$courseid   = optional_param('courseid', 0, PARAM_INT);
$add        = optional_param('add', false, PARAM_BOOL);

$returnurl  = $CFG->wwwroot.'/local/meditraxcohort/topic.php';
$title      = get_string('page:title', 'local_meditraxcohort');

$PAGE->set_url($returnurl);
$PAGE->set_context($systemcontext);
$PAGE->set_title($title);
$PAGE->set_pagelayout('report');
$PAGE->set_heading($title);

// Deleting a suburb?
/*
if (!empty($delete)) {
    auth_watereff_delete_profile_field_data($deletedata);
}
*/

$meditraxcohort         = new local_plugin_meditraxcohort;

$coursecohort           = $meditraxcohort->meditraxcohort_topic_cohort_form($returnurl, $courseid);
$coursedisplaysubmitted = false;
$hrstyle                = array('style' => "border-top: 1px solid #aaa; padding-bottom:10px;15px;width:99%;");

// Form processing
if ($whatform == 'cohort') {

    if ($coursecohort->is_cancelled()) { // Form cancelled?
        redirect(new moodle_url($returnurl));
        exit;
    } else if ($data = $coursecohort->get_data()) { // Form submitted?

        $course = $DB->get_record('course', array('id' => $courseid));

        $addselect = null;
        $removeselect = null;

        $form_vars = $_POST;

        if ($form_vars) {
            foreach ($form_vars as $key => $value) {
                if (preg_match("/addselect/", $key, $matches)) {
                    $addselect = $value;
                    break;
                }
            }
        }

        if ($add) {

            try {
                $transaction = $DB->start_delegated_transaction();

                $enrolid = 0;
                $groupid = 0;

                foreach ($addselect as $cohortid) {
             //       echo "add course $courseid to cohort $cohortid<br>";

                    $cohort = $DB->get_record('cohort', array('id' => $cohortid));

                    $groupid = $meditraxcohort->meditrax_add_group($courseid, $cohort);
                    $enrolid = $meditraxcohort->meditrax_add_enrol($courseid, $cohort);

                    $sql = "
                        SELECT *
                        FROM {cohort_members}
                        WHERE cohortid  = ?
                    ";

                    if ($cohortmembers = $DB->get_records_sql($sql, array($cohortid))) {

                        foreach ($cohortmembers as $user) {

                            $meditraxcohort->meditrax_add_user_enrolment($enrolid, $user);
                            $meditraxcohort->meditrax_add_group_member($groupid, $enrolid, $user, $cohort);
                        }
                    }
                }
                $transaction->allow_commit();
            } catch(Exception $e) {
                 $transaction->rollback($e);
            }

        }
      redirect(new moodle_url($returnurl."?whatform=display&courseid=$courseid"));
        //  echo "<a href='".$returnurl."?whatform=display&courseid=$courseid"."'>go</a>";
    }
}

$coursedisplay = $meditraxcohort->meditraxcohort_topic_display_form($returnurl, $courseid);

if ($whatform == 'display') {

    if ($coursedisplay->is_cancelled()) { // Form cancelled?
        redirect(new moodle_url($returnurl));
        exit;
    } else if ($data = $coursedisplay->get_data()) { // Form submitted?
        if ($courseid) $coursedisplaysubmitted = true;
    }

}

// Page display
echo $OUTPUT->header();
$coursedisplay->display();
echo html_writer::tag('div', '', $hrstyle);
if ($coursedisplaysubmitted or $courseid)    $coursecohort->display();
echo $OUTPUT->footer();