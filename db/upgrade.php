<?php

// This file keeps track of upgrades to
// the choice module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

function xmldb_attendanceregister_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012081004) {
        //// Add attendanceregister_session.addedbyuserid column

        // Define field addedbyuser to be added to attendanceregister_session
        $table = new xmldb_table('attendanceregister_session');
        $field = new xmldb_field('addedbyuserid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED );

        // Launch add field addedbyuserid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        //// Add attendanceregister.pendingrecalc column

        // Define field addedbyuser to be added to attendanceregister
        $table = new xmldb_table('attendanceregister');
        $field = new xmldb_field('pendingrecalc', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 1 );

        // Launch add field addedbyuserid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2012081004, 'attendanceregister');
    }

    if ( $oldversion < 2013020604 ) {
        /// Issue #36 and #42
        
        // Rename field attendanceregister_session.online to onlinessess
        $table = new xmldb_table('attendanceregister_session');
        $field = new xmldb_field('online', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 1 );
        if ( $dbman->field_exists($table, $field) ) {
            // Rename field
            $dbman->rename_field($table, $field, 'onlinesess');
        }
        
        // Rename field attendanceregister_aggregate.online to onlinessess
        $table = new xmldb_table('attendanceregister_aggregate');
        $field = new xmldb_field('online', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, null, null, 1  );
        if ( $dbman->field_exists($table, $field) ) {
            // Rename field
            $dbman->rename_field($table, $field, 'onlinesess');
        }
        
    }
    
    return true;
}


