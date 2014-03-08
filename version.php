<?php

/**
 * Attendance Register plugin version info
 *
 * @package    mod
 * @subpackage attendanceregister
 * @version $Id
 *
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
$module->version  = 2014030805;
$module->requires = 2011120100;  // Requires this Moodle version
$module->cron     = 300;
$module->component = 'mod_attendanceregister'; // Full name of the plugin (used for diagnostics)
$module->maturity  = MATURITY_STABLE;
$module->release   = "2014.03.08.05"; // User-friendly version number



