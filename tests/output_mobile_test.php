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
 * @covers \local_mail\output\mobile
 */
class output_mobile_test extends testcase {

    public function test_init() {
        global $CFG;

        $generator = self::getDataGenerator();
        $course = new course($generator->create_course());
        $user1 = new user($generator->create_user());
        $user2 = new user($generator->create_user());
        $generator->enrol_user($user1->id, $course->id);
        self::setUser($user1->id);

        // User with courses.
        self::assertEquals(
            ['javascript' => file_get_contents("$CFG->dirroot/local/mail/classes/output/mobile-init.js")],
            output\mobile::init(),
        );

        // User with no courses.
        self::setUser($user2->id);
        self::assertEquals(['disabled' => true], output\mobile::init());

        // Not installed.
        unset_config('version', 'local_mail');
        self::setUser($user1->id);
        self::assertEquals(['disabled' => true], output\mobile::init());
    }

    public function test_view() {
        global $CFG;

        self::assertEquals(
            [
                'templates' => [
                    [
                        'id' => 'main',
                        'html' => '<core-iframe src="' . $CFG->wwwroot . '/local/mail/view.php?t=inbox&m=123"></core-iframe>',
                    ],
                ],
                'javascript' => file_get_contents("$CFG->dirroot/local/mail/classes/output/mobile-view.js"),
            ],
            output\mobile::view(['t' => 'inbox', 'm' => 123])
        );
    }
}
