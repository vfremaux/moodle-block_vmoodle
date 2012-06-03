<?php
/**
 * Exception about role plugin library.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Vmoodle_Command_Role_Exception extends Exception {
	/**
	 * Constructor with localized message.
	 * @param	$identifier			string				The key identifier for the localized string.
 	 * @param	$a					mixed				An object, string or number that can be used (optional).
	 */
	public function __construct($identifier, $a=null) {
		parent::__construct(get_string_vml($identifier, 'rolelib', $a));
	}
}