<?php
/**
 * autoloader.php
 * 
 * This file provides class autoloading for Vmoodle classes.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
    function __autoload($classname) {
        global $CFG;
        // Checkink if is a Vmoodle class
        $classname = explode('_', $classname);
        if (!array_shift($classname) == 'Vmoodle')
            return;
        $classname = implode('_', $classname);
        
        // Checking class in common library
        $file = 'classes/'.$classname.'.class.php';
        if (file_exists($file))
            require_once $file;
        else {
            foreach(glob('plugins/*/classes/'.$classname.'.class.php') as $file)
                require_once $file;
        }
    }