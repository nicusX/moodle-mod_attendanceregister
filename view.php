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
    $cm = get_coursemodule_from_instance('attendanceregister', $register->id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $id = $cm->id;
}

// Retrive session to delete
$sessionToDelete = null;
if ($inputSessionId) {
    $sessionToDelete = attendanceregister_get_session($inputSessionId);
}

// =========================
// Prepare PAGE URL
// =========================

$url = attendanceregister_makeUrl($register, $userId, $groupId, $inputAction);



// ================================
// Basic security checks
// ================================
// Requires login
require_course_login($course, false, $cm);

// Retrieve Context
if (!($context = get_context_instance(CONTEXT_MODULE, $cm->id))) {
    print_error('badcontext');
}

// User's Capabilities
$userCapabilities = new attendanceregister_user_capablities($context);

// If $userId is 0 and the current user has not the capability required to view others,
// force viewing his own register
if (!$userId && !$userCapabilities->canViewOtherRegisters) {
    $userId = $USER->id;
}


// ==================================================
// Determine Action and checks specific permissions
// ==================================================
/// These capabilities checks block the page execution if failed
// Requires capabilities to view own or others' register
if ($userId == $USER->id) {
    require_capability(ATTENDANCEREGISTER_CAPABILITY_VIEW_OWN_REGISTERS, $context);
} else {
    require_capability(ATTENDANCEREGISTER_CAPABILITY_VIEW_OTHER_REGISTERS, $context);
}

// Require capability to recalculate
$doRecalculate = false;
if ($inputAction == ATTENDANCEREGISTER_ACTION_RECALCULATE) {
    require_capability(ATTENDANCEREGISTER_CAPABILITY_RECALC_SESSIONS, $context);
    $doRecalculate = true;
}


/// Check if User has capabilities to Save Own Offline Session
/// and determine if save Offline Session action has been called (and is allowed)
// If ATTENDANCEREGISTER_ACTION_SAVE_OFFLINE_SESSION == false, saving Self.Cert
// is allowed only to real-users and for himself only
$doShowOfflineSessionForm = false;
$doSaveOfflineSession = false;
if ((!session_is_loggedinas() || ATTENDANCEREGISTER_ALLOW_LOGINAS_OFFLINE_SESSIONS) && $USER->id == $userId) {
    $doShowOfflineSessionForm = $userCapabilities->canAddOwnOfflineSessions;

    // Saving new offline session?
    if ($inputAction == ATTENDANCEREGISTER_ACTION_SAVE_OFFLINE_SESSION && $userId && $register->offlinesessions) {
        $doSaveOfflineSession = true;
    }
}

// Printable version?
$doShowPrintableVersion = false;
if ($inputAction == ATTENDANCEREGISTER_ACTION_PRINTABLE) {
    $doShowPrintableVersion = true;
}


/// Check capabilities to delete self cert
// (in the meanwhile retrieve the record to delete)
$doDeleteOfflineSession = false;
if ($sessionToDelete) {
    // Check if logged-in-as Session Delete
    if (session_is_loggedinas() && !ATTENDANCEREGISTER_ACTION_SAVE_OFFLINE_SESSION) {
        print_error('onlyrealusercandeleteofflinesessions', 'attendanceregister');
    } else if ($sessionToDelete->userid == $USER->id) {
        require_capability(ATTENDANCEREGISTER_CAPABILITY_DELETE_OWN_OFFLINE_SESSIONS, $context);
        $doDeleteOfflineSession = true;
    } else {
        require_capability(ATTENDANCEREGISTER_CAPABILITY_DELETE_OTHER_OFFLINE_SESSIONS, $context);
        $doDeleteOfflineSession = true;
    }
}


// ==================================================
// Logs User's action
// ==================================================

attendanceregister_add_to_log($register, $cm->id, $inputAction, $userId, $groupId);


// =======================================
// Completition
// =======================================
/// Mark as viewed
$completion = new completion_info($course);
$completion->set_module_viewed($cm);
// TODO add other Completition info
// ===========================
// Retrieve data to be shown
// ===========================
// If $userId is defined, retireve User's Sessions,
// otherwise retrieve the Register's Tracked Users
$userSessions = null;
$trackedUsers = null;
$userFullName = null;
if ($userId) {
    $userSessions = new attendanceregister_user_sessions($register, $userId, $userCapabilities);
    $userFullName = fullname($userSessions->userAggregates->user);
} else {
    $trackedUsers = new attendanceregister_tracked_users($register, $userCapabilities);
}


// ===========================
// Pepare PAGE for rendering
// ===========================
// Setup PAGE
$PAGE->set_url($url->out());
$PAGE->set_context($context);
$titleStr = $course->shortname . ': ' . $register->name . ( ($userId) ? ( ': ' . $userFullName ) : ('') );
$PAGE->set_title(format_string($titleStr));

$PAGE->set_heading($course->fullname);
if ($doShowPrintableVersion) {
    $PAGE->set_pagelayout('popup');
}

// Add User's Register Navigation node
if ( $userId ) {
    $registerNavNode = $PAGE->navigation->find($cm->id, navigation_node::TYPE_ACTIVITY);
    $userNavNode = $registerNavNode->add( $userFullName, $url );
    $userNavNode->make_active();
}

echo $OUTPUT->header();

$headingStr = $register->name . ( ( $userId ) ? (': ' . fullname($userSessions->userAggregates->user) ) : ('') );
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
if ($doShowOfflineSessionForm && !$doShowPrintableVersion) {

    // Prepare Form
    $customFormData = array('register' => $register, 'courses' => $userSessions->trackedCourses->courses);
    $mform = new mod_attendanceregister_selfcertification_edit_form(null, $customFormData);


    // Process Self.Cert Form submission
    if ($mform->is_cancelled()) {
        // Cancel
        redirect($PAGE->url);
    } else if ($doSaveOfflineSession && ($formData = $mform->get_data())) {
        // Save Session
        $userId = $USER->id; // User is always current User!
        attendanceregister_save_offline_session($register, $userId, $formData);

        // Notification & Continue button
        echo $OUTPUT->notification(get_string('offline_session_saved', 'attendanceregister'), 'notifysuccess');
        echo $OUTPUT->continue_button(attendanceregister_makeUrl($register, $userId));
        $doShowContents = false;
    }
}

//// Process Recalculate
if ($doShowContents && $doRecalculate) {

    if ($userId) {
        // Recalculate Session for one User
        $progressbar = new progress_bar('recalcbar', 500, true);
        attendanceregister_force_recalc_user_sessions($register, $userId, $progressbar);

        // Reload User's Sessions
        $userSessions = new attendanceregister_user_sessions($register, $userId, $userCapabilities);
    } else {
        // Recalculate Session for all User
        // Turn off time limit: recalculation can be slow
        set_time_limit(0);

        // Iterate each user and recalculate Sessions
        foreach ($trackedUsers->users as $user) {
            // Recalculate Session for one User
            $progressbar = new progress_bar('recalcbar_' . $user->id, 500, true);
            attendanceregister_force_recalc_user_sessions($register, $user->id, $progressbar);
        }
        // Reload All Users Sessions
        $trackedUsers = new attendanceregister_tracked_users($register, $userCapabilities);
    }

    // Notification & Continue button
    echo $OUTPUT->notification(get_string('recalc_complete', 'attendanceregister'), 'notifysuccess');
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
    // Show User's Sessions
    if ($userId) {

        /// Update sessions

        // If Update-on-view is enabled and it is not executing Recalculate and is not
        // in Printable-version, updates User's Sessions (and aggregates)
        if (ATTENDANCEREGISTER_UPDATE_SESSIONS_ON_VIEW && !$doShowPrintableVersion && !$doRecalculate) {
            $progressbar = new progress_bar('recalcbar', 500, true);
            $updated = attendanceregister_update_user_sessions($register, $userId, $progressbar);

            // Reload User's Sessions if updated
            if ($updated) {
                $userSessions = new attendanceregister_user_sessions($register, $userId, $userCapabilities);
            }
            echo '<hr />';
        }

        /// Button bar

        echo $OUTPUT->container_start('attendanceregister_buttonbar');
        // Recalculate this User's Sessions (if allowed & !printable )
        if ($userCapabilities->canRecalcSessions && !$doShowPrintableVersion) {
            $linkUrl = attendanceregister_makeUrl($register, $userId, null, ATTENDANCEREGISTER_ACTION_RECALCULATE);
            echo $OUTPUT->single_button($linkUrl, get_string('force_recalc_user_session', 'attendanceregister'), 'get');
        }
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
        if ($mform && !$doShowPrintableVersion) {
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
    // Show list of Tracked Users summary
    else {

        /// Button bar
        echo $OUTPUT->container_start('attendanceregister_buttonbar');
        // Recalculate Sessions button (only if allowed & !printable)
        if ($userCapabilities->canRecalcSessions && !$doShowPrintableVersion) {
            $linkUrl = attendanceregister_makeUrl($register, null, null, ATTENDANCEREGISTER_ACTION_RECALCULATE);
            echo $OUTPUT->single_button($linkUrl, get_string('force_recalc_all_session', 'attendanceregister'), 'get');
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