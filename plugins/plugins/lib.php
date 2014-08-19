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
 * This library provides command for synchronize role capabilites.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

global $STANDARD_PLUGIN_TYPES;

$STANDARD_PLUGIN_TYPES = array(
    'mod' => get_string('activitymodules'),
    'format' => get_string('courseformats'),
    'block' => get_string('blocks'),
    'message' => get_string('messageoutputs', 'message'),
    'auth' => get_string('authentication', 'admin'),
    'enrol' => get_string('enrolments', 'enrol'),
    'editor' => get_string('editors', 'editor'),
    'filter' => get_string('managefilters'),
    'portfolio' => get_string('portfolios', 'portfolio'),
    'scormreport' => get_string('repositories', 'repository'),
    'repository' => get_string('repositories', 'repository'),
    'webservice' => get_string('webservices', 'webservice'),
    'qbehaviour' => get_string('questionbehaviours', 'admin'),
    'qtype' => get_string('questiontypes', 'admin'),
    'plagiarism' => get_string('plagiarism', 'plagiarism'),
    'coursereport' => get_string('coursereports'),
    'report' => get_string('reports'),
    'tool' => get_string('tools', 'admin'),
    'cachestore' => get_string('cachestores', 'cache'),
    'local' => get_string('localplugins'),
    'assignsubmission' => get_string('assignsubmission', 'assign'),
    'assignfeedback' => get_string('assignfeedback', 'assign'),
);