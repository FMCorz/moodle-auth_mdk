# MDK Authentication

This is an authentication plugin for Moodle. It is designed to help developers by creating users on the fly and enrolling them into courses. It is not to be used in a production environment!

## Usage

Login as a user, using any user name or password. 

At each login, depending on the first letter of its user name, the user will be enrolled in all courses as:

- Student (letter S)
- Teacher (letter T)
- Manager (letter M)

## Requirements

Moodle 2.x

## Install

- Clone this repository in the directory auth/mdk.
- Go to your admin notifications page to install the plugin.
- Navigate to Settings > Site administration > Plugins > Authentication to enable it.
- Visit the settings page.

## License

Licensed under the GNU GPL Licence http://www.gnu.org/copyleft/gpl.html
