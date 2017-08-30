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
 * Describes a category of commands.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version 2.x
 */
namespace block_vmoodle\commands;

class Command_Category {

    /**
     * Category's name
     */
    private $name;

    /**
     * Category's plugin name
     */
    private $plugin_name;

    /**
     * Category's commands
     */
    private $commands = array();

    /**
     * Constructor.
     * @param string $name The category's name.
     * @param string $plugin_name The category's file.
     */
    public function __construct($plugin_name) {
        global $CFG;

        // Checking category's name.
        $this->name = vmoodle_get_string('pluginname', 'vmoodleadminset_'.$plugin_name);

        // Checking category's plugin name.
        if (!is_string($plugin_name) || empty($plugin_name)){
            throw new Command_Exception('categorywrongpluginname', $name);
        } else {
            $this->plugin_name = $plugin_name;
        }
    }

    /**
     * Get category's name.
     * @return string The category's name.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get category's file.
     * @return string The category's plugin name.
     */
    public function getPluginName() {
        return $this->plugin_name;
    }

    /**
     * Add a command to the category.
     * @param Command $command Command to add to the category.
     */
    public function addCommand(Command $command) {
        $this->commands[] = $command;
        $command->setCategory($this);
    }

    /**
     * Get commands.
     * @param int $index Index of a command (optional).
     * @return mixed Array of Command or the requested Command.
     */
    public function getCommands($index=null) {
        if (!is_null($index)) {
            if (!array_key_exists($index, $this->commands))
                throw new Command_Exception('commandnotexits');
            else
                return $this->commands[$index];
        } else
            return $this->commands;
    }

    /**
     * Get the index of a command.
     * @param Command $command Command.
     * @return mixed Index of the command if is contained by the catogory or false otherwise.
     */
    public function getCommandIndex(Command $command) {
        $nbr_commands = count($this->commands);
        for($index=0 ; $index<$nbr_commands; $index++) {
            if ($command->equals($this->commands[$index]))
                return $index;
        }
        return null;
    }

    /**
     * Get ammount of commands
     * @return int The ammont of commands.
     */
    public function count() {
        return count($this->commands);
    }
}