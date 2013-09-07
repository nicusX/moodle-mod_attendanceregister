<?php
/**
 * attendanceregister_user_aggregates.class.php - Class containing User's Aggregate  in an AttendanceRegister
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
 * Holds in a single Object all attendanceregister_aggregate records
 * for a User and a Register instance.
 *
 * Implements method to return html_table to render it.
 *
 * Note that class constructor execute a db query for retrieving User's aggregates
 *
 * @author nicus
 */
class attendanceregister_user_aggregates  {

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
     * Offline sessions, per RefCourseId
     */
    public $perCourseOfflineSessions = array();

    /**
     * Offline Sessions w/o any RefCourse
     */
    public $noCourseOfflineSessions = 0;

    /**
     * Last calculated Session Logout
     */
    public $lastSassionLogout = 0;

    /**
     * Ref to attendanceregister_user_sessions instance
     */
    private $userSessions;

    /**
     * User instance
     */
    public $user;

    /**
     * Create an instance for a given register and user
     * @param object $register
     * @param int $userId
     * @param attendanceregister_user_sessions $userSessions
     */
    public function __construct($register, $userId, attendanceregister_user_sessions $userSessions) {
        global $DB;

        $this->userSessions = $userSessions;

        // Retrieve User instance
        $this->user = attendanceregister__getUser($userId);

        // Retrieve attendanceregister_aggregate records
        $aggregates = attendanceregister__get_user_aggregates($register, $userId);

        foreach ($aggregates as $aggregate) {
            if( $aggregate->grandtotal ) {
                $this->grandTotalDuration = $aggregate->duration;
                $this->lastSassionLogout = $aggregate->lastsessionlogout;
            } else if ( $aggregate->total && $aggregate->onlinesess == 1 ) {
                $this->onlineTotalDuration = $aggregate->duration;
            } else if ( $aggregate->total && $aggregate->onlinesess == 0 ) {
                $this->offlineTotalDuration = $aggregate->duration;
            } else if (!$aggregate->total && $aggregate->onlinesess == 0 && $aggregate->refcourse != null ) {
                $this->perCourseOfflineSessions[ $aggregate->refcourse ] = $aggregate->duration;
            } else if (!$aggregate->total && $aggregate->onlinesess == 0 && $aggregate->refcourse == null ) {
                $this->noCourseOfflineSessions = $aggregate->duration;
            } else {
                // Should not happen!
                debugging('Unconsistent Aggregate: '.print_r($aggregate, true), DEBUG_DEVELOPER);
            }
        }
    }


    /**
     * Build the html_table object to represent summary
     * @return html_table
     */
    public function html_table() {
        global $OUTPUT, $doShowPrintableVersion;

        $table = new html_table();
        $table->attributes['class'] .= ' attendanceregister_usersummary table table-condensed table-bordered table-striped table-hover';


        // Header
        $table->head[] =  get_string('user_sessions_summary', 'attendanceregister');
        $table->headspan = array(3);


        // Previous Site-wise Login (is Moodle's _last_ login)
        $row = new html_table_row();
        $labelCell = new html_table_cell();
        $labelCell->colspan = 2;
        $labelCell->text = get_string('prev_site_login', 'attendanceregister');
        $row->cells[] = $labelCell;
        $valueCell = new html_table_cell();
        $valueCell->text = attendanceregister__formatDateTime($this->user->lastlogin);
        $row->cells[] = $valueCell;
        $table->data[] = $row;


        // Last Site-wise Login (is Moodle's _current_ login)
        $row = new html_table_row();
        $labelCell = new html_table_cell();
        $labelCell->colspan = 2;
        $labelCell->text = get_string('last_site_login', 'attendanceregister');
        $row->cells[] = $labelCell;
        $valueCell = new html_table_cell();
        $valueCell->text = attendanceregister__formatDateTime($this->user->currentlogin);
        $row->cells[] = $valueCell;
        $table->data[] = $row;

        // Last Site-wise access
        $row = new html_table_row();
        $labelCell = new html_table_cell();
        $labelCell->colspan = 2;
        $labelCell->text = get_string('last_site_access', 'attendanceregister');
        $row->cells[] = $labelCell;
        $valueCell = new html_table_cell();
        $valueCell->text = attendanceregister__formatDateTime($this->user->lastaccess);
        $row->cells[] = $valueCell;
        $table->data[] = $row;

        // Last Calculated Session Logout
        $row = new html_table_row();
        $labelCell = new html_table_cell();
        $labelCell->colspan = 2;
        $labelCell->text = get_string('last_calc_online_session_logout', 'attendanceregister');
        $row->cells[] = $labelCell;
        $valueCell = new html_table_cell();
        $valueCell->text = attendanceregister__formatDateTime($this->lastSassionLogout);
        $row->cells[] = $valueCell;
        $table->data[] = $row;


        // Separator
        $table->data[] = 'hr';


        // Online Total
        $row = new html_table_row();
        $row->attributes['class'] .= ' attendanceregister_onlinesubtotal success';
        $labelCell = new html_table_cell();
        $labelCell->colspan = 2;
        $labelCell->text = get_string('online_sessions_total_duration', 'attendanceregister');
        $row->cells[] = $labelCell;

        $valueCell = new html_table_cell();
        $valueCell->text = attendanceregister_format_duration( $this->onlineTotalDuration );
        $row->cells[] = $valueCell;

        $table->data[] = $row;

        // Offline
        if ( $this->offlineTotalDuration ) {
            // Separator
            $table->data[] = 'hr';

            // Offline per RefCourse (if any)
            foreach($this->perCourseOfflineSessions as $refCourseId => $courseOfflineSessions  ) {
                $row = new html_table_row();
                $row->attributes['class'] .= '';
                $labelCell = new html_table_cell();
                $labelCell->text = get_string('offline_refcourse_duration', 'attendanceregister');
                $row->cells[] = $labelCell;

                $courseCell = new html_table_cell();
                if ( $refCourseId ) {
                    $courseCell->text = $this->userSessions->trackedCourses->courses[ $refCourseId ]->fullname;
                } else {
                   $courseCell->text = get_string('not_specified', 'attendanceregister');
                }
                $row->cells[] = $courseCell;

                $valueCell = new html_table_cell();
                $valueCell->text = attendanceregister_format_duration( $courseOfflineSessions );
                $row->cells[] = $valueCell;

                $table->data[] = $row;
            }

            // Offline no-RefCourse (if any)
            if ( $this->noCourseOfflineSessions ) {
                $row = new html_table_row();
                 $row->attributes['class'] .= '';
                $labelCell = new html_table_cell();
                $labelCell->text = get_string('offline_refcourse_duration', 'attendanceregister');
                $row->cells[] = $labelCell;

                $courseCell = new html_table_cell();
                $courseCell->text = get_string('no_refcourse', 'attendanceregister');
                $row->cells[] = $courseCell;

                $valueCell = new html_table_cell();
                $valueCell->text = attendanceregister_format_duration( $this->noCourseOfflineSessions );
                $row->cells[] = $valueCell;

                $table->data[] = $row;
            }

            // Offline Total (if any)
            $row = new html_table_row();
            $row->attributes['class'] .= ' attendanceregister_offlinesubtotal';
            $labelCell = new html_table_cell();
            $labelCell->colspan = 2;
            $labelCell->text = get_string('offline_sessions_total_duration', 'attendanceregister');
            $row->cells[] = $labelCell;

            $valueCell = new html_table_cell();
            $valueCell->text = attendanceregister_format_duration( $this->offlineTotalDuration );
            $row->cells[] = $valueCell;

            $table->data[] = $row;


            // GrandTotal
            $row = new html_table_row();
            $row->attributes['class'] .= ' attendanceregister_grandtotal active';
            $labelCell = new html_table_cell();
            $labelCell->colspan = 2;
            $labelCell->text = get_string('sessions_grandtotal_duration', 'attendanceregister');
            $row->cells[] = $labelCell;

            $valueCell = new html_table_cell();
            $valueCell->text = attendanceregister_format_duration( $this->grandTotalDuration );
            $row->cells[] = $valueCell;

            $table->data[] = $row;
        }

        return $table;
    }
}
