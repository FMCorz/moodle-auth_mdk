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
 * Events for MDK Auth plugin
 *
 * @package    auth
 * @subpackage mdk
 * @copyright  2012 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function called when a user is created
 *
 * @param stdClass $user object
 * @return void
 */
function auth_mdk_user_created_event($user) {
    global $CFG, $DB;

    // Do not proceed with this event for users created via another plugin.
    if ($user->auth != 'mdk') {
        return;
    }

    $path = $CFG->dirroot . '/auth/mdk/data/pics/';
    $config = get_config('auth/mdk');
    $picture = false;

    // Set default values for config (useful when the admin did not set the settings)
    if (!isset($config->adduserpicture)) {
        $config->adduserpicture = '1';
    }
    if (!isset($config->downloadgravatar)) {
        $config->downloadgravatar = '1';
    }
    if (!isset($config->gravatardefault)) {
        $config->gravatardefault = 'wavatar';
    }

    // User isset() and empty() because we want 
    if (empty($config->adduserpicture)) {
        return;
    }

    // Reading local pics folder for matching file name
    $fullname = strtolower(preg_replace('/[^a-z0-9_-]/i', '_', $user->firstname . '_' . $user->lastname)) . '.jpg';
    $usernamefile = $path . $user->username . '.jpg';
    if (is_file($path . $fullname)) {
        $picture = $path . $fullname;
    } else if (is_file($usernamefile)) {
        $picture = $usernamefile;
    }

    // Downloading an image from Gravatar
    if (!$picture && !empty($config->downloadgravatar)) {
        $types = array('identicon', 'monsterid', 'wavatar', 'retro');
        $type = $config->gravatardefault;
        if ($type == 'random') {
            $type = $types[array_rand($type)];
        }
        $params = array(
            'size' => 160,
            'force' => 'y',
            'default' => $type
        );
        $url = new moodle_url('http://www.gravatar.com/avatar/' . md5($user->id . ':' . $user->username), $params);

        // Temporary file name
        if (empty($CFG->tempdir)) {
            $tempdir = $CFG->dataroot . "/temp";
        } else {
            $tempdir = $CFG->tempdir;
        }
        $picture = $tempdir . '/' . 'auth_mdk.jpg';

        require_once($CFG->libdir . '/filelib.php');
        download_file_content($url->out(false), null, null, false, 5, 2, false, $picture);
    }

    // Ensures retro compatibility
    if (class_exists('context_user')) {
        $context = context_user::instance($user->id);
    } else {
        $context = get_context_instance(CONTEXT_USER, $user->id, MUST_EXIST);
    }

    require_once($CFG->libdir . '/gdlib.php');
    $id = process_new_icon($context, 'user', 'icon', 0, $picture);
    $DB->set_field('user', 'picture', $id, array('id' => $user->id));
}
