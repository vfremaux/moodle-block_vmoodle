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
 * Describes meta-administration plugin's command parameter.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
namespace block_vmoodle\commands;

class Command_Parameter {

    /**
     * Types of parameter allowed
     */
    const parameter_types_allowed = 'boolean|enum|text|ltext|internal';

    /**
     * Parameter's name
     */
    protected $name;

    /**
     * Parameter's type
     */
    protected $type; // boolean | enum | text | ltext | internal

    /**
     * Parameter's description : uses for label or choices of enum parameter
     */
    protected $description;

    /**
     * Parameter's default value (optional)
     */
    protected $default;

    /**
     * Parameter's choices (in case of enum)
     */
    protected $choices;

    /**
     * Parameter's attributes when used
     */
    protected $attributes;

    /**
     * Parameter's value
     */
    protected $value = null;

    /**
     * Constructor.
     * @param string $name Parameter's name.
     * @param string $type Parameter's type.
     * @param mixed $description Parameter's description.
     * @param string $default Parameter's defaut value (optional).
     * @param array $choices Parameter's choices (in case of enum).
     * @param array $attributes Parameter's choices (in case of enum).
     */
    public function __construct($name, $type, $description, $default = null, $choices = null, $attributes = null) {
        // Checking parameter's name.
        if (empty($name)) {
            throw new Command_Exception('parameteremptyname');
        } else {
            $this->name = $name;
        }

        // Checking parameter's type.
        if (!in_array($type, explode('|', self::parameter_types_allowed))) {
            throw new Command_Exception('parameterforbiddentype', $this->name);
        } else {
            $this->type = $type;
        }

        // Checking parameter's description.
        if ($this->type != 'internal' && empty($description)) {
            throw new Command_Exception('parameteremptydescription', $this->name);
        } else {
            $this->description = $description;
        }

        // Checking parameter's values.
        if ($this->type == 'enum' && !is_array($choices)) {
            throw new Command_Exception('parameterallowedvaluesnotgiven', $this->name);
        } else {
            $this->choices = $choices;
        }

        // Checking parameter's values.
        if (!empty($attributes)) {
            if ($this->type == 'text' && !is_array($attributes)) {
                throw new Command_Exception('parameterallowedvaluesnotgiven', $this->name);
            } else {
                $this->attributes = $attributes;
            }
        }

        // Checking parameter's default value.
        if (!is_null($default) && $this->type == 'enum' && (!is_string($default) || !array_key_exists($default, $this->choices))) {
            throw new Command_Exception('parameterwrongdefaultvalue', $this->name);
        } else {
            $this->default = $default;
        }
    }

    /**
     * Get parameter's name.
     * @return string Parameter's name.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get parameter's type.
     * @return string Parameter's type.
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Get parameter's description.
     * @return mixed Parameter's description.
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Get parameter's attributes.
     * @return mixed Parameter's description.
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Get parameter's default value.
     * @return mixed Parameter's default.
     */
    public function getDefault() {
        return $this->default;
    }

    /**
     * Get parameter's choices (in case of enum).
     * @return array Parameter's choices.
     */
    public function getChoices() {
        return $this->choices;
    }

    /**
     * Get parameter's value.
     * @return mixed Parameter's value.
     * @throws Command_Exception
     */
    public function getValue() {
        if (is_null($this->value))
            throw new Command_Exception('paramtervaluenotdefined', $this->name);
        return $this->value;
    }

    /**
     * Set parameter's value.
     * @param mixed $value Parameter's value.
     */
    public function setValue($value) {
        $this->value = $value;
    }
}