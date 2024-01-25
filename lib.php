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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    local_mail
 * @copyright  2012-2014 Institut Obert de Catalunya <https://ioc.gencat.cat>
 * @copyright  2014-2020 Marc Català <reskit@gmail.com>
 * @copyright  2016-2018 Albert Gasset <albertgasset@fsfe.org>
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_mail\course;
use local_mail\external;
use local_mail\message;
use local_mail\output\strings;
use local_mail\settings;
use local_mail\user;

function local_mail_pluginfile(
    $course,
    $cm,
    $context,
    $filearea,
    $args,
    $forcedownload,
    array $options = []
) {
    global $SITE;

    require_login($SITE, false);

    $user = user::current();

    if (!settings::is_installed() || !$user) {
        return false;
    }

    // Check message.
    $messageid = (int) array_shift($args);
    $message = message::get($messageid, IGNORE_MISSING);
    if ($filearea != 'message' || !$message || !$user || !$user->can_view_files($message)) {
        return false;
    }

    // Get file.
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_mail/$filearea/$messageid/$relativepath";
    $file = $fs->get_file_by_hash(sha1($fullpath));
    if (!$file || $file->is_directory()) {
        return false;
    }

    if (PHPUNIT_TEST) {
        return $file;
    }

    // @codeCoverageIgnoreStart
    send_stored_file($file, null, 0, true, $options);
    // @codeCoverageIgnoreEnd
}

/**
 * Renders the navigation bar popover.
 *
 * @param renderer_base $renderer
 * @return string The HTML
 */
function local_mail_render_navbar_output(\renderer_base $renderer) {
    global $COURSE, $PAGE;

    $user = user::current();

    if (!settings::is_installed() || !$user || !course::get_by_user($user)) {
        return '';
    }

    // Fallback link to avoid layout changes during page load.
    $url = new moodle_url('/local/mail/view.php', ['t' => 'inbox']);
    $title = strings::get('pluginname');
    $class = 'nav-link btn h-100 d-flex align-items-center px-2 py-0';

    $viewurl = new moodle_url('/local/mail/view.php');
    if ($PAGE->url->compare($viewurl, URL_MATCH_BASE)) {
        // Menu is handled from the view page.
        $icon = html_writer::tag('i', '', ['class' => 'icon fa fa-fw fa-spinner fa-pulse m-0', 'style' => "font-size: 16px"]);
        $spinner = html_writer::tag('div', $icon, ['class' => $class]);
        $container = html_writer::div($spinner, 'popover-region', ['id' => 'local-mail-navbar']);
        return $container;
    } else {
        // Other page in the site.
        $icon = html_writer::tag('i', '', ['class' => 'icon fa fa-fw fa-envelope-o m-0', 'style' => "font-size: 16px"]);
        $attributes = ['href' => $url, 'class' => $class, 'title' => $title];
        $link = html_writer::tag('a', $icon, $attributes);
        $container = html_writer::div($link, 'popover-region', ['id' => 'local-mail-navbar']);

        // Pass all data via a script tag to avoid web service requests.
        $courses = external::get_courses_raw();
        $courseid = 0;
        if (array_search($COURSE->id, array_column($courses, 'id')) !== false) {
            $courseid = (int) $COURSE->id;
        }
        $data = [
            'userid' => $user->id,
            'courseid' => $courseid,
            'settings' => (array) settings::get(),
            'strings' => strings::get_many([
                'allcourses',
                'bcc',
                'cc',
                'changecourse',
                'compose',
                'course',
                'drafts',
                'inbox',
                'nocoursematchestext',
                'pluginname',
                'preferences',
                'sendmail',
                'sentplural',
                'starredplural',
                'to',
                'trash',
            ]),
            'courses' => $courses,
            'labels' => external::get_labels_raw(),
        ];
        $datascript = html_writer::script('window.local_mail_navbar_data = ' . json_encode($data));
        $renderer = $PAGE->get_renderer('local_mail');
        $sveltescript = $renderer->svelte_script('src/navigation.ts');
        return $container . $datascript . $sveltescript;
    }
}
