<?php
/**
 * Custom cohort enrolment for Meditrax Facility plugin
 *
 * Administration settings
 *
 * @package    local_meditraxcohort
 * @author     Bevan Holman <bevan@pukunui.com>, Pukunui
 * @copyright  2015 onwards, Pukunui
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Settings menu.

$ADMIN->add('root', new admin_category('local_meditraxcohort', get_string('pluginname', 'local_meditraxcohort')));

$ADMIN->add('local_meditraxcohort', new admin_externalpage('local_meditraxcohort_facility', get_string('facility:link:add', 'local_meditraxcohort'),
            new moodle_url('/local/meditraxcohort/facility.php'),
            'local/meditraxcohort:manage'));

$ADMIN->add('local_meditraxcohort', new admin_externalpage('local_meditraxcohort_topic', get_string('topic:link:add', 'local_meditraxcohort'),
            new moodle_url('/local/meditraxcohort/topic.php'),
            'local/meditraxcohort:manage'));
