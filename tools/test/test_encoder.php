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
 * @package blocks_vmoodle
 * @category
 * @author Valery Fremaux <valery.fremaux@gmail.com>, <valery@edunao.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft Open Technologies, Inc. (http://msopentech.com/)
 */
ini_set('display_errors', true);
define('MOODLE_INTERNAL', true);
require_once('../lib.php');

echo "<h2>Testing numeric sequences protection encoder</h2><br>";
echo "<p>This usefull encode/decoder protects any numeric sequence longer then 2, f.e. postalcodes or
plant identification, so those longer codes may not be affected by version (2 digit) processing</p>";

$strings = array();
$strings[] = 'http://my_moodle_45456345_34.mydomain.com';
$strings[] = 'http://withountnumebers.mydomain.com';
$strings[] = 'http://my_moodle_1111_2222_3333_34.mydomain.com';
$strings[] = 'http://my_moodle34_346456.mydomain.com';
$strings[] = 'http://my_moodle26_45456345_34.mydomain.com';

foreach ($strings as $string) {
    echo "Original String : $string</br/>";

    $encoded = encode_numeric_sequences($string);
    echo "Encoded : $encoded<br/> ";

    $reverted = decode_numeric_sequences($encoded);
    echo "Reverted : $reverted<br/>";
    echo '<br/>';
}