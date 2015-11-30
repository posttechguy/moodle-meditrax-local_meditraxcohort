<?php
/**
 * Custom cohort enrolment for Meditrax Facility plugin
 *
 * Form definitions
 *
 * @package    local_meditraxcohort
 * @author     Bevan Holman <bevan@pukunui.com>, Pukunui
 * @copyright  2015 onwards, Pukunui
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

class local_meditraxcohort_add_form extends moodleform {

    /**
     * Define the form
     */
    public function definition() {
        global $DB;

        $mform =& $this->_form;
        $strrequired = get_string('required');

        $mform->addElement('hidden', 'whatform');
        $mform->setType('whatform', PARAM_TEXT);
        $mform->setDefault('whatform', 'add');

        $mform->addElement('text', 'name', get_string('formlabel:name', 'local_meditraxcohort'), null);
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', get_string('formvalidation:name', 'local_meditraxcohort'), 'required', null, 'server');
        $mform->setDefault('name', '');

        $mform->addElement('text', 'idnumber', get_string('formlabel:idnumber', 'local_meditraxcohort'), null);
        $mform->setType('idnumber', PARAM_NOTAGS);
        $mform->addRule('idnumber', get_string('formvalidation:idnumber', 'local_meditraxcohort'), 'required', null, 'server');
        $mform->setDefault('idnumber', '');

        $this->add_action_buttons(false, get_string('button:add', 'local_meditraxcohort'));
    }

    /**
     * Validate the form submission
     *
     * @param array $data  submitted form data
     * @param array $files submitted form files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;

        $error = array();

        $name = trim($data['name']);
        if (strlen($name) < 1) {
            $error['name'] = get_string('formvalidation:name', 'local_meditraxcohort');
        } else {
            if ($DB->record_exists("cohort", array("name" =>  $name))) {
                $error['name'] = get_string('dbvalidation:name', 'local_meditraxcohort');
            }
        }
        $idnumber = trim($data['idnumber']);
        if (strlen($idnumber) < 1) {
            $error['idnumber'] = get_string('formvalidation:idnumber', 'local_meditraxcohort');
        } else {
            if ($DB->record_exists("cohort", array("idnumber" => $idnumber))) {
                $error['idnumber'] = get_string('dbvalidation:idnumber', 'local_meditraxcohort');
            }
        }
        return (count($error) == 0) ? true : $error;
    }
}

class local_meditraxcohort_display_form extends moodleform {

    /**
     * Define the form
     */
    public function definition() {
        global $DB;

        $mform =& $this->_form;
        $cohort = $this->_customdata['cohort'];
        $strrequired = get_string('required');

        $mform->addElement('hidden', 'whatform');
        $mform->setType('whatform', PARAM_TEXT);
        $mform->setDefault('whatform', 'display');

        $cohorts = array();
        $cohorts[0] = get_string('form:element:default:cohort', 'local_meditraxcohort');
        $cohorts += $DB->get_records_select_menu('cohort', '', null, 'name', 'id, name');

        $mform->addElement('select', 'cohort', get_string('form:element:label:cohort', 'local_meditraxcohort'), $cohorts);
        $mform->setDefault('cohort', $cohort);

        $this->add_action_buttons(false, get_string('button:display', 'local_meditraxcohort'));
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Validate the form submission
     *
     * @param array $data  submitted form data
     * @param array $files submitted form files
     * @return array
     */
    public function validation($data, $files) {
    }
}

class local_meditraxcohort_topic_form extends moodleform {

    /**
     * Define the form
     */
    public function definition() {
        global $DB, $OUTPUT;

        $mform =& $this->_form;
        $cohort = $this->_customdata['cohort'];
        $strrequired = get_string('required');

        $cohortname = $DB->get_field('cohort',  'name',  array('id' => $cohort));

        $mform->addElement('hidden', 'whatform');
        $mform->setType('whatform', PARAM_TEXT);
        $mform->setDefault('whatform', 'topic');

        $mform->addElement('hidden', 'cohort');
        $mform->setType('cohort', PARAM_INT);
        $mform->setDefault('cohort', $cohort);

        $sql = "
            SELECT c.id, c.fullname
            FROM {cohort} as h
            JOIN {context} ctx ON ctx.id = h.contextid
            JOIN {enrol} AS e ON h.id = e.customint1
            JOIN {course} AS c ON e.courseid = c.id
            WHERE h.id = :cohort
            AND c.visible = 1
            GROUP BY 1
            ORDER BY c.fullname
        ";
        $coursesincohort = $DB->get_records_sql_menu($sql, array("cohort" => $cohort));
        $coursesavailable = $DB->get_records_select_menu('course', 'category > 0 and visible = 1', null, 'fullname', 'id, fullname');

        foreach ($coursesincohort as $id => $course) {
            if (array_key_exists($id, $coursesavailable)) unset($coursesavailable[$id]);
        }
        $larrowtext = $OUTPUT->larrow().'&nbsp;'.s(get_string('add'));
        $rarrowtext = $OUTPUT->rarrow().'&nbsp;'.s(get_string('remove'));

        $mform->addElement('html',  html_writer::start_tag('style'));
        $mform->addElement('html',  '   table.addremovetable div.fitemtitle { display:none; }');
        $mform->addElement('html',  '   #buttonscell { width:30%; display:inline-block;float:left !important; min-height:215px; }');
        $mform->addElement('html',  '   #existingcell { width:25%; display:inline-block;float:left !important; min-height:215px;}');
        $mform->addElement('html',  '   #potentialcell { display:inline-block;float:left !important; min-height:215px;}');
        $mform->addElement('html',  '   #removecontrols input#remove, #addcontrols input#add { width: 90px; display:inline-block;float:left; }');
        $mform->addElement('html',  '   .fitemtitle {display:none;}');
        $mform->addElement('html',  '   #fitem_id_addselect .fselect {   }');
        $mform->addElement('html',  '   #fitem_id_removeselect .fselect {  margin-left:0;  }');
        $mform->addElement('html',  '    #id_addselect { width:300px; min-height:215px;float:right; }');
        $mform->addElement('html',  '   #id_removeselect { width:300px; min-height:215px; }');
        $mform->addElement('html',  '   .addlabel { text-align:right; width:100%; }');
        $mform->addElement('html',  '   .larrow, .rarrow { width:90px; clear: left; margin:25px; display:inline-block;float:left;  }');
        $mform->addElement('html',  '   .meditraxselect, .facilityfooter { clear: left; width: 80%;}');
        $mform->addElement('html',  html_writer::end_tag('style'));
        $mform->addElement('html',  html_writer::start_tag('div', array('class' => 'groupmanagementtable boxaligncenter cohortselector meditraxselect')));
        $mform->addElement('html',      html_writer::start_tag('div', array('id' => 'existingcell')));
        $mform->addElement('html',          html_writer::start_tag('label', array('for' => 'removeselect')));
        $mform->addElement('html',              get_string('form:element:label:coursesincohort', 'local_meditraxcohort', $cohortname));
        $mform->addElement('html',          html_writer::end_tag('label'));
        $mform->addElement('html',          html_writer::start_tag('div', array('class' => 'felement fselect')));
        $mform->addElement('html',              '<select multiple="multiple" name="removeselect[]" id="id_removeselect">');
                                                    foreach ($coursesincohort as $id => $cohort)
                                                    {
                                                        $mform->addElement('html', '<option value="'.$id.'">'.$cohort.'</option>');
                                                    }
        $mform->addElement('html',              html_writer::end_tag('select'));

        $mform->addElement('html',          html_writer::end_tag('div'));
        $mform->addElement('html',      html_writer::end_tag('div'));
        $mform->addElement('html',      html_writer::start_tag('div', array('id' => 'buttonscell')));
        $mform->addElement('html',          html_writer::start_tag('div', array('id' => 'addcontrols')));
        $mform->addElement('html',              html_writer::tag('input', '', array('name' => 'add', 'id' => 'add', 'type' => 'submit', 'value' =>  html_entity_decode($larrowtext), 'class' => 'larrow')));
        $mform->addElement('html',          html_writer::end_tag('div'));
        $mform->addElement('html',          html_writer::start_tag('div', array('id' => 'removecontrols')));
        $mform->addElement('html',              html_writer::tag('input', '', array('name' => 'remove', 'id' => 'remove', 'type' => 'submit', 'value' => html_entity_decode($rarrowtext), 'class' => 'rarrow')));
        $mform->addElement('html',          html_writer::end_tag('div'));
        $mform->addElement('html',      html_writer::end_tag('div'));
        $mform->addElement('html',      html_writer::start_tag('div', array('id' => 'potentialcell')));
        $mform->addElement('html',          html_writer::start_tag('label', array('for' => 'addselect', 'class' => 'addlabel')));
        $mform->addElement('html',              get_string('form:element:label:coursesavailable', 'local_meditraxcohort'));
        $mform->addElement('html',          html_writer::end_tag('label'));
        $mform->addElement('html',          html_writer::start_tag('div', array('class' => 'felement fselect')));
        $mform->addElement('html',              '<select multiple="multiple" name="addselect[]" id="id_addselect">');
                                                    foreach ($coursesavailable as $id => $cohort)
                                                    {
                                                        $mform->addElement('html', '<option value="'.$id.'" title="'.$cohort.'">'.$cohort.'</option>');
                                                    }
                                                $mform->addElement('html',          html_writer::end_tag('select'));

        $mform->addElement('html',          html_writer::end_tag('div'));
        $mform->addElement('html',      html_writer::end_tag('div'));
        $mform->addElement('html',      get_string('facility:footer:text', 'local_meditraxcohort'));
        $mform->addElement('html',  html_writer::end_tag('div'));
    }

    /**
     * Validate the form submission
     *
     * @param array $data  submitted form data
     * @param array $files submitted form files
     * @return array
     */
    public function validation($data, $files) {
    }
}

class local_meditraxcohort_topic_display_form extends moodleform {

    /**
     * Define the form
     */
    public function definition() {
        global $DB;

        $mform =& $this->_form;
        $courseid = $this->_customdata['courseid'];
        $strrequired = get_string('required');

        $courses = array();
        $courses[0] = get_string('form:element:default:course', 'local_meditraxcohort');
        $courses += $DB->get_records_select_menu('course', 'visible = 1', null, 'fullname', 'id, fullname');

        $mform->addElement('hidden', 'whatform');
        $mform->setType('whatform', PARAM_TEXT);
        $mform->setDefault('whatform', 'display');

        $mform->addElement('select', 'courseid', get_string('form:element:label:course', 'local_meditraxcohort'), $courses);
        $mform->setDefault('courseid', $courseid);
        $this->add_action_buttons(false, get_string('button:select', 'local_meditraxcohort'));

        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Validate the form submission
     *
     * @param array $data  submitted form data
     * @param array $files submitted form files
     * @return array
     */
    public function validation($data, $files) {
    }
}

class local_meditraxcohort_topic_cohort_form extends moodleform {

    /**
     * Define the form
     */
    public function definition() {
        global $DB, $OUTPUT;

        $mform =& $this->_form;
        $courseid = $this->_customdata['courseid'];
        $strrequired = get_string('required');

        $coursename = $DB->get_field('course',  'fullname',  array('id' => $courseid));

        $mform->addElement('hidden', 'whatform');
        $mform->setType('whatform', PARAM_TEXT);
        $mform->setDefault('whatform', 'cohort');

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $courseid);

        $sql = "
            SELECT h.id, h.name
            FROM {cohort} as h
            JOIN {context} ctx ON ctx.id = h.contextid
            JOIN {enrol} AS e ON h.id = e.customint1
            JOIN {course} AS c ON e.courseid = c.id
            WHERE c.id = :courseid
            AND c.visible = 1
            GROUP BY 1
            ORDER BY h.name
        ";
        $coursesbycohort = $DB->get_records_sql_menu($sql, array("courseid" => $courseid));
        $cohortsavailable = $DB->get_records_select_menu('cohort', '', null, 'name', 'id, name');

        foreach ($coursesbycohort as $id => $course) {
            if (array_key_exists($id, $cohortsavailable)) unset($cohortsavailable[$id]);
        }
        natcasesort($cohortsavailable);

        $larrowtext = $OUTPUT->larrow().'&nbsp;'.s(get_string('add'));
        $rarrowtext = $OUTPUT->rarrow().'&nbsp;'.s(get_string('remove'));

        $mform->addElement('html',  html_writer::start_tag('style'));
        $mform->addElement('html',  '   table.addremovetable div.fitemtitle { display:none; }');
        $mform->addElement('html',  '   #buttonscell { width:30%; display:inline-block;float:left !important; min-height:215px; }');
        $mform->addElement('html',  '   #existingcell { width:25%; display:inline-block;float:left !important; min-height:215px;}');
        $mform->addElement('html',  '   #potentialcell { display:inline-block;float:left !important; min-height:215px;}');
        $mform->addElement('html',  '   #removecontrols input#remove, #addcontrols input#add { width: 90px; display:inline-block;float:left; }');
        $mform->addElement('html',  '   .fitemtitle {display:none;}');
        $mform->addElement('html',  '   #fitem_id_addselect .fselect {   }');
        $mform->addElement('html',  '   #fitem_id_removeselect .fselect {  margin-left:0;  }');
        $mform->addElement('html',  '    #id_addselect { width:300px; min-height:215px;float:right; }');
        $mform->addElement('html',  '   #id_removeselect { width:300px; min-height:215px; }');
        $mform->addElement('html',  '   .addlabel { text-align:right; width:100%; }');
        $mform->addElement('html',  '   .larrow { width:90px; clear: left; margin:25px; display:inline-block;float:left;  }');
        $mform->addElement('html',  '   .meditraxselect, .facilityfooter { clear: left; width: 80%;}');
        $mform->addElement('html',  html_writer::end_tag('style'));
        $mform->addElement('html',  html_writer::start_tag('div', array('class' => 'groupmanagementtable boxaligncenter cohortselector meditraxselect')));
        $mform->addElement('html',      html_writer::start_tag('div', array('id' => 'existingcell')));
        $mform->addElement('html',          html_writer::start_tag('label', array('for' => 'removeselect')));
        $mform->addElement('html',              get_string('form:element:label:coursesbycohort', 'local_meditraxcohort', $coursename));
        $mform->addElement('html',          html_writer::end_tag('label'));
        $mform->addElement('html',          html_writer::start_tag('div', array('class' => 'felement fselect')));
        $mform->addElement('html',              html_writer::start_tag('select', array('name' => 'removeselect[]', 'multiple' => 'multiple', 'id' => 'id_removeselect')));
        foreach ($coursesbycohort as $id => $cohort)
        {
            $mform->addElement('html',              '<option value="'.$id.'">'.$cohort.'</option>');
        }
        $mform->addElement('html',              html_writer::end_tag('select'));
        $mform->addElement('html',          html_writer::end_tag('div'));
        $mform->addElement('html',      html_writer::end_tag('div'));
        $mform->addElement('html',      html_writer::start_tag('div', array('id' => 'buttonscell')));
        $mform->addElement('html',          html_writer::start_tag('div', array('id' => 'addcontrols')));
        $mform->addElement('html',              html_writer::tag('input', '', array('name' => 'add', 'id' => 'add', 'type' => 'submit', 'value' =>  html_entity_decode($larrowtext), 'class' => 'larrow')));
        $mform->addElement('html',          html_writer::end_tag('div'));
        $mform->addElement('html',      html_writer::end_tag('div'));
        $mform->addElement('html',      html_writer::start_tag('div', array('id' => 'potentialcell')));
        $mform->addElement('html',          html_writer::start_tag('label', array('for' => 'addselect', 'class' => 'addlabel')));
        $mform->addElement('html',              get_string('form:element:label:cohortsavailable', 'local_meditraxcohort'));
        $mform->addElement('html',          html_writer::end_tag('label'));
        $mform->addElement('html',          html_writer::start_tag('div', array('class' => 'felement fselect')));
        $mform->addElement('html',              html_writer::start_tag('select', array('name' => 'addselect[]', 'multiple' => 'multiple', 'id' => 'id_addselect')));
        foreach ($cohortsavailable as $id => $cohort)
        {
            $mform->addElement('html',              '<option value="'.$id.'">'.$cohort.'</option>');
        }
        $mform->addElement('html',              html_writer::end_tag('select'));
        $mform->addElement('html',          html_writer::end_tag('div'));
        $mform->addElement('html',      html_writer::end_tag('div'));
        $mform->addElement('html',      get_string('topic:footer:text', 'local_meditraxcohort'));
        $mform->addElement('html',  html_writer::end_tag('div'));
    }

    /**
     * Validate the form submission
     *
     * @param array $data  submitted form data
     * @param array $files submitted form files
     * @return array
     */
    public function validation($data, $files) {
    }
}