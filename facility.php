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
require_once('./forms.php');
require_once($CFG->dirroot.'/lib/adminlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

admin_externalpage_setup('local_meditraxcohort_facility');

$systemcontext  = context_system::instance();
$strpluginname  = get_string('pluginname', 'local_meditraxcohort');

require_capability('local/meditraxcohort:manage', $systemcontext);

$whatform       = optional_param('whatform', 'add', PARAM_TEXT);
$cohortid       = optional_param('cohort', 0, PARAM_INT);
$add            = optional_param('add', false, PARAM_BOOL);
$remove         = optional_param('remove', false, PARAM_BOOL);

$returnurl      = $CFG->wwwroot.'/local/meditraxcohort/facility.php';
$title          = get_string('page:title', 'local_meditraxcohort');

$PAGE->set_url($returnurl);
$PAGE->set_context($systemcontext);
$PAGE->set_title($title);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($title);

// Deleting a suburb?
/*
if (!empty($delete)) {
    auth_watereff_delete_profile_field_data($deletedata);
}
*/
$meditraxcohort         = new local_plugin_meditraxcohort;
$cohortadd              = $meditraxcohort->meditraxcohort_add_form($returnurl);
$cohortdisplaysubmitted = false;
$hrstyle                = array('style' => "border-top: 1px solid #aaa; padding-bottom:15px;width:99%;");

// Form processing
if ($whatform == 'add') {
    if ($cohortadd->is_cancelled()) { // Form cancelled?
        redirect(new moodle_url($returnurl));
        exit;
    } else if ($data = $cohortadd->get_data()) { // Form submitted?

        if ($data->name and $data->idnumber) {

            $params = array($data->name, $data->idnumber);

            $sql = "
                SELECT *
                FROM {cohort}
                WHERE name = ?
                OR idnumber = ?
            ";
            if (!($cohortfound = $DB->get_record_sql($sql, $params))) {

                $addcohort                      = new stdClass();
                $addcohort->contextid           = 'overview';
                $addcohort->name                = $data->name;
                $addcohort->idnumber            = $data->idnumber;
                $addcohort->contextid           = '1';
                $addcohort->description         = '';
                $addcohort->descriptionformat   = '1';
                $addcohort->visible             = '1';
                $addcohort->component           = '';
                $addcohort->timecreated         = time();
                $addcohort->timemodified        = time();
                $lastinsertid                   = $DB->insert_record('cohort', $addcohort, false);
            } else {
                $strcontinue = get_string('error:add:cohort', 'local_meditraxcohort');
            }
        }
    }
}

$cohorttopic            = $meditraxcohort->meditraxcohort_topic_form($returnurl, $cohortid);

if ($whatform == 'topic') {

    if ($cohorttopic->is_cancelled()) { // Form cancelled?
        redirect(new moodle_url($returnurl));
        exit;
    } else if ($data = $cohorttopic->get_data()) { // Form submitted?

        $cohort = $DB->get_record('cohort', array('id' => $cohortid));

        $addselect = null;
        $removeselect = null;

        $form_vars = $_POST;

        if ($form_vars) {
            foreach ($form_vars as $key => $value) {
                if (preg_match("/addselect/", $key, $matches)) {
                    $addselect = $value;
                    break;
                }
                if (preg_match("/removeselect/", $key, $matches)) {
                    $removeselect = $value;
                    break;
                }
            }
        }

        if ($add) {

            try {
                $transaction = $DB->start_delegated_transaction();

                $groupid = 0;
                $enrolid = 0;

                foreach ($addselect as $courseid) {
             //      echo "add course $courseid to cohort $cohortid<br>";

                    $groupid = $meditraxcohort->meditrax_add_group($courseid, $cohort);
                    $enrolid = $meditraxcohort->meditrax_add_enrol($courseid, $cohort);

                    $sql = "
                        #bevancohort
                        SELECT *
                        FROM {cohort_members}
                        WHERE cohortid  = ?
                    ";
//echo "cohortid-".$cohortid;
                    if ($cohortmembers = $DB->get_records_sql($sql, array($cohortid))) {
//print_object($cohortmembers);
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

        if ($remove) {

            try {
                $transaction = $DB->start_delegated_transaction();

                foreach ($removeselect as $courseid) {

                    $params = array('enrol'     => 'cohort',
                                    'courseid'  => $courseid,
                                    'name'      => $cohort->idnumber);

                    // Find the enrolment id to delete user enrolments
                    $enrolid = $DB->get_field('enrol', 'id', $params);
                    $groupid = $DB->get_field('groups', 'id', array('courseid'  => $courseid, 'idnumber' => $cohort->idnumber));

                    $DB->delete_records('user_enrolments', array('enrolid' => $enrolid));
                    $DB->delete_records('enrol', $params);
                    $DB->delete_records('groups_members', array('id' => $groupid, 'component' => $cohort->idnumber));

                   // echo "remove course $courseid from cohort<br>";
                }

                $transaction->allow_commit();
            } catch(Exception $e) {
                 $transaction->rollback($e);
            }
        }
  //     redirect(new moodle_url($returnurl."?whatform=display&cohort=$cohortid"));
    }
}

$cohortdisplay = $meditraxcohort->meditraxcohort_display_form($returnurl, $cohortid);

if ($whatform == 'display') {

    if ($cohortdisplay->is_cancelled()) { // Form cancelled?
        redirect(new moodle_url($returnurl));
        exit;
    } else if ($data = $cohortdisplay->get_data()) { // Form submitted?

    }
    if ($cohortid) $cohortdisplaysubmitted = true;
}

// Page display
echo $OUTPUT->header();
$cohortadd->display();
echo html_writer::tag('div', '', $hrstyle);
$cohortdisplay->display();
echo html_writer::tag('div', '', $hrstyle);
if ($cohortdisplaysubmitted)    $cohorttopic->display();
echo $OUTPUT->footer();