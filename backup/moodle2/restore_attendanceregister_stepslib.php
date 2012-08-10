<?php

/**
 * @package    mod
 * @subpackage attendanceregister
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Define all the restore steps that will be used by the restore_attendanceregister_activity_task
 */

/**
 * Structure step to restore one choice activity
 */
class restore_attendanceregister_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('attendanceregister', '/activity/attendanceregister');
        if ($userinfo) {
            $paths[] = new restore_path_element('attendanceregister_session', '/activity/attendanceregister/sessions/session');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_attendanceregister($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Insert the attendanceregister record
        $newitemid = $DB->insert_record('attendanceregister', $data);

        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_attendanceregister_session($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->register = $this->get_new_parentid('attendanceregister');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->addedbyuserid = $this->get_mappingid('user', $data->addedbyuserid);

        // Lookup RefCourse by ShortName, if exists on destination
        if ($data->refcourseshortname) {
            $refcourse = $DB->get_record('course', array('shortname' => $data->refcourseshortname), '*', IGNORE_MISSING);
            if ($refcourse) {
                $data->refcourse = $refcourse->id;
            }
        }


        $newitemid = $DB->insert_record('attendanceregister_session', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function apply_activity_instance($newitemid) {
        // Call parent setup to adjust the restore register instance
        parent::apply_activity_instance($newitemid);
    }

    protected function after_execute() {
        global $DB;
        // Add attendanceregister related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_attendanceregister', 'intro', null);

        // Execute recalculate all
        $register = $DB->get_record('attendanceregister', array('id' => $this->task->get_activityid()), '*', MUST_EXIST);
        $this->log('Recalculating Session of restored AttendanceRegister (id:' . $this->task->get_activityid() . ')', LOG_INFO);
        attendanceregister_force_recalc_all($register);
    }

}
