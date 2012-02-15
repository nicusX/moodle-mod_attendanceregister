<?php

/**
 * @package    mod
 * @subpackage attendanceregister
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/mod/attendanceregister/backup/moodle2/backup_attendanceregister_stepslib.php'); // Because it exists (must)
require_once($CFG->dirroot . '/mod/attendanceregister/backup/moodle2/backup_attendanceregister_settingslib.php'); // Because it exists (optional)

/**
 * attendanceregister backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_attendanceregister_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        $this->add_step(new backup_attendanceregister_activity_structure_step('attendanceregister_structure', 'attendanceregister.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        // Link to the list of attendanceregisters
        $search="/(".$base."\/mod\/attendanceregister\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@ATTENDANCEREGISTERINDEX*$2@$', $content);

        // Link to attendanceregisters view by moduleid
        $search="/(".$base."\/mod\/attendanceregister\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@ATTENDANCEREGISTERVIEWBYID*$2@$', $content);


        // Link to attendanceregisters view by registerid
        $search="/(".$base."\/mod\/attendanceregister\/view.php\?a\=)([0-9]+)/";
        $content= preg_replace($search, '$@ATTENDANCEREGISTERVIEWBYREGISTERID*$2@$', $content);

        // Link to a User's Regiter by moduleid
        $search="/(".$base."\/mod\/attendanceregister\/view.php\?id\=)([0-9]+)\&userid\=([0-9]+)/";
        $content= preg_replace($search, '$@ATTENDANCEREGISTERVIEWUSERBYID*$2*$3@$', $content);

        // Link to a User's Regiter by registerid
        $search="/(".$base."\/mod\/attendanceregister\/view.php\?a\=)([0-9]+)\&userid\=([0-9]+)/";
        $content= preg_replace($search, '$@ATTENDANCEREGISTERVIEWUSERBYREGISTERID*$2*$3@$', $content);

        return $content;
    }

}

