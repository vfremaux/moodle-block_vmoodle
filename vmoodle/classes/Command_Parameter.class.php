<?php

require_once(VMOODLE_CLASSES_DIR.'Command_Exception.class.php');

/**
 * Describes meta-administration plugin's command parameter.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Vmoodle_Command_Parameter {
	/** Types of parameter allowed */
	const parameter_types_allowed = 'boolean|enum|text|ltext|internal';
	
	/** Parameter's name */
	protected $name;
	/** Parameter's type */
	protected $type;
	/** Parameter's description : uses for label or choices of enum parameter */
	protected $description;
	/** Parameter's default value (optional) */
	protected $default;
	/** Parameter's choices (in case of enum) */
	protected $choices;
	/** Parameter's value */
	protected $value = null;

	/**
	 * Constructor.
	 * @param	$name				string					Parameter's name.
	 * @param	$type				string					Parameter's type.
	 * @param	$description		mixed					Parameter's description.
	 * @param	$default			string					Parameter's defaut value (optional).
	 * @param	$choices			array					Parameter's choices (in case of enum).
	 */
	public function __construct($name, $type, $description, $default=null, $choices=null) {
		// Checking parameter's name
		if (empty($name))
			throw new Vmoodle_Command_Exception('parameteremptyname');
		else
			$this->name = $name;
			
		// Checking parameter's type
		if (!in_array($type, explode('|', self::parameter_types_allowed)))
			throw new Vmoodle_Command_Exception('parameterforbiddentype', $this->name);
		else
			$this->type = $type;
			
		// Checking parameter's description
		if ($this->type != 'internal' && empty($description))
			throw new Vmoodle_Command_Exception('parameteremptydescription', $this->name);
		else
			$this->description = $description;
			
		// Checking parameter's values
		if ($this->type == 'enum' && !is_array($choices))
			throw new Vmoodle_Command_Exception('parameterallowedvaluesnotgiven', $this->name);
		else
			$this->choices = $choices;
			
		// Checking parameter's default value
		if (!is_null($default) && $this->type == 'enum' && (!is_string($default) || !array_key_exists($default, $this->choices)))
			throw new Vmoodle_Command_Exception('parameterwrongdefaultvalue', $this->name);
		else
			$this->default = $default;
	}
	
	/**
	 * Get parameter's name.
	 * @return						string					Parameter's name.
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Get parameter's type.
	 * @return						string					Parameter's type.
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Get parameter's description.
	 * @return						mixed					Parameter's description.
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Get parameter's default value.
	 * @return						mixed					Parameter's default.
	 */
	public function getDefault() {
		return $this->default;
	}
	
	/**
	 * Get parameter's choices (in case of enum).
	 * @return						array					Parameter's choices.
	 */
	public function getChoices() {
		return $this->choices;
	}
	
	/**
	 * Get parameter's value.
	 * @return						mixed					Parameter's value.
	 * @throws						Vmoodle_Command_Exception
	 */
	public function getValue() {
		if (is_null($this->value))
			throw new Vmoodle_Command_Exception('paramtervaluenotdefined', $this->name);
		return $this->value;
	}
	
	/**
	 * Set parameter's value.
	 * @param	$value				mixed					Parameter's value.
	 */
	public function setValue($value) {
		$this->value = $value;
	}
}