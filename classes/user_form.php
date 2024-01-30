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
 * user_form file for local_hugosoares.
 *
 * @package    local_hugosoares
 * @copyright  2024 Hugo Soares
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hugosoares;

use moodleform;

require_once($CFG->libdir.'/formslib.php');

class user_form extends moodleform {
    protected function definition() {
        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);

        $mform->addElement(
            'submit', 
            'submitbutton', 
            get_string('select'), 
            ['style'=> 'margin-left: auto;'] // Not going to try and do elaborate styling.
        );
    }
}
