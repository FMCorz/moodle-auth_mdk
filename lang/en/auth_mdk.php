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
 * Strings for component auth_mdk
 *
 * @package    auth
 * @subpackage mdk
 * @author     Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['adduserpicture'] = 'Add user picture';
$string['adduserpicturehelp'] = 'When logging in for the first time, a picture is added to the user profile. The picture is searched for within the data/pics directory based on name or user name. The image extension must be .jpg.';
$string['anyofthese'] = 'Any of these';
$string['auth_mdkdescription'] = 'This plugin will automatically login the user with any user name and password. If the user does not exist it will be created with a random profile. At each login, if the first letter or the user name is S, T or M the user will automatically be enrolled as a student, teacher or manager in all courses.';
$string['downloadgravatar'] = 'Download gravatars';
$string['downloadgravatarhelp'] = 'Download a random avatar from gravatar.com if the file is not found locally. This is not related to the configuration parameter enablegravatar, in this case gravatar.com is only used to generate an image which will be cached locally.';
$string['gravatardefault'] = 'Type of gravatar to generate';
$string['gravatardefaulthelp'] = 'The type of gravatar to be generated. Check the documentation on gravatar.com to find out more.';
$string['pluginname'] = 'MDK Authentication';
