<?php

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id',PARAM_INT);   // course

    $PAGE->set_url('/mod/choice/index.php', array('id'=>$id));

    if (!$course = $DB->get_record('course', array('id'=>$id))) {
        print_error('invalidcourseid');
    }

    require_course_login($course);
    $PAGE->set_pagelayout('incourse');

    $strregister = get_string("modulename", "attendanceregister");
    $strregisters = get_string("modulenameplural", "attendanceregister");
    $strsectionname  = get_string('sectionname', 'format_'.$course->format);
    $PAGE->set_title($strregisters);
    $PAGE->set_heading($course->fullname);
    $PAGE->navbar->add($strregisters);
    echo $OUTPUT->header();

    if (! $registers = get_all_instances_in_course("attendanceregister", $course)) {
        notice(get_string('thereareno', 'moodle', $strregisters), "../../course/view.php?id=$course->id");
    }

    $usesections = course_format_uses_sections($course->format);
    if ($usesections) {
        $sections = get_all_sections($course->id);
    }

    // XXX Count tracked users

    $timenow = time();

    $table = new html_table();

    if ($usesections) {
        $table->head  = array ($strsectionname, $strregister, get_string('registertype','attendanceregister'), get_string("tracked_users",'attendanceregister'));
        $table->align = array ("center", "left","left", "center");
    } else {
        $table->head  = array ($strregister, get_string('registertype','attendanceregister'),  get_string("tracked_users",'attendanceregister'));
        $table->align = array ("left", "left", "center");
    }

    $currentsection = "";

    foreach ($registers as $register) {
        $trackedUsers = attendanceregister_get_tracked_users($register);
        $aa = 0;
        if (is_array($trackedUsers) ) {
            $aa = count($trackedUsers);
        }

        if ($usesections) {
            $printsection = "";
            if ($register->section !== $currentsection) {
                if ($register->section) {
                    $printsection = get_section_name($course, $sections[$register->section]);
                }
                if ($currentsection !== "") {
                    $table->data[] = 'hr';
                }
                $currentsection = $register->section;
            }
        }
        //Calculate the href
        if (!$register->visible) {
            //Show dimmed if the mod is hidden
            $tt_href = "<a class=\"dimmed\" href=\"view.php?id=$register->coursemodule\">".format_string($register->name,true)."</a>";
        } else {
            //Show normal if the mod is visible
            $tt_href = "<a href=\"view.php?id=$register->coursemodule\">".format_string($register->name,true)."</a>";
        }
        if ($usesections) {
            $table->data[] = array ($printsection, $tt_href, get_string( 'type_'.$register->type,'attendanceregister'),  $aa);
        } else {
            $table->data[] = array ($tt_href, get_string( $register->type,'attendanceregister'), $aa);
        }
    }
    echo "<br />";
    echo html_writer::table($table);

    echo $OUTPUT->footer();
