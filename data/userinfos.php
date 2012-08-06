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
 * Data for user creation
 *
 * @package    auth
 * @subpackage mdk
 * @copyright  2012 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Contains possible user information
 *
 * @package auth
 * @subpackage mdk
 * @author  Frédéric Massart
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class auth_mdk_userinfos {

    // All the following variables are used to generate user information. They must be arrays.
    // They can all contain the placeholders:
    // - {username}
    // - {hostname} (Generated from $_SERVER or $CFG->auth_mdk_hostname it set)

    protected $default = array(
        array(
            'firstname' => '{username}',
            'lastname' => 'Wayne',
            'email' => '{username}@{hostname}',
            'city' => 'Perth',
            'country' => 'AU',
            'lang' => 'en',
            'description' => 'I am your father!',
            'url' => 'http://moodle.org',
            'idnumber' => '',
            'institution' => 'Moodle HQ',
            'department' => 'Yoshi riders',
            'phone1' => '',
            'phone2' => '',
            'address' => ''
        )
    );

    protected $name = array(
        "Eric,Cartman",
        "Stan,Marsh",
        "Kyle,Broflovski",
        "Kenny,McCormick",
        "Butters,Stotch",
        "Clyde,Donovan",
        "Jimmy,Valmer",
        "Timmy,Burch",
        "Wendy,Testaburger",
        "Bebe,Stevens",
        "Herbert,Garrison",
        "Sheila,Broflovski",
        "Liane,Cartman",
        "Officer,Barbady",
        "Principal,Victoria",
        "Randy,Marsh",
        "Mr,Mackey",

        "Thomas,Anderson",
        "Neo,The One",
        "Morpheus,The Captain",
        "Agent,Smith",

        "Luke,Skywalker",
        "Anakin,Skywalker",
        "Darth,Vader",
        "Boba,Fett",
        "Han,Solo",
        "Obi-Wan,Kenobi",
        "Yoda,Wise He Is",
        "R2,D2",
        "C,3PO",
        "Padmé,Amidala",
        "Chewbacca,The Furry",
        "Leia,The Princess",
    );

    protected $firstname = array();
    protected $lastname = array();
    protected $email = array('{username}@{hostname}');
    protected $country = array('AU');
    protected $city = array('Perth');

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct() {

        // Auto add first and last names
        foreach ($this->name as $fullname) {
            if (strpos($fullname, ',') === false) {
                continue;
            }
            list($firstname, $lastname) = explode(',', $fullname);
            $firstname = trim($firstname);
            if (!in_array($firstname, $this->firstname)) {
                $this->firstname[] = $firstname;
            }
            $lastname = trim($lastname);
            if (!in_array($lastname, $this->lastname)) {
                $this->lastname[] = $lastname;
            }
        }
    }

    /**
     * Getter
     *
     * @param string $var variable name
     * @return string of random characters
     */
    public function get($var) {
        if (isset($this->$var)) {
            $val = $this->$var;
            return $val[array_rand($val)];
        }
        return false;
    }

}

