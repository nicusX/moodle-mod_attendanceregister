<?php

/**
 * attendanceregister_tracked_users.class.php - Class containing Attendance Register's tracked Users and their summaries
 *
 * @package    mod
 * @subpackage attendanceregister
 * @version $Id
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Holds all tracked Users of an Attendance Register
 *
 * Implements method to return html_table to render it.
 *
 * @author nicus
 */
class attendanceregister_tracked_users {

    /**
     * Array of User
     */
    public $users;

    /**
     * Array if attendanceregister_user_aggregates_summary
     * keyed by $userId
     */
    public $usersSummaryAggregates;


    /**
     * Instance of attendanceregister_tracked_courses
     * containing all tracked Courses
     * @var type
     */
    public $trackedCourses;

    /**
     * Ref. to AttendanceRegister instance
     */
    private $register;

    /**
     * Ref to mod_attendanceregister_user_capablities instance
     */
    private $userCapabilites;


    /**
     * Constructor
     * Load all tracked User's and their summaris
     * Load list of tracked Courses
     * @param object $register
     * @param attendanceregister_user_capablities $userCapabilities
     */
    function __construct($register, attendanceregister_user_capablities $userCapabilities) {
        $this->register = $register;
        $this->userCapabilities = $userCapabilities;
        $this->users = attendanceregister_get_tracked_users($register);
        $this->trackedCourses = new attendanceregister_tracked_courses($register);

        $trackedUsersIds = attendanceregister__extract_property($this->users, 'id');

        // Retrieve Aggregates summaries
        $aggregates = attendanceregister__get_all_users_aggregate_summaries($register);
        // Remap in an array of attendanceregister_user_aggregates_summary, mapped by userId
        $this->usersSummaryAggregates = array();
        foreach ($aggregates as $aggregate) {
            // Retain only tracked users
            if ( in_array( $aggregate->userid, $trackedUsersIds) ) {
                // Create User's attendanceregister_user_aggregates_summary instance if not exists
                if ( !isset( $this->usersSummaryAggregates[ $aggregate->userid ] )) {
                    $this->usersSummaryAggregates[ $aggregate->userid ] = new attendanceregister_user_aggregates_summary();
                }
                // Populate attendanceregister_user_aggregates_summary fields
                if( $aggregate->grandtotal ) {
                    $this->usersSummaryAggregates[ $aggregate->userid ]->grandTotalDuration = $aggregate->duration;
                    $this->usersSummaryAggregates[ $aggregate->userid ]->lastSassionLogout = $aggregate->lastsessionlogout;
                } else if ( $aggregate->total && $aggregate->onlinesess == 1 ) {
                $this->usersSummaryAggregates[ $aggregate->userid ]->onlineTotalDuration = $aggregate->duration;
                } else if ( $aggregate->total && $aggregate->onlinesess == 0 ) {
                    $this->usersSummaryAggregates[ $aggregate->userid ]->offlineTotalDuration = $aggregate->duration;
                }
            }
        }
    }

    /**
     * Build the html_table object to represent details
     * @return html_table
     */
    public function html_table() {
        global $OUTPUT, $doShowPrintableVersion;

        $strNotAvail = get_string('notavailable');

        $table = new html_table();
        $table->attributes['class'] .= ' attendanceregister_userlist table table-condensed table-bordered table-striped table-hover';

        /// Header

        $table->head = array(
            get_string('count', 'attendanceregister'),
            get_string('fullname', 'attendanceregister'),
            get_string('total_time_online', 'attendanceregister'),
        );
        $table->align = array('left', 'left', 'right');

        if ( $this->register->offlinesessions ) {
            $table->head[] = get_string('total_time_offline', 'attendanceregister');
            $table->align[] = 'right';
            $table->head[] = get_string('grandtotal_time', 'attendanceregister');
            $table->align[] = 'right';
        }

        $table->head[] = get_string('last_session_logout', 'attendanceregister');
        $table->align[] = 'left';


        /// Table Rows

        if( $this->users ) {
            $rowcount = 0;
            foreach ($this->users as $user) {
                $rowcount++;

                $userAggregate = null;
                if ( isset( $this->usersSummaryAggregates[$user->id] ) ) {
                    $userAggregate = $this->usersSummaryAggregates[$user->id];
                }

                // Basic columns
                $linkUrl = attendanceregister_makeUrl($this->register, $user->id);
                $fullnameWithLink = '<a href="' . $linkUrl . '">' . fullname($user) . '</a>';
                $onlineDuration = ($userAggregate)?( $userAggregate->onlineTotalDuration ):( null );
                $onlineDurationStr =  attendanceregister_format_duration($onlineDuration );
                $tableRow = new html_table_row( array( $rowcount, $fullnameWithLink, $onlineDurationStr ) );

                // Add class for zebra stripes
                $tableRow->attributes['class'] .= (  ($rowcount % 2)?' attendanceregister_oddrow':' attendanceregister_evenrow' );

                // Optional columns
                if ( $this->register->offlinesessions ) {
                    $offlineDuration = ($userAggregate)?($userAggregate->offlineTotalDuration):( null );
                    $offlineDurationStr = attendanceregister_format_duration($offlineDuration);
                    $tableCell = new html_table_cell( $offlineDurationStr );
                    $tableRow->cells[] = $tableCell;

                    $grandtotalDuration = ($userAggregate)?($userAggregate->grandTotalDuration ):( null );
                    $grandtotalDurationStr = attendanceregister_format_duration($grandtotalDuration);
                    $tableCell = new html_table_cell( $grandtotalDurationStr );
                    $tableRow->cells[] = $tableCell;
                }

                $lastSessionLogoutStr = ($userAggregate)?( attendanceregister__formatDateTime( $userAggregate->lastSassionLogout ) ):( get_string('no_session','attendanceregister') );
                $tableCell = new html_table_cell( $lastSessionLogoutStr );
                 $tableRow->cells[] = $tableCell;

                $table->data[] = $tableRow;
            }
        } else {
            // No User
            $row = new html_table_row();
            $labelCell = new html_table_cell();
            $labelCell->colspan = count($table->head);
            $labelCell->text = get_string('no_tracked_user', 'attendanceregister');
            $row->cells[] = $labelCell;
            $table->data[] = $row;
        }

        return $table;
    }
}

