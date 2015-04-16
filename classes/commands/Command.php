<?php

/**
 * Describes meta-administration plugin's command.
 * It should be extended for command plugin.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

namespace block_vmoodle\commands;

abstract class Command {

    /**
     * Define placeholder 
     */
    const placeholder = '#\[\[(\??)(\w+)(?::(\w+))?\]\]#';
    
    /**
     * Command's name
     */
    protected $name;

    /**
     * Command's description
     */
    protected $description;
    
    /**
     * Command's parameters (optional)
     */
    protected $parameters;

    /**
     * Command's results
     */
    protected $results = array();

    /**
     * Retrieve platforms command 
     */
    protected $rpcommand = null;

    /**
     * Command's category
     */
    private $category = null;

    /**
     * Constructor.
     * Build parameters array whatever is received in parameters input.
     * @param    $name                string                    Command's name.
     * @param    $description        string                    Command's description.
     * @param    $parameters            mixed                    Command's parameters (optional / could be null, Command_Parameter object or Command_Parameter array).
     * @throws                        Command_Exception
     */
    protected function __construct($name, $description, $parameters=null) {
        // Checking command's name
        if (empty($name))
            throw new Command_Exception('commandemptyname');
        else
            $this->name = $name;
            
        // Checking command's description
        if (empty($description))
            throw new Command_Exception('commandemptydescription', $this->name);
        else
            $this->description = $description;
        
        // Checking parameters' format
        if (is_null($parameters)) {
            $this->parameters = array();
        } else {
            if ($parameters instanceof Command_Parameter) {
                $parameters = array($parameters);
            }

            $i_parameters = array();
            if (is_array($parameters)) {
                foreach ($parameters as $parameter) {
                    if ($parameter instanceof Command_Parameter) {
                        $i_parameters[$parameter->getName()] = $parameter;
                    } else {
                        throw new Command_Exception('commandnotaparameter', $this->name);
                    }
                }
            } else {
                throw new Command_Exception('commandwrongparametertype', $this->name);
            }

            $this->parameters = $i_parameters;
        }
    }
    
    /**
     * Populate parameters with their values.
     * @param object $data The data from Command_From.
     * @throws Command_Exception
     */
    public function populate($data) {
        $parameters = $this->getParameters();
        // Setting parameters' values
        foreach ($parameters as $parameter) {
            if (!($parameter instanceof Command_Parameter_Internal)) {
                if ($parameter->getType() == 'boolean' && !property_exists($data, $parameter->getName()))
                    $parameter->setValue('0');
                else
                    $parameter->setValue($data->{$parameter->getName()});
            }
        }
         
         // Retrieving internal parameters' value
        foreach ($parameters as $parameter) {
            if ($parameter instanceof Command_Parameter_Internal)
                $parameter->retrieveValue($parameters);
        }
    }
    
    /**
     * Execute the command.
     * @param mixed $hosts The host where run the command (may be wwwroot or an array).
     */
    public abstract function run($hosts);
    
    /**
     * Return if the command were runned.
     * @return boolean TRUE if the command were runned, FALSE otherwise.
     */
    public function isRunned() {
        return !empty($this->results);
    }
    
    /**
     * Get the result of command execution for one host.
     * @param string $host The host to retrieve result (optional, if null, returns general result).
     * @param string $key The information to retrieve (ie status, error / optional).
     */
    public abstract function getResult($host=null, $key=null);
    
    /**
     * Clear result of command execution.
     */
    public function clearResult() {
        $this->results = array();
    }
    
    /**
     * Get the command's name.
     * @return string Command's name.
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Get the command's description.
     * @return string Command's description.
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * Get the command's parameter from name.
     * @param string $name A command parameter name.
     * @return mixed The command parameter.
     */
    public function getParameter($name) {
        if (!array_key_exists($name, $this->parameters))
            return null;
        else
            return $this->parameters[$name];
    }
     
    /**
     * Get the command's parameters.
     * @return mixed Command's parameters.
     */
    public function getParameters() {
        return $this->parameters;
    }
    
    /**
     * Get the retrieve platforms command.
     * @return Command Retrieve platforms command.
     */
    public function getRPCommand() {
        return $this->rpcommand;
    }
    
    /**
     * Attach a retrieve platform command to the command.
     * @param Command $rpcommand Retrieve platforms command (optional / could be null or Command object).
     * @throws Command_Exception
     */
    public function attachRPCommand($rpcommand) {
        // Checking retrieve platforms command
        if (!(is_null($rpcommand) || $rpcommand instanceof Command))
            throw new Command_Exception('commandwrongrpcommand', $this->name);
        else
            $this->rpcommand = $rpcommand;
    }
    
    /**
     * Get the command's category.
     * @return Command_Category Command's category.
     */
    public function getCategory() {
        return $this->category;
    }
    
    /**
     * Define the command's category.
     * @param Command_Category $category Command's category.
     */
    public function setCategory(Command_Category $category) {
        $this->category = $category;
    }
    
    /**
     * Get command's index on this category. 
     * @returm mixed The index of the command if is in a category or null otherwise.
     */
    public function getIndex() {
        if (is_null($this->category))
            return null;
        else
            return $this->category->getCommandIndex($this);
    }

    /**
     * Safe comparator.
     * @param Command $command The commande to compare to.
     * @return boolean True if the compared command is the same of command parameter, false otherwise.
     */    
    public function equals($command) {
        return ($command->getName() == $this->name);
    }
}
