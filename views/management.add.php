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
 * Form for adding a virtual host.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

// Loading the library.
Use \block_vmoodle\Host_Form;

// Print title (heading).
echo $OUTPUT->heading(get_string('newvmoodle', 'block_vmoodle'));

echo $OUTPUT->box_start();

// Displays the form.
if (isset($SESSION->vmoodle_mg['dataform'])) {
    $platform_form = new \block_vmoodle\Host_Form('add', $SESSION->vmoodle_mg['dataform']);
} else {
    $platform_form = new \block_vmoodle\Host_Form('add', null);

    if ($CFG->block_vmoodle_automatedschema) {
        $formdata = new StdClass;
        if ($CFG->block_vmoodle_mnet == 'NEW') {
            $lastsubnetwork = $DB->get_field('block_vmoodle', 'MAX(mnet)', array());
            $formdata->mnet = $lastsubnetwork + 1;
        } else {
            $formdata->mnet = 0 + @$CFG->block_vmoodle_mnet;
        }

        $formdata->services = $CFG->block_vmoodle_services;
        $platform_form->set_data($formdata);
    }
}

$platform_form->display();
echo $OUTPUT->box_end();
