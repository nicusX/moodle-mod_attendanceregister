<?php

/**
 * Add Attendance Register instance form
 *
 * @package    mod
 * @subpackage attendanceregister
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/course/moodleform_mod.php');

class mod_attendanceregister_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $COURSE, $DB;
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Instance name
        $mform->addElement('text', 'name', get_string('registername', 'attendanceregister'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Intro
        $this->add_intro_editor(true);

        // Register Type
        $register_types = attendanceregister_get_register_types( );
        $mform->addElement('select', 'type', get_string('registertype', 'attendanceregister'), $register_types);
        $mform->addHelpButton('type', 'registertype', 'attendanceregister');
        $mform->setDefault('type', ATTENDANCEREGISTER_TYPE_COURSE );


        // Session timeout
        $minutes = ' '.get_string('minutes');
        $session_duration_choices = array (
                5=> ('5'.$minutes),
                10=> ('10'.$minutes),
                15=> ('15'.$minutes),
                20=> ('20'.$minutes),
                30=> ('30'.$minutes),
                45=> ('45'.$minutes),
                60=> ('60'.$minutes),
            );
        $mform->addElement('select', 'sessiontimeout', get_string('sessiontimeout', 'attendanceregister'), $session_duration_choices);
        $mform->addHelpButton('sessiontimeout', 'sessiontimeout', 'attendanceregister');
        $mform->setDefault('sessiontimeout', ATTENDANCEREGISTER_DEFAULT_SESSION_TIMEOUT );


        // Offline Session self-certification
        $mform->addElement('header', '', get_string('offline_sessions_certification', 'attendanceregister'));

        // Enable self-certifications
        $mform->addElement('checkbox', 'offlinesessions', get_string('enable_offline_sessions_certification', 'attendanceregister'));
        $mform->addHelpButton('offlinesessions', 'offline_sessions_certification', 'attendanceregister');
        $mform->setDefault('offlinesessions', false );

        // Number of day before a self-certification will be accepted
        $day = ' '.get_string('day');
        $days = ' '.get_string('days');
        $dayscertificable = array(
            1=> ('1'.$day),
            2=> ('2'.$days),
            3=> ('3'.$days),
            4=> ('4'.$days),
            5=> ('5'.$days),
            6=> ('6'.$days),
            7=> ('7'.$days),
            10=> ('10'.$days),
            14=> ('14'.$days),
            21=> ('21'.$days),
            30=> ('30'.$days),
            60=> ('60'.$days),
            90=> ('90'.$days),
            120=> ('12'.$days),
            180=> ('180'.$days),
            365=> ('365'.$days),
        );
        $mform->addElement('select', 'dayscertificable', get_string('dayscertificable', 'attendanceregister'), $dayscertificable);
        $mform->addHelpButton('dayscertificable', 'dayscertificable', 'attendanceregister');
        $mform->setDefault('dayscertificable', ATTENDANCEREGISTER_DEFAULT_DAYS_CERTIFICABLE );
        $mform->disabledIf('dayscertificable', 'offlinesessions');


        // Offline Sessions self-cert comments
        $mform->addElement('checkbox', 'offlinecomments', get_string('offlinecomments', 'attendanceregister'));
        $mform->addHelpButton('offlinecomments', 'offlinecomments', 'attendanceregister');
        $mform->setDefault('offlinecomments', false );
        $mform->disabledIf('offlinecomments', 'offlinesessions');

        // Mandatory Offline Sessions self-cert comments
        $mform->addElement('checkbox', 'mandatoryofflinecomm', get_string('mandatory_offline_sessions_comments', 'attendanceregister'));
        $mform->setDefault('mandatoryofflinecomm', false );
        $mform->disabledIf('mandatoryofflinecomm', 'offlinesessions');
        $mform->disabledIf('mandatoryofflinecomm', 'offlinecomments');

        // Offline Certifiations allow to specify Course
        $mform->addElement('checkbox', 'offlinespecifycourse', get_string('offlinespecifycourse', 'attendanceregister'));
        $mform->addHelpButton('offlinespecifycourse', 'offlinespecifycourse', 'attendanceregister');
        $mform->setDefault('offlinespecifycourse', false );
        $mform->disabledIf('offlinespecifycourse', 'offlinesessions');

        // Mandatory Offline Certification Course specification
        $mform->addElement('checkbox', 'mandofflspeccourse', get_string('mandatoryofflinespecifycourse', 'attendanceregister'));
        $mform->addHelpButton('mandofflspeccourse', 'mandatoryofflinespecifycourse', 'attendanceregister');
        $mform->setDefault('mandofflspeccourse', false );
        $mform->disabledIf('mandofflspeccourse', 'offlinesessions');
        $mform->disabledIf('mandofflspeccourse', 'offlinespecifycourse');


        // Standard controls
        $this->standard_coursemodule_elements( );

        // Buttons
        $this->add_action_buttons();
    }

}