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
 * Script for moving the string files from a .json file to a Moodle .php string file.
 */

// Check we are in CLI.
if (isset($_SERVER['REMOTE_ADDR'])) {
    exit(1);
}

define("MOODLE_INTERNAL", 1);

define("JSON_FILE_PATH",   "/Users/juanleyvadelgado/Documents/MoodleMobile/moodlemobile2/www/build/lang/en.json");
define("STRING_FILES_PATH", "/Users/juanleyvadelgado/Documents/MoodleMobile/moodle-local_moodlemobileapp/lang/en/local_moodlemobileapp.php");
define("MOODLE_STRING_FILES_PATH", "/Users/juanleyvadelgado/www/m/stable_master");

$jsonstrings = (array) json_decode(file_get_contents(JSON_FILE_PATH), true);

include(STRING_FILES_PATH);

$finalstrings = array_replace($string, $jsonstrings);
// Order the array.
ksort($finalstrings);

$templatefile = "<?php
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
 * Version details.
 *
 * @package    local
 * @subpackage moodlemobileapp
 * @copyright  2014 Juan Leyva <juanleyvadelgado@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

";

foreach ($finalstrings as $key => $value) {
    if (strpos($key, 'mm.core.country') !== false) {
        continue;
    }
    // Ommit modules already translated files.
    if (strpos($key, 'mma.mod_') !== false) {
        list($comp, $mod, $id) = explode(".", $key);
        list($t, $modname) = explode("_", $mod);
        $string = array();
        include(MOODLE_STRING_FILES_PATH . "/mod/" . $modname . "/lang/en/" . $modname . ".php");
        if (isset($string[$id]) and $string[$id] == $value) {
            echo "$modname string with id $key exists \n";
            continue;
        }
    }
    if (strpos($key, 'mm.core.') !== false) {
        list($comp, $mod, $id) = explode(".", $key);
        $string = array();
        include(MOODLE_STRING_FILES_PATH . "/lang/en/moodle.php");
        if (isset($string[$id]) and $string[$id] == $value) {
            echo "string with id $key exists \n";
            continue;
        }
    }

    if (strpos($key, 'mma.messages.') !== false) {
        list($comp, $mod, $id) = explode(".", $key);
        $string = array();
        include(MOODLE_STRING_FILES_PATH . "/lang/en/message.php");
        if (isset($string[$id]) and $string[$id] == $value) {
            echo "string with id $key exists \n";
            continue;
        }
    }

    $value = str_replace("'", "\'", $value);
    $templatefile .= '$string' . "['$key'] = '$value';\n";
}
$templatefile .= "\n";

file_put_contents(STRING_FILES_PATH, $templatefile);

// Exit 0 mean success.
exit(0);