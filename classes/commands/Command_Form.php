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

namespace block_vmoodle\commands;

require_once($CFG->libdir.'/formslib.php');


/**
 * Defines forms to set Command.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Command_Form extends \moodleform {

    /**
     * Form modes
     */
    const MODE_COMMAND_CHOICE = 1;
    const MODE_RETRIEVE_PLATFORM = 2;
    const MODE_DISPLAY_COMMAND = 3;

    /**
     * Command linked to the form 
     */
    public $command;

    /**
     * Form mode
     */
    public $mode;

    /**
     * Constructor.
     * @param Command $command The Command to link to the form.
     * @param int $mode The form mode.
     */
    public function __construct(Command $command, $mode) {
        // Checking command.
        if (is_null($command)) {
            throw new Command_Exception('commandformnotlinked');
        }

        // Linking the command and her category.
        $this->command = $command;

        // Setting configuration.
        $this->mode = $mode;

        // Setting form action.
        switch($mode) {
            case self::MODE_COMMAND_CHOICE:
                $url = 'view.php?view=sadmin&what=validateassistedcommand';
                break;
            case self::MODE_RETRIEVE_PLATFORM:
                $url = 'view.php?view=sadmin&what=gettargetbyvalue';
                break;
            case self::MODE_DISPLAY_COMMAND:
                $url = 'view.php?view=targetchoice';
                break;
            default:
                throw new Command_Exception('badformmode');
                break;
        }
        // Calling parent's constructor.
        parent::__construct($url);
    }
    
    /**
     * Describes form depending on command.
     * @throws Command_Exception.
     */
    function definition() {
        global $CFG;

        // Setting variables.
        $mform =& $this->_form;
        $command = $this->command;
        $parameters = $command->getParameters();

        // Adding fieldset.
        $mform->addElement('header', null, $command->getName());

        // Adding hidden fields.
        if ($this->mode == self::MODE_COMMAND_CHOICE) {
            $mform->addElement('hidden', 'category_name', $command->getCategory()->getName());
            $mform->setType('category_name', PARAM_TEXT);
            $mform->addElement('hidden', 'category_plugin_name', $command->getCategory()->getPluginName());
            $mform->setType('category_plugin_name', PARAM_TEXT);
            $mform->addElement('hidden', 'command_index', $command->getIndex());
            $mform->setType('command_index', PARAM_TEXT);
        }

        // Adding command's description.
        $mform->addElement('static', 'description', get_string('commanddescription', 'block_vmoodle'), $command->getDescription());

        // Adding elements depending on command's parameter.
        if (!is_null($parameters)) {
            foreach ($parameters as $parameter) {
                switch ($parameter->getType()) {
                    case 'boolean': {
                        $mform->addElement('checkbox', $parameter->getName(), $parameter->getDescription());
                    }
                    break;
                    case 'enum': {
                        $mform->addElement('select', $parameter->getName(), $parameter->getDescription(), $parameter->getChoices());
                    }
                    break;
                    case 'text': {
                        $mform->addElement('text', $parameter->getName(), $parameter->getDescription());
                        $mform->setType($parameter->getName(), PARAM_TEXT);
                        if ($this->mode != self::MODE_DISPLAY_COMMAND) {
                            $mform->addRule($parameter->getName(), null, 'required', null, 'client');
                        }
                    }
                    break;
                    case 'ltext': {
                        $mform->addElement('textarea', $parameter->getName(), $parameter->getDescription(), 'wrap="virtual" rows="20" cols="50"');
                        $mform->setType($parameter->getName(), PARAM_TEXT);
                        if ($this->mode != self::MODE_DISPLAY_COMMAND) {
                            $mform->addRule($parameter->getName(), null, 'required', null, 'client');
                        }
                    }
                    break;
                    case 'internal': {
                        continue 2;
                    }
                }
                // Defining value.
                if ($this->mode == self::MODE_DISPLAY_COMMAND) {
                    $mform->setDefault($parameter->getName(), $parameter->getValue());
                    $mform->freeze($parameter->getName());
                } else if (!is_null($parameter->getDefault())) {
                    $mform->setDefault($parameter->getName(), $parameter->getDefault());
                }
            }
        }

        // Adding submit button.
        switch($this->mode) {
            case self::MODE_COMMAND_CHOICE:
                $mform->addElement('submit', 'submitbutton', get_string('nextstep', 'block_vmoodle'));
                break;
            case self::MODE_RETRIEVE_PLATFORM:
                $mform->addElement('submit', 'submitbutton', get_string('retrieveplatforms', 'block_vmoodle'));
                break;
        }
    }
}