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
 * Exception about Command_SetConfig.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
namespace vmoodleadminset_generic;

defined('MOODLE_INTERNAL') || die();

use \Exception;

class Command_SetConfig_Exception extends Exception {
    /**
     * Constructor with localized message.
     * @param string $identifier The key identifier for the localized string.
     * @param mixed $a An object, string or number that can be used (optional).
     */
    public function __construct($identifier, $a=null) {
        parent::__construct(vmoodle_get_string($identifier, 'vmoodleadminset_generic', $a));
    }
}