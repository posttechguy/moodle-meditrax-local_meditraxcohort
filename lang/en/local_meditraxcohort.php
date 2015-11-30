<?php
/**
 * Custom cohort enrolment for Meditrax Facility plugin
 *
 * String definitions
 *
 * @package    local_meditraxcohort
 * @author     Bevan Holman <bevan@pukunui.com>, Pukunui
 * @copyright  2015 onwards, Pukunui
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'Meditrax Cohort Management';
$string['page:title'] = 'Meditrax Facilites';
$string['topic:link:add'] = 'Manage Topics';
$string['facility:link:add'] = 'Manage Facilities';
$string['local/meditraxcohort:manage'] = 'Meditrax - Manage Facilities/Topics';

$string['formlabel:name'] = 'Facility name';
$string['formvalidation:name'] = 'Missing facility name';
$string['formlabel:idnumber'] = 'Facility ID';
$string['formvalidation:idnumber'] = 'Missing facility ID';
$string['button:add'] = 'Add';

$string['dbvalidation:name'] = 'Facility name already exists';
$string['dbvalidation:idnumber'] = 'Facility ID already exists';


$string['form:element:default:cohort'] = 'Existing facilities ....';
$string['form:element:label:cohort'] = 'Select an existing facility';
$string['button:display'] = 'Display';

$string['error:add:cohort'] = 'Could not add cohort: cohort already exists';

$string['form:element:label:coursesavailable'] = 'Topics Available';
$string['form:element:label:coursesincohort'] = 'Topics For Cohort: {$a}';

$string['form:element:label:coursesavailable'] = 'Topics Available';
$string['form:element:label:coursesincohort'] = 'Topics For Cohort: {$a}';

$string['form:element:label:cohortsavailable'] = 'Cohorts Available';
$string['form:element:label:coursesbycohort'] = 'Cohorts For Topic: {$a}';

$string['form:element:default:course'] = 'Existing topics ....';
$string['form:element:label:course'] = 'Select an existing topic';

$string['button:select'] = 'Select';


$string['form:element:removetopicselect'] = '';
$string['form:element:addtopicselect'] = '';

$string['form:element:removecohortselect'] = '';
$string['form:element:addcohortselect'] = '';


$string['facility:footer:text'] = '
<div class="facilityfooter">
    <span>Add Topic</span> will:
    <ol>
        <li>Create a course group in the Facility name</li>
        <li>Create a Cohort Sync Enrolment method in the Facility name</li>
        <li>Enrol all the students in the Facility, into the topic and place them in the new group</li>
    </ol>
    <span>Remove Topic</span> will:
    <ol>
        <li>Remove any user related data from the topic (Course)</li>
        <li>Unenrol the user from the topic</li>
        <li>Remove the Cohort Enrolment method from the topic</li>
    </ol>
</div>';

$string['topic:footer:text'] = '
<div class="topicfooter">
    <span>Add facility</span> will:
    <ol>
        <li>Create course group(s) for each selected facility (Cohort)</li>
        <li>Create a Cohort Sync Enrolment method in the Facility name for each facility</li>
        <li>Enrol all the students in the cohort, into the topic and place them in the new group</li>
    </ol>
</div>';
