<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos

/**
 * Version details
 *
 * @package    local_mail
 * @copyright  2014 Institut Obert de Catalunya <https://ioc.gencat.cat>
 * @copyright  2014-2017 Marc Català <reskit@gmail.com>
 * @copyright  2016 Albert Gasset <albertgasset@fsfe.org>
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_local_mail_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2015121400) {

        // Clean obsolete local_mail_fullmessage preference.
        $params = ['name' => 'local_mail_fullmessage'];
        $DB->execute('DELETE FROM {user_preferences} WHERE name = :name', $params);

        upgrade_plugin_savepoint(true, 2015121400, 'local', 'mail');
    }

    if ($oldversion < 2016070100) {

        // Define field attachments to be added to local_mail_messages.
        $table = new xmldb_table('local_mail_messages');
        $field = new xmldb_field('attachments', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'format');

        // Conditionally launch add field attachments.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mail savepoint reached.
        upgrade_plugin_savepoint(true, 2016070100, 'local', 'mail');
    }

    if ($oldversion < 2016070101) {

        // Update field attachments.
        $sql = 'SELECT f.itemid, COUNT(*) as numfiles
                FROM {files} f
                WHERE f.component = :component
                AND f.filearea = :filearea
                AND f.filename <> :filename
                GROUP BY f.itemid';
        $params = [
            'component' => 'local_mail',
            'filearea' => 'message',
            'filename' => '.',
        ];
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $record) {
            $DB->set_field('local_mail_messages', 'attachments', $record->numfiles, ['id' => $record->itemid]);
        }
        $rs->close();

        // Mail savepoint reached.
        upgrade_plugin_savepoint(true, 2016070101, 'local', 'mail');
    }

    if ($oldversion < 2016070103) {

        // Clean obsolete settings.
        unset_config('cronenabled', 'local_mail');
        unset_config('cronstart', 'local_mail');
        unset_config('cronstop', 'local_mail');
        unset_config('cronduration', 'local_mail');

        upgrade_plugin_savepoint(true, 2016070103, 'local', 'mail');
    }

    if ($oldversion < 2017070400) {

        // Define field normalizedsubject to be added to local_mail_messages.
        $table = new xmldb_table('local_mail_messages');
        $field = new xmldb_field('normalizedsubject', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'time');

        // Conditionally launch add field normalizedsubject.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mail savepoint reached.
        upgrade_plugin_savepoint(true, 2017070400, 'local', 'mail');
    }

    if ($oldversion < 2017070401) {

        // Define field normalizedcontent to be added to local_mail_messages.
        $table = new xmldb_table('local_mail_messages');
        $field = new xmldb_field('normalizedcontent', XMLDB_TYPE_TEXT, null, null, null, null, null, 'normalizedsubject');

        // Conditionally launch add field normalizedcontent.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mail savepoint reached.
        upgrade_plugin_savepoint(true, 2017070401, 'local', 'mail');
    }

    if ($oldversion < 2017070402) {

        $normalize = function ($text) {
            return trim(preg_replace('/(*UTF8)[^\p{L}\p{N}]+/', ' ', $text));
        };
        $lastid = 0;
        while (true) {
            $transaction = $DB->start_delegated_transaction();
            $select = 'id > :lastid AND normalizedsubject IS NULL';
            $params = ['lastid' => $lastid];
            $fields = 'id, courseid, subject, content, format';
            $records = $DB->get_records_select('local_mail_messages', $select, $params, 'id', $fields, 0, 100);
            foreach ($records as $record) {
                $context = context_course::instance($record->courseid);
                $content = file_rewrite_pluginfile_urls(
                    $record->content,
                    'pluginfile.php',
                    $context->id,
                    'local_mail',
                    'message',
                    $record->id
                );
                $content = format_text($record->content, $record->format, ['filter' => false, 'nocache' => true]);
                $data = new stdClass;
                $data->id = $record->id;
                $data->normalizedsubject = $normalize($record->subject);
                $data->normalizedcontent = $normalize(html_to_text($content, 0, false));
                $DB->update_record('local_mail_messages', $data);
                $lastid = $record->id;
            }
            $transaction->allow_commit();
            if (!$records) {
                break;
            }
        }

        upgrade_plugin_savepoint(true, 2017070402, 'local', 'mail');
    }

    // Version 2.0.

    // Change type of field role on table local_mail_message_users to integer.

    if ($oldversion < 2023060700) {
        $table = new xmldb_table('local_mail_message_users');

        // Rename field to tmp_role.
        $field = new xmldb_field('role', XMLDB_TYPE_CHAR, '4', null, XMLDB_NOTNULL, null, null, 'userid');
        if (!$dbman->field_exists($table, 'tmp_role')) {
            $dbman->rename_field($table, $field, 'tmp_role');
        }

        // Add new field with default value 0.
        $field = new xmldb_field('role', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'userid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Copy data from tmp_role.
        $DB->set_field('local_mail_message_users', 'role', 1, ['tmp_role' => 'from']);
        $DB->set_field('local_mail_message_users', 'role', 2, ['tmp_role' => 'to']);
        $DB->set_field('local_mail_message_users', 'role', 3, ['tmp_role' => 'cc']);
        $DB->set_field('local_mail_message_users', 'role', 4, ['tmp_role' => 'bcc']);

        // Remove default value from new field.
        $field = new xmldb_field('role', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'userid');
        $dbman->change_field_default($table, $field);

        upgrade_plugin_savepoint(true, 2023060700, 'local', 'mail');
    }

    if ($oldversion < 2023060701) {
        $table = new xmldb_table('local_mail_message_users');

        // Drop temporary field.
        $field = new xmldb_field('tmp_role');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2023060701, 'local', 'mail');
    }

    // Add redundant courseid, draft and time fields to local_mail_message_users.

    if ($oldversion < 2023060702) {
        $table = new xmldb_table('local_mail_message_users');

        // Create field courseid with default value 0.
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'messageid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Create field draft with default value 0.
        $field = new xmldb_field('draft', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'courseid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Create field time with default value 0.
        $field = new xmldb_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'draft');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Copy data from local_mail_messages.
        $fromsql = 'FROM {local_mail_messages} m WHERE m.id = mu.messageid';
        $sql = 'UPDATE {local_mail_message_users} mu SET'
            . " courseid = (SELECT m.courseid $fromsql),"
            . " draft = (SELECT m.draft $fromsql),"
            . " time = (SELECT m.time $fromsql)";
        $DB->execute($sql);

        // Remove default value from field courseid.
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'messageid');
        $dbman->change_field_default($table, $field);

        // Remove default value from field draft.
        $field = new xmldb_field('draft', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'courseid');
        $dbman->change_field_default($table, $field);

        // Remove default value from field time.
        $field = new xmldb_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'draft');
        $dbman->change_field_default($table, $field);

        upgrade_plugin_savepoint(true, 2023060702, 'local', 'mail');
    }

    // Add redundant courseid, draft, time, role, unread, starred and deleted fields to local_mail_message_labels.

    if ($oldversion < 2023060704) {
        $table = new xmldb_table('local_mail_message_labels');

        // Create field courseid with default value 0.
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'messageid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Create field draft with default value 0.
        $field = new xmldb_field('draft', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'courseid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Create field time with default value 0.
        $field = new xmldb_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'draft');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Create field role with default value 0.
        $field = new xmldb_field('role', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'labelid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Create field unread with default value 0.
        $field = new xmldb_field('unread', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'role');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Create field starred with default value 0.
        $field = new xmldb_field('starred', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'unread');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Create field deleted with default value 0.
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'starred');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Copy courseid, draft and time from local_mail_messages.
        $fromsql = 'FROM {local_mail_messages} m WHERE m.id = ml.messageid';
        $sql = 'UPDATE {local_mail_message_labels} ml SET'
            . " courseid = (SELECT m.courseid $fromsql),"
            . " draft = (SELECT m.draft $fromsql),"
            . " time = (SELECT m.time $fromsql)";
        $DB->execute($sql);

        // Copy role, unread, starred and deleted from local_mail_message_users.
        $fromsql = 'FROM {local_mail_message_users} mu'
            . ' JOIN {local_mail_labels} l ON l.userid = mu.userid'
            . ' WHERE l.id = ml.labelid AND mu.messageid = ml.messageid';
        $sql = 'UPDATE {local_mail_message_labels} ml SET'
            . " role = (SELECT mu.role $fromsql),"
            . " unread = (SELECT mu.unread $fromsql),"
            . " starred = (SELECT mu.starred $fromsql),"
            . " deleted = (SELECT mu.deleted $fromsql)";
        $DB->execute($sql);

        // Remove default value from field courseid.
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'messageid');
        $dbman->change_field_default($table, $field);

        // Remove default value from field draft.
        $field = new xmldb_field('draft', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'courseid');
        $dbman->change_field_default($table, $field);

        // Remove default value from field time.
        $field = new xmldb_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'draft');
        $dbman->change_field_default($table, $field);

        // Remove default value from field role.
        $field = new xmldb_field('role', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'labelid');
        $dbman->change_field_default($table, $field);

        // Remove default value from field unread.
        $field = new xmldb_field('unread', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'role');
        $dbman->change_field_default($table, $field);

        // Remove default value from field starred.
        $field = new xmldb_field('starred', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'unread');
        $dbman->change_field_default($table, $field);

        // Remove default value from field deleted.
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'starred');
        $dbman->change_field_default($table, $field);

        upgrade_plugin_savepoint(true, 2023060704, 'local', 'mail');
    }

    // Add new indexes to local_mail_message_users and local_mail_message_labels.

    if ($oldversion < 2023060705) {

        $table = new xmldb_table('local_mail_message_users');
        $index = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index(
            'userid',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid', 'courseid', 'draft', 'role', 'unread', 'starred', 'deleted', 'time', 'messageid']
        );
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('local_mail_message_labels');
        $index = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index(
            'labelid',
            XMLDB_INDEX_NOTUNIQUE,
            ['labelid', 'courseid', 'draft', 'role', 'unread', 'starred', 'deleted', 'time', 'messageid']
        );
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2023060705, 'local', 'mail');
    }

    // Drop local_mail_index table.

    if ($oldversion < 2023092200) {
        $table = new xmldb_table('local_mail_index');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        upgrade_plugin_savepoint(true, 2023092200, 'local', 'mail');
    }

    return true;
}
