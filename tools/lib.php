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

defined('MOODLE_INTERNAL') || die();

/**
 * @package blocks_vmoodle
 * @category
 * @author Valery Fremaux <valery.fremaux@gmail.com>, <valery@edunao.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft Open Technologies, Inc. (http://msopentech.com/)
 */

function remove_moodle_version($string) {
    return preg_replace('/moodle\d{2}/', 'moodle', $string);
}

function change_version($from, $to, $string, $fallbackto = 'from') {

    // Start by protecting any digit sequence that is longer than the moodle version.
    $string = encode_numeric_sequences($string);

    if (strstr($string, $from) !== false) {
        $result = str_replace($from, $to, $string);
        return decode_numeric_sequences($result);
    } else {
        // We need inject the from or to version depending on fallback.
        if ($fallbackto == 'from') {
            $inject = $from;
        } else {
            $inject = $to;
        }

        /**
         * now we have several moodle dedicated strategies :
         *
         * If the string contains "moodle" not followed by numeric, then append after
         */
        if (preg_match('/moodle/', $string) && !preg_match('/moodle[\d+]/', $string)) {
            $result = str_replace('moodle', 'moodle'.$inject, $string);
            return decode_numeric_sequences($result);
        }
    }

    return decode_numeric_sequences($string);
}

function encode_numeric_sequences($string) {
    if (preg_match_all('/[\d][\d][\d]+/', $string, $matches)) {
        foreach ($matches[0] as $pat) {
            $dest = '';
            $len = strlen($pat);
            for ($i = 0; $i < $len; $i++) {
                $dest .= chr(ord('A') + ord($pat[$i]) - ord('0'));
            }
            $string = str_replace($pat, '$$'.$dest.'$$', $string);
        }
    }

    return $string;
}

function decode_numeric_sequences($string) {

    if (preg_match_all('/\$\$[A-J]+\$\$/', $string, $matches)) {
        foreach ($matches[0] as $origin) {
            // Trim start/end markers out.
            $pat = str_replace('$$', '', $origin);
            $len = strlen($pat);
            $dest = '';
            for ($i = 0; $i < $len; $i++) {
                $dest .= chr(ord($pat[$i]) - ord('A') + ord('0'));
            }
            $string = str_replace($origin, $dest, $string);
        }
    }

    return $string;
}