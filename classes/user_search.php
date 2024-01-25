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

class user_search {
    /** @var user Search users visible by this user. */
    public user $user;

    /** @var course Search users in this course. */
    public course $course;

    /** @var int If not zero, search users with this role. */
    public int $roleid = 0;

    /** @var int If not zero, search users in this group. */
    public int $groupid = 0;

    /** @var string If not empty, search users with a full name that contains this text. */
    public string $fullname = '';

    /** @var int[] If not empty, search users with one of these IDs. */
    public array $include = [];

    /**
     * Constructs the criteria for searching users.
     *
     * @param user $user Search users visible by this user.
     * @param course $course Search users in this course.
     */
    public function __construct(user $user, course $course) {
        $this->user = $user;
        $this->course = $course;
    }

    /**
     * Convert search parameters to a string.
     *
     * Used for debugging.
     */
    public function __toString(): string {
        $str = 'user: ' . $this->user->id;
        $str .= "\ncourse: " . $this->course->id;
        if ($this->roleid) {
            $str .= "\nrole: " . $this->roleid;
        }
        if ($this->groupid) {
            $str .= "\ngroup: " . $this->groupid;
        }
        if ($this->fullname) {
            $str .= "\nfullname: " . $this->fullname;
        }
        return $str;
    }

    /**
     * Counts the number of users that match the search parameters.
     *
     * @return int
     */
    public function count(): int {
        global $DB;

        [$sql, $params] = $this->get_base_sql('COUNT(*)');

        return $DB->count_records_sql($sql, $params);
    }

    /**
     * Gets users that match the search parameters.
     *
     * @param int $offset Skip this number of users.
     * @param int $limit Maximum number of users, 0 means no limit.
     * @return user[] Found users, indexed by ID.
     */
    public function get(int $offset = 0, int $limit = 0): array {
        global $DB;

        $fields = \core_user\fields::get_picture_fields();

        [$sql, $params] = $this->get_base_sql(implode(',', $fields));

        [$sort, $sortparams] = users_order_by_sql();
        $sql .= ' ORDER BY ' . $sort;
        $params = array_merge($params, $sortparams);

        $records = $DB->get_records_sql($sql, $params, $offset, $limit);

        $users = [];
        foreach ($records as $record) {
            $users[$record->id] = new user($record);
        }

        return $users;
    }

    /**
     * Returns the SQL for searching users.
     *
     * @param string $fields Fields to use in SELECT.
     * @return mixed[] Array with SQL and parameters.
     */
    private function get_base_sql(string $fields): array {
        global $DB;

        // Enrolled.
        $context = $this->course->get_context();
        $ejoin = get_enrolled_join($context, 'u.id', true);
        $joins = $ejoin->joins;
        $wheres = $ejoin->wheres;
        $params = $ejoin->params;

        // Capability.
        $capjoin = get_with_capability_join($context, 'local/mail:usemail', 'u.id');
        $joins .= ' ' . $capjoin->joins;
        $wheres .= ' AND ' . $capjoin->wheres;
        $params = array_merge($params, $capjoin->params);

        // Exclude user.
        $wheres .= ' AND u.id <> :userid';
        $params['userid'] = $this->user->id;

        // Exclude users with same role.
        if (!has_capability('local/mail:mailsamerole', $context, $this->user->id)) {
            $samerolesql = 'SELECT ra2.userid'
                . ' FROM {role_assignments} ra1'
                . ' JOIN {role_assignments} ra2 ON ra1.roleid = ra2.roleid'
                . ' WHERE ra1.userid = :sameroleurserid'
                . ' AND ra1.contextid = :samerolecontextid1'
                . ' AND ra2.contextid = :samerolecontextid2';
            $wheres .= " AND u.id NOT IN ($samerolesql)";
            $params['sameroleurserid'] = $this->user->id;
            $params['samerolecontextid1'] = $context->id;
            $params['samerolecontextid2'] = $context->id;
        }

        // Role.
        if ($this->roleid) {
            $joins .= ' JOIN {role_assignments} ra3 ON ra3.userid = u.id';
            $wheres .= ' AND ra3.contextid = :contextid AND ra3.roleid = :roleid';
            $params['contextid'] = $context->id;
            $params['roleid'] = $this->roleid;
        }

        // Group.
        if ($this->groupid || $this->course->groupmode == SEPARATEGROUPS) {
            if ($this->course->groupmode == SEPARATEGROUPS) {
                $groupids = array_keys($this->course->get_viewable_groups($this->user));
                if ($this->groupid) {
                    $groupids = array_intersect($groupids, [$this->groupid]);
                }
            } else {
                $groupids = [$this->groupid];
            }
            if ($groupids) {
                [$groupsql, $groupparams] = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED, 'group');
                $wheres .= " AND u.id IN (SELECT gm.userid FROM {groups_members} gm WHERE gm.groupid $groupsql)";
                $params = array_merge($params, $groupparams);
            } else {
                // No groups, return an empty result.
                $wheres .= ' AND 1 = 2';
            }
        }

        // Full name.
        if ($this->fullname) {
            $fullnamefield = $DB->sql_fullname('u.firstname', 'u.lastname');
            $wheres .= ' AND ' . $DB->sql_like($fullnamefield, ':fullname', false, false);
            $params['fullname'] = '%' . $DB->sql_like_escape($this->fullname) . '%';
        }

        // IDs.
        if ($this->include) {
            [$includesql, $includeparams] = $DB->get_in_or_equal($this->include, SQL_PARAMS_NAMED, 'id');
            $wheres .= ' AND u.id ' . $includesql;
            $params = array_merge($params, $includeparams);
        }

        $sql = "SELECT $fields FROM {user} WHERE id IN (SELECT DISTINCT u.id FROM {user} u $joins WHERE $wheres)";

        return [$sql, $params];
    }
}
