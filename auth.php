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
 * Moodle Development Kit Authentication plugin
 *
 * @package    auth
 * @subpackage mdk
 * @copyright  2012 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

/**
 * Moodle Development Kit Authentication plugin
 *
 * @package    auth
 * @subpackage mdk
 */
class auth_plugin_mdk extends auth_plugin_base {

    /**
     * Constructor.
     */
    function __construct() {
        $this->authtype = 'mdk';
        $this->config = $this->init_config(get_config('auth/mdk'));
    }

    /**
     * Creates the config entries
     *
     * @return void
     */
    public function init_config($config) {
        if (!isset($config->adduserpicture)) {
            $config->adduserpicture = '1';
        }
        if (!isset($config->downloadgravatar)) {
            $config->downloadgravatar = '1';
        }
        if (!isset($config->gravatardefault)) {
            $config->gravatardefault = 'wavatar';
        }
        foreach ($this->userfields as $field) {
            if (!isset($pluginconfig->{"field_map_$field"})) {
                $config->{"field_map_$field"} = '';
            }
            if (!isset($config->{"field_updatelocal_$field"})) {
                $config->{"field_updatelocal_$field"} = '';
            }
            if (!isset($config->{"field_updateremote_$field"})) {
                $config->{"field_updateremote_$field"} = '';
            }
            if (!isset($config->{"field_lock_$field"})) {
                $config->{"field_lock_$field"} = '';
            }
        }
        return $config;
    }

    /**
     * Returns true if the username and password work or don't exist and false
     * if the user exists and the password is wrong.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
        global $CFG, $DB;
        if ($user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            return validate_internal_user_password($user, $password);
        }
        return true;
    }

    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param object $user User table object
     * @param string $newpassword Plaintext password
     * @return boolean result
     *
     */
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $newpassword);
    }

    /**
     * Do we forbid saved passwords locally?
     *
     * @return boolean
     */
    function prevent_local_passwords() {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return true;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return true;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return true;
    }

    /**
     * Indicates if moodle should automatically update internal user
     * records with data from external sources using the information
     * from get_userinfo() method.
     *
     * @return bool true means automatically copy data from ext to user table
     */
    function is_synchronised_with_external() {
        return true;
    }

    /**
     * Read user information from external database and returns it as array().
     * Function should return all information available. If you are saving
     * this information to moodle user-table you should honour synchronisation flags
     *
     * @param string $username username
     *
     * @return mixed array with no magic quotes or false on error
     */
    function get_userinfo($username) {
        include_once('data/userinfos.php');
        $userinfos = new auth_mdk_userinfos();

        $default = $userinfos->get('default');
        $data = array();

        // Name
        $loop = 0;
        if ($name = $userinfos->get('name')) {
            $name = explode(',', $name, 2);
            if (isset($name[1])) {
                $firstname = trim($name[0]);
                $lastname = trim($name[1]);
            }
        }
        while (!isset($firstname) || $this->user_exists_with_name($firstname, $lastname)) {
            $firstname = $userinfos->get('firstname');
            $lastname = $userinfos->get('lastname');

            // Avoid infinite loop
            if ($loop++ > 10) {
                break;
            }
        }
        $data['firstname'] = $this->parse_value($firstname, $username);
        $data['lastname'] = $this->parse_value($lastname, $username);

        // Any other field
        $missing = array_diff($this->userfields, array_keys($data));
        foreach ($missing as $field) {
            if ($value = $userinfos->get($field)) {
                $data[$field] = $this->parse_value($value, $username);
            }
        }

        return array_merge($default, $data);
    }

    /**
     * Returns the role of a user based on its username
     *
     * @param string $username
     * @return int
     */
    public function get_role($username) {
        $letter = substr($username, 0, 1);
        switch ($letter) {
            case 's':
                $archetype = 'student';
                break;
            case 't':
                $archetype = 'editingteacher';
                break;
            case 'm':
                $archetype = 'manager';
                break;
            default:
                $archetype = 'user';
                break;
        }
        $role = get_archetype_roles($archetype);
        return reset($role);
    }

    /**
     * Sync roles for this user
     *
     * @param $user object user object (without system magic quotes)
     */
    public function sync_roles($user) {
        global $CFG, $DB, $PAGE;
        require_once($CFG->dirroot . '/enrol/manual/lib.php');
        $plugin = new enrol_manual_plugin();

        $courses = $DB->get_records_select('course', 'id > ?', array(1), '', 'id, startdate');
        foreach ($courses as $course) {
            $role = $this->get_role($user->username);
            if (!in_array($role->archetype, array('user', 'guest', 'frontpage'))) {
                $instance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'));
                $plugin->enrol_user($instance, $user->id, $role->id, $course->startdate, 0);
            }
        }
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        include('config.html');
    }

    /**
     * Check if a user exists with that name
     *
     * @param string $firstname
     * @param string $lastname
     * @return bool
     */
    public function user_exists_with_name($firstname, $lastname) {
        global $DB;
        return $DB->record_exists('user', array('deleted' => 0, 'firstname' => $firstname, 'lastname' => $lastname));
    }

    /**
     * Check for patterns and replace them
     *
     * @param string $value the string to work on
     * @param string $username the username
     * @return string modified
     */
    public function parse_value($value, $username) {
        global $CFG;
        if (!empty($CFG->auth_mdk_hostname)) {
            $host = $CFG->auth_mdk_hostname;
        } else {
            $url = parse_url($CFG->wwwroot);
            $host = $url['host'];
        }
        $value = str_replace('{username}', $username, $value);
        $value = str_replace('{hostname}', $host, $value);
        return $value;
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     * @param srdClass $config
     * @return bool always true or exception
     */
    function process_config($config) {

        set_config('adduserpicture', $config->adduserpicture, 'auth/mdk');
        set_config('downloadgravatar', $config->downloadgravatar, 'auth/mdk');
        set_config('gravatardefault', $config->gravatardefault, 'auth/mdk');

        return true;
    }

}
