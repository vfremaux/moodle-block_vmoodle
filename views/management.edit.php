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
 * Form for editing a virtual host.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Loading the library.
require_once(VMOODLE_CLASSES_DIR.'Host_Form.class.php');

// Print title (heading).
echo $OUTPUT->heading(get_string('editvmoodle', 'block_vmoodle'));

echo $OUTPUT->box_start();

// Displays the form with data (and errors).
if (!isset($platform_form)) {
    $datas = (isset($SESSION->vmoodle_mg['dataform']) ? $SESSION->vmoodle_mg['dataform'] : null);
    $platform_form = new Vmoodle_Host_Form('edit', $datas);
}

$platform_form->display();
echo $OUTPUT->box_end();
