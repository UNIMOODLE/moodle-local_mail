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
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mail;

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/testcase.php');

/**
 * @covers \local_mail\settings
 */
class settings_test extends testcase {

    public function test_defaults() {
        set_config('maxbytes', 123000);

        $settings = settings::defaults();

        self::assertTrue($settings->enablebackup);
        self::assertEquals(100, $settings->maxrecipients);
        self::assertEquals(100, $settings->usersearchlimit);
        self::assertEquals(20, $settings->maxfiles);
        self::assertEquals(123000, $settings->maxbytes);
        self::assertEquals(['starred', 'sent', 'drafts', 'trash'], $settings->globaltrays);
        self::assertEquals('none', $settings->coursetrays);
        self::assertEquals('fullname', $settings->coursetraysname);
        self::assertEquals('fullname', $settings->coursebadges);
        self::assertEquals(20, $settings->coursebadgeslength);
        self::assertEquals('fullname', $settings->filterbycourse);
        self::assertTrue($settings->incrementalsearch);
        self::assertEquals(1000, $settings->incrementalsearchlimit);
        self::assertEquals('hidden', $settings->courselink);
    }

    public function test_get() {
        set_config('maxbytes', 123000);
        set_config('enablebackup', '0', 'local_mail');
        set_config('maxrecipients', '20', 'local_mail');
        set_config('usersearchlimit', '50', 'local_mail');
        set_config('maxfiles', '5', 'local_mail');
        set_config('maxbytes', '45000', 'local_mail');
        set_config('globaltrays', 'sent,trash', 'local_mail');
        set_config('coursetrays', 'unread', 'local_mail');
        set_config('coursetraysname', 'shortname', 'local_mail');
        set_config('coursebadges', 'hidden', 'local_mail');
        set_config('coursebadgeslength', '10', 'local_mail');
        set_config('filterbycourse', 'hidden', 'local_mail');
        set_config('incrementalsearch', '0', 'local_mail');
        set_config('incrementalsearchlimit', '2000', 'local_mail');
        set_config('courselink', 'fullname', 'local_mail');
        set_config('message_provider_local_mail_mail_enabled', 'popup,email', 'message');
        set_config('email_provider_local_mail_mail_locked', '1', 'message');

        $settings = settings::get();

        self::assertFalse($settings->enablebackup);
        self::assertEquals(20, $settings->maxrecipients);
        self::assertEquals(50, $settings->usersearchlimit);
        self::assertEquals(5, $settings->maxfiles);
        self::assertEquals(45000, $settings->maxbytes);
        self::assertEquals(['sent', 'trash'], $settings->globaltrays);
        self::assertEquals('unread', $settings->coursetrays);
        self::assertEquals('shortname', $settings->coursetraysname);
        self::assertEquals('hidden', $settings->coursebadges);
        self::assertEquals(10, $settings->coursebadgeslength);
        self::assertEquals('hidden', $settings->filterbycourse);
        self::assertFalse($settings->incrementalsearch);
        self::assertEquals(2000, $settings->incrementalsearchlimit);
        self::assertEquals('fullname', $settings->courselink);
        self::assertEquals([
            [
                'name' => 'popup',
                'displayname' => get_string('pluginname', 'message_popup'),
                'locked' => false,
                'enabled' => true,
            ],
            [
                'name' => 'email',
                'displayname' => get_string('pluginname', 'message_email'),
                'locked' => true,
                'enabled' => true,
            ],
        ], $settings->messageprocessors);

        // Empty global trays.

        set_config('globaltrays', '', 'local_mail');
        $settings = settings::get();
        self::assertEquals([], $settings->globaltrays);
    }

    public function test_is_installed() {
        self::assertTrue(settings::is_installed());

        set_config('version', 123, 'local_mail');

        self::assertFalse(settings::is_installed());
    }
}
