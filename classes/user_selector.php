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

/**
 * Lib file for local_hugosoares.
 *
 * @package    local_hugosoares
 * @copyright  2024 Hugo Soares
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hugosoares;

use user_selector_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/user/selector/lib.php');

// Custom user selector class
class user_selector extends user_selector_base {
  /**
     * Create instance.
     *
     * @param string $name control name
     * @param array $options should have two elements with keys groupid and courseid.
     */
    public function __construct($name = null, $options = array()) {
        if (is_null($name)) {
            $name = 'removeselect';
        }
        $options['multiselect'] = false;
        $options['includecustomfields'] = true;
        parent::__construct($name, $options);
    }

    public function find_users($search): array {
        // Stripped from an admin plugin in moodle and modified to do the trick.
        global $DB, $CFG;

        [$wherecondition, $params] = $this->search_sql($search, 'u');
        
        $params = array_merge($params, $this->userfieldsparams);

        $fields = 'SELECT u.id, ' . $this->userfieldsselects;

        if ($wherecondition) {
            $wherecondition = "$wherecondition AND u.suspended = 0 AND u.deleted = 0";
        } else {
            $wherecondition = "u.suspended = 0 AND u.deleted = 0";
        }
        $sql = " FROM {user} u $this->userfieldsjoin WHERE $wherecondition";

        [$sort, $sortparams] = users_order_by_sql('u', $search, $this->accesscontext, $this->userfieldsmappings);
        $params = array_merge($params, $sortparams);

        // Sort first by email domain and then by normal name order.
        $order = " ORDER BY " . $DB->sql_substr('email', $DB->sql_position("'@'", 'email'),
            $DB->sql_length('email') ) .  ", $sort";

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        if ($availableusers) {
            // TODO: Group users maybe by number of course completions?
            $result['Found users'] = $availableusers;
        }

      return $result;
    }

}