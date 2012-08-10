<?php

/**
 * attendanceregister_user_sessions.class.php - Class containing User's Sessions in an AttendanceRegister
 *
 * @package    mod
 * @subpackage attendanceregister
 * @version $Id
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Holds all attendanceregister_session record of a User's Register
 *
 * Implements method to return html_table to render it.
 *
 * @author nicus
 */
class attendanceregister_user_sessions {

    /**
     * attendanceregister_session records
     */
    public $userSessions;

    /**
     * Instance of attendanceregister_user_aggregates
     */
    public $userAggregates;

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
     * Load User's Sessions
     * Load User's Aggregates
     *
     * @param object $register
     * @param int $userId
     * @param attendanceregister_user_capablities $userCapabilities
     */
    public function __construct($register, $userId, attendanceregister_user_capablities $userCapabilities) {
        $this->register = $register;
        $this->userSessions = attendanceregister_get_user_sessions($register, $userId);
        $this->userAggregates = new attendanceregister_user_aggregates($register, $userId, $this);
        $this->trackedCourses = new attendanceregister_tracked_courses($register);
        $this->userCapabilites = $userCapabilities;
    }

    /**
     * Build the html_table object to represent details
     * @return html_table
     */
    public function html_table() {
        global $OUTPUT, $doShowPrintableVersion;

        $table = new html_table();
        $table->attributes['class'] .= ' attendanceregister_sessionlist';

        /// Header

        $table->head = array(
            get_string('count', 'attendanceregister'),
            get_string('start', 'attendanceregister'),
            get_string('end', 'attendanceregister'),
            get_string('online_offline', 'attendanceregister')
        );
        $table->align = array('left', 'left', 'left', 'right');

        if ($this->register->offlinesessions) {
            $table->head[] = get_string('online_offline', 'attendanceregister');
            $table->align[] = 'center';
            if ($this->register->offlinespecifycourse) {
                $table->head[] = get_string('ref_course', 'attendanceregister');
                $table->align[] = 'left';
            }
            if ($this->register->offlinecomments) {
                $table->head[] = get_string('comments', 'attendanceregister');
                $table->align[] = 'left';
            }
        }

        /// Table rows

        if ( $this->userSessions ) {
            $stronline = get_string('online', 'attendanceregister');
            $stroffline = get_string('offline', 'attendanceregister');

            // Iterate sessions
            $rowcount = 0;
            foreach ($this->userSessions as $session) {
                $rowcount++;

                // Rowcount column
                $rowcountStr = (string)$rowcount;
                // Offline Delete button (if Session is offline and the current user may delete this user's offline sessions)
                if ( !$session->online && $this->userCapabilites->canDeleteThisUserOfflineSession($session->userid) ) {
                    $deleteUrl = attendanceregister_makeUrl($this->register, $session->userid, null, ATTENDANCEREGISTER_ACTION_DELETE_OFFLINE_SESSION, array('session' => $session->id ));
                    $confirmAction = new confirm_action(get_string('are_you_sure_to_delete_offline_session', 'attendanceregister'));
                    $rowcountStr .= ' ' . $OUTPUT->action_icon($deleteUrl, new pix_icon('t/delete',  get_string('delete') ), $confirmAction);
                }

                // Duration
                $duration = attendanceregister_format_duration($session->duration);

                // Basic columns
                $tableRow = new html_table_row( array($rowcountStr, attendanceregister__formatDateTime($session->login), attendanceregister__formatDateTime($session->logout), $duration) );

                // Add class for zebra stripes
                $tableRow->attributes['class'] .= (  ($rowcount % 2)?' attendanceregister_oddrow':' attendanceregister_evenrow' );

                // Optional columns
                if ($this->register->offlinesessions) {

                    // Offline/Online
                    $onlineOfflineStr = (($session->online) ? $stronline : $stroffline);

                    // if saved by other
                    if ( $session->addedbyuserid ) {
                        // Retrieve the other user, if any, or unknown
                        $a = attendanceregister__otherUserFullnameOrUnknown($session->addedbyuserid);
                        $addedByStr = get_string('session_added_by_another_user', 'attendanceregister', $a);
                        $onlineOfflineStr = html_writer::tag('a', $onlineOfflineStr . '*', array('title'=>$addedByStr, 'class'=>'addedbyother') );
                    }
                    $tableCell = new html_table_cell($onlineOfflineStr);
                    $tableCell->attributes['class'] .=  ( ($session->online)?' online_label':' offline_label' );
                    $tableRow->cells[] = $tableCell;

                    // Ref.Course
                    if ( $this->register->offlinespecifycourse  ) {
                        if ( $session->online ) {
                            $refCourseName = '';
                        } else {
                            if ( $session->refcourse ) {
                                $refCourse = $this->trackedCourses->courses[ $session->refcourse ];

                                // In Printable Version show fullname (shortname), otherwise only shortname
                                if ($doShowPrintableVersion) {
                                    $refCourseName = $refCourse->fullname . ' ('. $refCourse->shortname .')';
                                } else {
                                    $refCourseName = $refCourse->shortname;
                                }
                            } else {
                                $refCourseName = get_string('not_specified', 'attendanceregister');
                            }
                        }
                        $tableCell = new html_table_cell($refCourseName);
                        $tableRow->cells[] = $tableCell;
                    }

                    // Offline Comments
                    if ($this->register->offlinecomments  ) {
                        if ( !$session->online && $session->comments ) {
                            // Shorten the comments (if !printable)
                            if ( !$doShowPrintableVersion ) {
                                $comment = attendanceregister__shorten_comment($session->comments);
                            } else {
                                $comment = $session->comments;
                            }
                        } else {
                            $comment = '';
                        }
                        $tableCell = new html_table_cell($comment);
                        $tableRow->cells[] = $tableCell;
                    }
                }
                $table->data[] = $tableRow;
            }
        } else {
            // No Session

            $row = new html_table_row();
            $labelCell = new html_table_cell();
            $labelCell->colspan = count($table->head);
            $labelCell->text = get_string('no_session_for_this_user', 'attendanceregister');
            $row->cells[] = $labelCell;
            $table->data[] = $row;
        }

        return $table;
    }

}
