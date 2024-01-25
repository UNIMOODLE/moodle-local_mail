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
 * @copyright  2014-2019 Marc Català <reskit@gmail.com>
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_mail\course;
use local_mail\external;
use local_mail\output\strings;
use local_mail\settings;
use local_mail\user;

require_once('../../config.php');
require_once("$CFG->libdir/filelib.php");

global $PAGE;

$appid = optional_param('appid', '', PARAM_NOTAGS);
$applang = optional_param('applang', '', PARAM_LANG);

// Use languuage from the app.
if ($appid != '' && $applang != '') {
    force_current_language($applang);
}

require_login(null, false);

if (!settings::is_installed()) {
    throw new moodle_exception('errorpluginnotinstalled', 'local_mail');
}

$url = new moodle_url('/local/mail/view.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout($appid != '' ? 'embedded' : 'base');
$PAGE->set_title(strings::get('pluginname'));

$user = user::current();

if ($user && course::get_by_user($user)) {
    // Initial data passed via a script tag.
    $data = [
        'userid' => $user->id,
        'settings' => (array) settings::get(),
        'preferences' => external::get_preferences_raw(),
        'strings' => strings::get_all(),
        'mobile' => $appid != '',
    ];

    // Prepare script and styles before sending header.
    $renderer = $PAGE->get_renderer('local_mail');
    $sveltescript = $renderer->svelte_script('src/view.ts');

    // Print content.
    echo $OUTPUT->header();
    echo html_writer::div('', '', ['id' => 'local-mail-view']);
    echo html_writer::script('window.local_mail_view_data = ' . json_encode($data));
    echo $sveltescript;
    echo $OUTPUT->footer();
} else {
    // Print error.
    echo $OUTPUT->header();
    echo $OUTPUT->notification(strings::get('errornocourses'), 'warning', false);
    echo $OUTPUT->footer();
}
