<?php

/**
 * Exception about Command.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

namespace block_vmoodle\commands;

class Command_Exception extends \Exception {

    /**
     * Constructor with localized message.
     * @param    $identifier            string                The key identifier for the localized string.
     * @param    $a                    mixed                An object, string or number that can be used (optional).
     */
    public function __construct($identifier, $a=null) {
        parent::__construct(get_string($identifier, 'block_vmoodle', $a));
    }
}