<?php

/**
 * attendanceregister_tracked_courses.class.php - Class containing Attendance Register's tracked Courses
 *
 * @package    mod
 * @subpackage attendanceregister
 * @version $Id
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Holds all tracked Course of an Attendance Register
 *
 * Implements method to return html_table to render it. *
 * @author nicus
 */
class attendanceregister_tracked_courses {

    /**
     * Array of Courses
     * Keyed by CourseID
     */
    public $courses;

    /**
     * Ref. to AttendanceRegister instance
     */
    private $register;

    public function __construct($register) {
        $this->register = $register;
        $courses = attendanceregister_get_tracked_courses($register);
        // Save courseses using id as key
        $this->courses = array();
        foreach ($courses as $course) {
            $this->courses[$course->id] = $course;
        }
    }

    /**
     * Build the html_table object to represent list of tracked Courses
     * @return html_table
     */
    public function html_table() {
        global $OUTPUT, $doShowPrintableVersion;

        $table = new html_table();
        $table->attributes['class'] .= ' attendanceregister_courselist';

        $tableHeadCell = new html_table_cell(get_string('tracked_courses', 'attendanceregister'));
        $tableHeadCell->colspan = 2;
         $table->head = array($tableHeadCell);

        $rowcount = 0;
        foreach ($this->courses as $course) {
            $rowcount++;
            $tableRow = new html_table_row(array($course->shortname, $course->fullname));

            // Add class for zebra stripes
            $tableRow->attributes['class'] .= (  ($rowcount % 2)?' attendanceregister_oddrow':' attendanceregister_evenrow' );

            $table->data[] = $tableRow;
        }

        return $table;
    }

}
