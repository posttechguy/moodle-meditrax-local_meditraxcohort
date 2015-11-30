<?php
/*
 * Custom cohort enrolment for Meditrax Facility plugin
 *
 * Capabilities definition
 *
 * @package    local_meditraxcohort
 * @author     Bevan Holman <bevan@pukunui.com>, Pukunui
 * @copyright  2015 onwards, Pukunui
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$capabilities = array (
    'local/meditraxcohort:manage' => array (
        'riskbitmask'  => RISK_CONFIG,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array (
            'manager' => CAP_ALLOW
        )
    ),
);
