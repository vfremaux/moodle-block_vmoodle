<?php

require_once(VMOODLE_CLASSES_DIR.'Command.class.php');

/**
 * Describes a category of commands.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version 2.x
 */
class Vmoodle_Command_Category {
	/** Category's name */
	private $name;
	/** Category's plugin name */
	private $plugin_name;
	/** Category's commands */
	private $commands = array();
	
	/**
	 * Constructor.
	 * @param	$name			string					The category's name.
	 * @param	$plugin_name	string					The category's file.
	 */
	public function __construct($plugin_name) {
	    global $CFG;
	    
		// Checking category's name
		$this->name = vmoodle_get_string('pluginname', 'vmoodleadminset_'.$plugin_name);
		// Checking category's plugin name
		if (!is_string($plugin_name) || empty($plugin_name))
			throw new Vmoodle_Command_Exception('categorywrongpluginname', $name);
		else
			$this->plugin_name = $plugin_name;
	}
	
	/**
	 * Get category's name.
	 * @return					string					The category's name.
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Get category's file.
	 * @return					string					The category's plugin name.
	 */
	public function getPluginName() {
		return $this->plugin_name;
	}
	
	/**
	 * Add a command to the category.
	 * @param	$command		Vmoodle_Command			Command to add to the category.
	 */
	public function addCommand(Vmoodle_Command $command) {
		$this->commands[] = $command;
		$command->setCategory($this);
	}
	
	/**
	 * Get commands.
	 * @param	$index									Index of a command (optional).
	 * @return					mixed					Array of Vmoodle_Command or the requested Vmoodle_Command.
	 */
	public function getCommands($index=null) {
		if (!is_null($index)) {
			if (!array_key_exists($index, $this->commands))
				throw new Vmoodle_Command_Exception('commandnotexits');
			else
				return $this->commands[$index];
		} else
			return $this->commands;
	}
	
	/**
	 * Get the index of a command.
	 * @param	$command		Vmoodle_Command			Vmoodle_Command.
	 * @return					mixed					Index of the command if is contained by the catogory or false otherwise.
	 */
	public function getCommandIndex(Vmoodle_Command $command) {
		$nbr_commands = count($this->commands);
	    for($index=0 ; $index<$nbr_commands; $index++) {
	        if ($command->equals($this->commands[$index]))
	        	return $index;
	    }
	    return null;
	}
	
	/**
	 * Get ammount of commands
	 * @return					int						The ammont of commands.
	 */
	public function count() {
		return count($this->commands);
	}
}