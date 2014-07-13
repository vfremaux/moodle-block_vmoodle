<?php

namespace block_vmoodle\commands;

/**
 * Describes meta-administration plugin's command parameter.
 * This kind of parameters retrieve his value from a system function (not from an user form).
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Command_Parameter_Internal extends Command_Parameter {
    /** The parameter's function */
    private $fct;
    /** The parameters of function's parameters */
    private $parameters;
    
    /**
     * Constructor.
     * @param    $name            string        Parameter's name.
     * @param    $function        string        Parameter's function.
     * @param    $parameters        mixed        Parameters of parameter's function.
     */
    public function __construct($name, $function, $parameters=null) {
        // Calling parent constructor
        parent::__construct($name, 'internal', null, null);
            
        // Checking parameter's function
        if (!function_exists($function)) {
            throw new Command_Exception('parameterinternalfunctionnotexists', (object) array('function_name' => $function, 'parameter_name' => $this->name));
        } else
            $this->fct = $function;
        
        // Setting parameters
        if (!(is_array($parameters) || is_null($parameters)))
            $parameters = array($parameters);
        $this->parameters = $parameters;
    }

    /**
     * Retrieve the parameter's value.
     * @param    $datas            mixed                        Values of Command's parameters (optional).
     * @throws                    Command_Exception
     */
    public function retrieveValue($datas=null) {
        global $vmcommands_constants;
        
        // Looking for parameters to replace
        if (!is_null($this->parameters)) {
            foreach ($this->parameters as $parameter) {
                preg_match_all(Command::placeholder, $parameter, $vars);
                // Check if parameters and constants are given
                foreach ($vars[2] as $key => $var) {
                    if (empty($vars[1][$key]) && !array_key_exists($var, $vmcommands_constants)) {
                        throw new Command_Exception('parameterinternalconstantnotgiven', (object)array('constant_name' => $var, 'parameter_name' => $this->name));
                    } else if (!empty($vars[1][$key]) && !array_key_exists($var, $datas))
                        throw new Command_Exception('parameterinternalparameternotgiven', (object)array('parameter_need' => $var, 'parameter_name' => $this->name));
                }
            }
            // Replace placeholders by theirs values
            $this->datas = $datas;
            $this->parameters = preg_replace_callback(Command::placeholder, array($this, '_replaceParametersValues'), $this->parameters);
            unset($this->datas);
        }
        
        // Call parameter's function with parameters
        try {
            $this->value = call_user_func_array($this->fct, $this->parameters);
        }
        catch (Exception $exception) {
            $message = $exception->getMessage();
            throw new Command_Exception(
                        'parameterinternalfunctionfailed',
                        (object) array(
                                'function_name' => $this->fct,
                                'message' => empty($message) ?
                                                get_string('withoutmessage', 'block_vmoodle') :
                                                get_string('withmessage', 'block_vmoodle', $message)                                            
                        )
            );
        }
    }
    
    /**
     * Bind the replace_parameters_values function to create a callback.
      * @param    $matches            array                The placeholders found.
      * @return                        string|array        The parameters' values.
     */
    private function _replaceParametersValues($matches) {
        return replace_parameters_values($matches, $this->datas);
    }
}