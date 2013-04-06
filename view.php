<?php

/**
 * Attendance Register view page
 *
 * @package    mod
 * @subpackage attendanceregister
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Disable output buffering
define('NO_OUTPUT_BUFFERING', true);


require('../../config.php');
require_once("lib.php");
require_once($CFG->libdir . '/completionlib.php');

// Main parameters
$userId = optional_param('userid', 0, PARAM_INT);   // if $userId = 0 you'll see all logs
$id = optional_param('id', 0, PARAM_INT);           // Course Module ID, or
$a = optional_param('a', 0, PARAM_INT);             // register ID
$groupId = optional_param('groupid', 0, PARAM_INT);             // Group ID
// Other parameters
$inputAction = optional_param('action', '', PARAM_ALPHA);   // Available actions are defined as ATTENDANCEREGISTER_ACTION_*
// Parameter for deleting offline session
$inputSessionId = optional_param('session', null, PARAM_INT);

// =========================
// Retrieve objects
// =========================

if ($id) {
    $cm = get_coursemodule_from_id('attendanceregister', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $register = $DB->get_record('attendanceregister', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $register = $DB->get_record('attendanceregister', array('id' => $a), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('attendanceregister', $register->id, $register->course, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $id = $cm->id;
}

// Retrive session to delete
$sessionToDelete = null;
if ($inputSessionId) {
    $sessionToDelete = attendanceregister_get_session($inputSessionId);
}

// ================================
// Basic security checks
// ================================
// Requires login
require_course_login($course, false, $cm);

// Retrieve Context
if (!($context = get_context_instance(CONTEXT_MODULE, $cm->id))) {
    print_error('badcontext');
}

// Preload User's Capabilities
$userCapabilities = new attendanceregister_user_capablities($context);

// If user is not defined AND the user has NOT the capability to view other's Register
// force $userId to User's own ID
if ( !$userId && !$userCapabilities->canViewOtherRegisters) {
    $userId = $USER->id;
}
// (beyond this point, if $userId is specified means you are working on one User's Register
//  if not you are viewing all users Sessions)


// ==================================================
// Determine Action and checks specific permissions
// ==================================================
/// These capabilities checks block the page execution if failed

// Requires capabilities to view own or others' register
if ( attendanceregister__isCurrentUser($userId) ) {
    require_capability(ATTENDANCEREGISTER_CAPABILITY_VIEW_OWN_REGISTERS, $context);
} else {
    require_capability(ATTENDANCEREGISTER_CAPABILITY_VIEW_OTHER_REGISTERS, $context);
}

// Require capability to recalculate
$doRecalculate = false;
$doScheduleRecalc = false;
if ($inputAction == ATTENDANCEREGISTER_ACTION_RECALCULATE ) {
    require_capability(ATTENDANCEREGISTER_CAPABILITY_RECALC_SESSIONS, $context);
    $doRecalculate = true;
}
if ($inputAction == ATTENDANCEREGISTER_ACTION_SCHEDULERECALC ) {
    require_capability(ATTENDANCEREGISTER_CAPABILITY_RECALC_SESSIONS, $context);
    $doScheduleRecalc = true;
}


// Printable version?
$doShowPrintableVersion = false;
if ($inputAction == ATTENDANCEREGISTER_ACTION_PRINTABLE) {
    $doShowPrintableVersion = true;
}

/// Check permissions and ownership for showing offline session form or saving them
$doShowOfflineSessionForm = false;
$doSaveOfflineSession = false;
// Only if Offline Sessions are enabled (and No printable-version action)
if ( $register->offlinesessions &&  !$doShowPrintableVersion  ) {
    // Only if User is NOT logged-in-as, or ATTENDANCEREGISTER_ALLOW_LOGINAS_OFFLINE_SESSIONS is enabled
    if ( !session_is_loggedinas() || ATTENDANCEREGISTER_ALLOW_LOGINAS_OFFLINE_SESSIONS ) {
        // If user is on his own Register and may save own Sessions
        // or is on other's Register and may save other's Sessions..
        if ( $userCapabilities->canAddThisUserOfflineSession($register, $userId) ) {
            // Do show Offline Sessions Form
            $doShowOfflineSessionForm = true;

            // If action is saving Offline Session...
            if ( $inputAction == ATTENDANCEREGISTER_ACTION_SAVE_OFFLINE_SESSION  ) {
                // Check Capabilities, to show an error if a security violation attempt occurs
                if ( attendanceregister__isCurrentUser($userId) ) {
                    require_capability(ATTENDANCEREGISTER_CAPABILITY_ADD_OWN_OFFLINE_SESSIONS, $context);
                } else {
                    require_capability(ATTENDANCEREGISTER_CAPABILITY_ADD_OTHER_OFFLINE_SESSIONS, $context);
                }

                // Do save Offline Session
                $doSaveOfflineSession = true;
            }
        }
    }
}


/// Check capabilities to delete self cert
// (in the meanwhile retrieve the record to delete)
$doDeleteOfflineSession = false;
if ($sessionToDelete) {
    // Check if logged-in-as Session Delete
    if (session_is_loggedinas() && !ATTENDANCEREGISTER_ACTION_SAVE_OFFLINE_SESSION) {
        print_error('onlyrealusercandeleteofflinesessions', 'attendanceregister');
    } else if ( attendanceregister__isCurrentUser($userId) ) {
        require_capability(ATTENDANCEREGISTER_CAPABILITY_DELETE_OWN_OFFLINE_SESSIONS, $context);
        $doDeleteOfflineSession = true;
    } else {
        require_capability(ATTENDANCEREGISTER_CAPABILITY_DELETE_OTHER_OFFLINE_SESSIONS, $context);
        $doDeleteOfflineSession = true;
    }
}

// ===========================
// Retrieve data to be shown
// ===========================

// Retrieve Course Completion info object
$completion = new completion_info($course);


// If viewing/updating one User's Register, load the user into $userToProcess
// and retireve User's Sessions or retrieve the Register's Tracked Users
// If viewing all Users load tracked user list
$userToProcess = null;
$userSessions = null;
$trackedUsers = null;
if ( $userId ) {
    $userToProcess = attendanceregister__getUser($userId);
    $userToProcessFullname = fullname($userToProcess);
    $userSessions = new attendanceregister_user_sessions($register, $userId, $userCapabilities);
} else {
    $trackedUsers = new attendanceregister_tracked_users($register, $userCapabilities);
}


// ===========================
// Pepare PAGE for rendering
// ===========================
// Setup PAGE
$url = attendanceregister_makeUrl($register, $userId, $groupId, $inputAction);
$PAGE->set_url($url->out());
$PAGE->set_context($context);
$titleStr = $course->shortname . ': ' . $register->name . ( ($userId) ? ( ': ' . $userToProcessFullname ) : ('') );
$PAGE->set_title(format_string($titleStr));

$PAGE->set_heading($course->fullname);
if ($doShowPrintableVersion) {
    $PAGE->set_pagelayout('print');
}

// Add User's Register Navigation node
if ( $userToProcess ) {
    $registerNavNode = $PAGE->navigation->find($cm->id, navigation_node::TYPE_ACTIVITY);
    $userNavNode = $registerNavNode->add( $userToProcessFullname, $url );
    $userNavNode->make_active();
}


// ==================================================
// Logs User's action
// ==================================================

attendanceregister_add_to_log($register, $cm->id, $inputAction, $userId, $groupId);



// ==============================================
// Start Page Rendering
// ==============================================
echo $OUTPUT->header();
$headingStr = $register->name . ( ( $userId ) ? (': ' . $userToProcessFullname ) : ('') );
echo $OUTPUT->heading(format_string($headingStr));


// ==============================================
// Pepare Offline Session insert form, if needed
// ==============================================
// If a userID is defined, offline sessions are enabled and the user may insert Self.certificatins...
// ...prepare the Form for Self.Cert.
// Process the form (if submitted)
// Note that the User is always the CURRENT User (no UserId param is passed by the form)
$doShowContents = true;
$mform = null;
if ($userId && $doShowOfflineSessionForm && !$doShowPrintableVersion ) {

    // Prepare Form
    $customFormData = array('register' => $register,'courses' => $userSessions->trackedCourses->courses);
    // Also pass userId only if is saving for another user
    if (!attendanceregister__isCurrentUser($userId)) {
        $customFormData['userId'] = $userId;
    }
    $mform = new mod_attendanceregister_selfcertification_edit_form(null, $customFormData);


    // Process Self.Cert Form submission
    if ($mform->is_cancelled()) {
        // Cancel
        redirect($PAGE->url);
    } else if ($doSaveOfflineSession && ($formData = $mform->get_data())) {
        // Save Session
        attendanceregister_save_offline_session($register, $formData);

        // Notification & Continue button
        echo $OUTPUT->notification(get_string('offline_session_saved', 'attendanceregister'), 'notifysuccess');
        echo $OUTPUT->continue_button(attendanceregister_makeUrl($register, $userId));
        $doShowContents = false;
    }
}

//// Process Recalculate
if ($doShowContents && ($doRecalculate||$doScheduleRecalc)) {

    //// Recalculate Session for one User
    if ($userToProcess) {
        $progressbar = new progress_bar('recalcbar', 500, true);
        attendanceregister_force_recalc_user_sessions($register, $userId, $progressbar);

        // Reload User's Sessions
        $userSessions = new attendanceregister_user_sessions($register, $userId, $userCapabilities);
    }

    //// Recalculate (or schedule recalculation) of all User's Sessions
    else {

        //// Schedule Recalculation?
        if ( $doScheduleRecalc ) {
            // Set peding recalc, if set
            if ( !$register->pendingrecalc ) {
                attendanceregister_set_pending_recalc($register, true);
            }
        }

        //// Recalculate Session for all User
        if ( $doRecalculate ) {
            // Reset peding recalc, if set
            if ( $register->pendingrecalc ) {
                attendanceregister_set_pending_recalc($register, false);
            }

            // Turn off time limit: recalculation can be slow
            set_time_limit(0);

            // Cleanup all online Sessions & Aggregates before recalculating [issue #14]
            attendanceregister_delete_all_users_online_sessions_and_aggregates($register);

            // Reload tracked Users list before Recalculating [issue #14]
            $newTrackedUsers = attendanceregister_get_tracked_users($register);

            // Iterate each user and recalculate Sessions
            foreach ($newTrackedUsers as $user) {

                // Recalculate Session for one User
                $progressbar = new progress_bar('recalcbar_' . $user->id, 500, true);
                attendanceregister_force_recalc_user_sessions($register, $user->id, $progressbar, false); // No delete needed, having done before [issue #14]
            }
            // Reload All Users Sessions
            $trackedUsers = new attendanceregister_tracked_users($register, $userCapabilities);
        }
    }

    // Notification & Continue button
    if ( $doRecalculate || $doScheduleRecalc ) {
        $notificationStr = get_string( ($doRecalculate)?'recalc_complete':'recalc_scheduled', 'attendanceregister');
        echo $OUTPUT->notification($notificationStr, 'notifysuccess');
    }
    echo $OUTPUT->continue_button(attendanceregister_makeUrl($register, $userId));
    $doShowContents = false;
}
//// Process Delete Offline Session Action
else if ($doShowContents && $doDeleteOfflineSession) {
    // Delete Offline Session
    attendanceregister_delete_offline_session($register, $sessionToDelete->userid, $sessionToDelete->id);

    // Notification & Continue button
    echo $OUTPUT->notification(get_string('offline_session_deleted', 'attendanceregister'), 'notifysuccess');
    echo $OUTPUT->continue_button(attendanceregister_makeUrl($register, $userId));
    $doShowContents = false;
}
//// Show Contents: User's Sesions (if $userID) or Tracked Users summary
else if ($doShowContents) {

    //// Show User's Sessions
    if ($userId) {
        
        /// On View Completion [fixed with isse #52]        
        // If current user is the selected user (and completion is enabled) mark module as viewed
        if ( $userId == $USER->id && $completion->is_enabled($cm) ) {
            $completion->set_module_viewed($cm, $userId);
        }    
        
        /// Button bar

        echo $OUTPUT->container_start('attendanceregister_buttonbar');
        
        // Printable version button or Back to normal version
        $linkUrl = attendanceregister_makeUrl($register, $userId, null, ( ($doShowPrintableVersion) ? (null) : (ATTENDANCEREGISTER_ACTION_PRINTABLE)));
        echo $OUTPUT->single_button($linkUrl, (($doShowPrintableVersion) ? (get_string('back_to_normal', 'attendanceregister')) : (get_string('show_printable', 'attendanceregister'))), 'get');
        // Back to Users List Button (if allowed & !printable)
        if ($userCapabilities->canViewOtherRegisters && !$doShowPrintableVersion) {
            echo $OUTPUT->single_button(attendanceregister_makeUrl($register), get_string('back_to_tracked_user_list', 'attendanceregister'), 'get');
        }
        echo $OUTPUT->container_end();  // Button Bar
        echo '<br />';


        /// Offline Session Form
        // Show Offline Session Self-Certifiation Form (not in printable)
        if ($mform && $register->offlinesessions && !$doShowPrintableVersion) {
            echo "<br />";
            echo $OUTPUT->box_start('generalbox attendanceregister_offlinesessionform');
            $mform->display();
            echo $OUTPUT->box_end();
        }

//    // Show tracked Courses
//    echo "<br />";
//    echo html_writer::table( $userSessions->trackedCourses->html_table()  );

        // Show User's Sessions summary
        echo "<br />";
        echo html_writer::table($userSessions->userAggregates->html_table());

        echo "<br />";
        echo html_writer::table($userSessions->html_table());
    }

    //// Show list of Tracked Users summary
    else {

        /// Button bar
        // Show Recalc pending warning
        if ( $register->pendingrecalc && $userCapabilities->canRecalcSessions && !$doShowPrintableVersion ) {
            echo $OUTPUT->notification( get_string('recalc_scheduled_on_next_cron', 'attendanceregister')  );
        }
        // Show cron not yet run on this instance
        else if ( !attendanceregister__didCronRanAfterInstanceCreation($cm) ) {
            echo $OUTPUT->notification( get_string('first_calc_at_next_cron_run', 'attendanceregister')  );
        }

        echo $OUTPUT->container_start('attendanceregister_buttonbar');

        // If current user is tracked, show view-my-sessions button [feature #28]
        if ( $userCapabilities->isTracked ) {
            $linkUrl = attendanceregister_makeUrl($register, $USER->id);
            echo $OUTPUT->single_button($linkUrl, get_string('show_my_sessions' ,'attendanceregister'), 'get');
        }
        
        // Printable version button or Back to normal version
        $linkUrl = attendanceregister_makeUrl($register, null, null, ( ($doShowPrintableVersion) ? (null) : (ATTENDANCEREGISTER_ACTION_PRINTABLE)));
        echo $OUTPUT->single_button($linkUrl, (($doShowPrintableVersion) ? (get_string('back_to_normal', 'attendanceregister')) : (get_string('show_printable', 'attendanceregister'))), 'get');
        
        echo $OUTPUT->container_end();  // Button Bar
        echo '<br />';

        // Show list of tracked courses
        echo "<br />";
        echo html_writer::table($trackedUsers->trackedCourses->html_table());


        // Show tracked Users list
        echo "<br />";
        echo html_writer::table($trackedUsers->html_table());
    }
}



// Output page footer
if (!$doShowPrintableVersion) {
    echo $OUTPUT->footer();
}