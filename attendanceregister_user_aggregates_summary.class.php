<?php

/**
 * attendanceregister_user_aggregates_summary.class.php
 * Class containing User's Aggregate in an AttendanceRegister (only for summary aggregates)
 *
 * @package    mod
 * @subpackage attendanceregister
 * @version $Id
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Represents a User's Aggregate for a Register
 * Holds in a single Object attendanceregister_aggregate records for
 * summary infos only (total & grandtotal)
 * for a User and a Register instance.
 *
 * @author nicus
 */
class attendanceregister_user_aggregates_summary {

    /**
     * Grandtotal of all sessions
     */
    public $grandTotalDuration = 0;

    /**
     * Total of all Online Sessions
     */
    public $onlineTotalDuration = 0;

    /**
     * Total of all Offline Sessions
     */
    public $offlineTotalDuration = 0;

    /**
     * Last calculated Session Logout
     */
    public $lastSassionLogout = 0;

}