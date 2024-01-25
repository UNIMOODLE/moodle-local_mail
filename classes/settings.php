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
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mail;

class settings {
    /** @var bool Backup and restore enabled. */
    public bool $enablebackup = true;

    /** @var int Maximum number of recipients allowed per message. */
    public int $maxrecipients = 100;

    /** @var int Maximum number of results displayed in the user search. */
    public int $usersearchlimit = 100;

    /** @var int Maximum number of attachments allowed per message. */
    public int $maxfiles = 20;

    /** @var int Maximum size of attachments allowed per message. */
    public int $maxbytes;

    /** @var string[] Global trays displayed in menus: "starred", "sent", "drafts" and/or "trash". */
    public array $globaltrays = ['starred', 'sent', 'drafts', 'trash'];

    /** @var string Course trays displayed in menus: "none", "unread" or "all". */
    public string $coursetrays = 'none';

    /** @var string Type of course name displayed in menus: "shortname" or "fullname". */
    public string $coursetraysname = 'fullname';

    /** @var string Type of course name displayed in messages: "hidden", "shortname" or "fullname". */
    public string $coursebadges = 'fullname';

    /** @var int Course badges are truncated to this approximate length. */
    public int $coursebadgeslength = 20;

    /** @var string Type of course name used in the filter by course: "hidden", "shortname" or "fullname". */
    public string $filterbycourse = 'fullname';

    /** @var bool Incremental search enabled. */
    public bool $incrementalsearch = true;

    /** @var int Maximum number of recent messages included in incremental search. */
    public int $incrementalsearchlimit = 1000;

    /** @var string Type of course name displayed in the course link: "hidden", "shortname" or "fullname". */
    public string $courselink = 'hidden';

    /** @var array Array of message providers (name, displayname, locked, enabled). */
    public array $messageprocessors = [];

    /**
     * Private constructor.
     */
    private function __construct() {
        global $CFG;

        $this->maxbytes = get_max_upload_file_size($CFG->maxbytes ?? 0);
    }

    /**
     * Returns default settings.
     *
     * @return self
     */
    public static function defaults(): self {
        return new self();
    }

    /**
     * Returns the stored settings.
     *
     * @return self
     */
    public static function get(): self {
        $settings = new self();

        $config = get_config('local_mail');

        if (isset($config->enablebackup)) {
            $settings->enablebackup = (bool) $config->enablebackup;
        }
        if (isset($config->maxrecipients)) {
            $settings->maxrecipients = (int) $config->maxrecipients;
        }
        if (isset($config->usersearchlimit)) {
            $settings->usersearchlimit = (int) $config->usersearchlimit;
        }
        if (isset($config->maxfiles)) {
            $settings->maxfiles = (int) $config->maxfiles;
        }
        if (isset($config->maxbytes)) {
            $settings->maxbytes = (int) $config->maxbytes;
        }
        if (isset($config->globaltrays)) {
            if ($config->globaltrays) {
                $settings->globaltrays = explode(',', $config->globaltrays);
            } else {
                $settings->globaltrays = [];
            }
        }
        if (isset($config->coursetrays)) {
            $settings->coursetrays = $config->coursetrays;
        }
        if (isset($config->coursetraysname)) {
            $settings->coursetraysname = $config->coursetraysname;
        }
        if (isset($config->coursebadges)) {
            $settings->coursebadges = $config->coursebadges;
        }
        if (isset($config->coursebadgeslength)) {
            $settings->coursebadgeslength = (int) $config->coursebadgeslength;
        }
        if (isset($config->filterbycourse)) {
            $settings->filterbycourse = $config->filterbycourse;
        }
        if (isset($config->incrementalsearch)) {
            $settings->incrementalsearch = (bool) $config->incrementalsearch;
        }
        if (isset($config->incrementalsearchlimit)) {
            $settings->incrementalsearchlimit = (int) $config->incrementalsearchlimit;
        }
        if (isset($config->courselink)) {
            $settings->courselink = $config->courselink;
        }
        if (!get_config('message', 'local_mail_mail_disable')) {
            $enabled = explode(',', get_config('message', 'message_provider_local_mail_mail_enabled'));
            foreach (get_message_processors(true) as $processor) {
                if ($processor->name == 'localmail') {
                    continue;
                }
                $locked = (bool) get_config('message', "{$processor->name}_provider_local_mail_mail_locked");
                $settings->messageprocessors[] = [
                    'name' => $processor->name,
                    'displayname' => get_string('pluginname', 'message_' . $processor->name),
                    'locked' => $locked,
                    'enabled' => array_search($processor->name, $enabled) !== false,
                ];
            }
        }

        return $settings;
    }

    /**
     * Returns whether the plugin is installed and upgraded.
     *
     * @return bool
     */
    public static function is_installed(): bool {
        global $CFG;

        $plugin = new \stdClass();
        include("$CFG->dirroot/local/mail/version.php");

        $version = get_config('local_mail', 'version');

        return $version == $plugin->version;
    }
}
