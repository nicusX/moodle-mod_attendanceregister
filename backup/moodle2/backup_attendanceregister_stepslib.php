<?php

/**
 * @package    mod
 * @subpackage attendanceregister
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_attendanceregister_activity_task
 */

/**
 * Define the complete attendanceregister structure for backup, with file and id annotations
 */
class backup_attendanceregister_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');


        // Define each element
        $attendanceregister = new backup_nested_element('attendanceregister', array('id'),
            array( 'name', 'intro', 'introformat' , 'type', 'offlinesessions',
                'sessiontimeout', 'dayscertificable',
                'offlinecomments', 'mandatoryofflinecomm', 'offlinespecifycourse', 'mandofflspeccourse',
                'timemodified' ));

        $sessions = new backup_nested_element('sessions');

        $session = new backup_nested_element('session', array('id'),
                array( 'userid', 'login', 'logout', 'duration', 'onlinesess', 'refcourseshortname', 'comments', 'addedbyuserid' ));

        // Builds the tree
        $attendanceregister->add_child($sessions);
        $sessions->add_child($session);


        // Define sources
        $attendanceregister->set_source_table('attendanceregister', array('id' => backup::VAR_ACTIVITYID ));

        if ( $userinfo ) {
            $session->set_source_sql('
                SELECT s.id, s.register, s.userid, s.login, s.logout, s.duration, s.onlinesess, s.comments,
                    c.shortname AS refcourseshortname,
                    s.addedbyuserid
                  FROM {attendanceregister_session} s LEFT JOIN {course} c ON c.id = s.refcourse
                  WHERE s.register = ? AND s.onlinesess = 0
                ', array(backup::VAR_PARENTID));
        }

        // Define ID annotations
        $session->annotate_ids('user', 'userid');
        $session->annotate_ids('user', 'addedbyuserid');


        // Define file annotations
        $attendanceregister->annotate_files('mod_attendanceregister', 'intro', null); // This file area hasn't itemid

        // Return the root element (attendanceregister), wrapped into standard activity structure
        return $this->prepare_activity_structure($attendanceregister);
    }
}
