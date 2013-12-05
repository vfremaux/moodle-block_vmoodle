<?php


/**
 * Find and check all submodules and load them up or upgrade them if necessary
 *
 * @global object
 * @global object
 */
function vmoodle_upgrade_subplugins_modules($startcallback, $endcallback, $verbose = true) {
    global $CFG, $DB;

	include $CFG->dirroot.'/blocks/vmoodle/db/subplugins.php';

    foreach($subplugins as $type => $subpluginpath){

    	$plugindirs = glob($CFG->dirroot.'/'.$subpluginpath.'/*');
    	
	    foreach ($plugindirs as $dir) {
	    	
			$plug = basename($dir);
			$fullplug = $dir;	    	
			
	        if ($plug === 'CVS') {   // Someone has unzipped the template, ignore it
	            continue;
	        }
	
	        if ($plug === 'NEWMODULE') {   // Someone has unzipped the template, ignore it
	            continue;
	        }
	
	        // Reset time so that it works when installing a large number of plugins
	        set_time_limit(600);
	        $component = clean_param($type.'_'.$plug, PARAM_COMPONENT); // standardised plugin name
	
	        // check plugin dir is valid name
	        if (empty($component)) {
	            throw new plugin_defective_exception($type.'_'.$plug, 'Invalid plugin directory name.');
	        }
	
	        if (!is_readable($fullplug.'/version.php')) {
	            continue;
	        }
	        	
	        $plugin = new stdClass();
	        require($fullplug.'/version.php');  // defines $plugin with version etc

	        // if plugin tells us it's full name we may check the location
	        if (isset($plugin->component)) {
	            if ($plugin->component !== $component) {
	                throw new plugin_defective_exception($component, 'Plugin installed in wrong folder.');
	            }
	        }
	
	        if (empty($plugin->version)) {
	            throw new plugin_defective_exception($component, 'Missing version value in version.php');
	        }
	
	        $plugin->name     = $plug;
	        $plugin->fullname = $component;	
	
	        if (!empty($plugin->requires)) {
	            if ($plugin->requires > $CFG->version) {
	                throw new upgrade_requires_exception($component, $plugin->version, $CFG->version, $plugin->requires);
	            } else if ($plugin->requires < 2010000000) {
	                throw new plugin_defective_exception($component, 'Plugin is not compatible with Moodle 2.x or later.');
	            }
	        }
	
	        // try to recover from interrupted install.php if needed
	        if (file_exists($fullplug.'/db/install.php')) {
	            if (get_config($plugin->fullname, 'installrunning')) {
	                require_once($fullplug.'/db/install.php');
	                $recover_install_function = 'xmldb_'.$plugin->fullname.'_install_recovery';
	                if (function_exists($recover_install_function)) {
	                    $startcallback($component, true, $verbose);
	                    $recover_install_function();
	                    unset_config('installrunning', $plugin->fullname);
	                    update_capabilities($component);
	                    log_update_descriptions($component);
	                    external_update_descriptions($component);
	                    events_update_definition($component);
	                    message_update_providers($component);
	                    if ($type === 'message') {
	                        message_update_processors($plug);
	                    }
	                    vmoodle_upgrade_plugin_mnet_functions($component, $fullplug);
	                    
						// fix wrongly twicked paths
	                    if ($rpc_shifted_defines = $DB->get_records_select('mnet_rpc', " xmlrpcpath LIKE 'vmoodleadminset' ", array())){
	                    	foreach($rpc_shifted_defines as $rpc){
	                    		$rpc->xmlrpcpath = str_replace('vmoocleadminset', 'blocks/vmoodle/plugins');
	                    		$DB->update_record('mnet_rpc', $rpc);
	                    	}
	                    }
	                    
	                    $endcallback($component, true, $verbose);
	                }
	            }
	        }

	        $installedversion = get_config($plugin->fullname, 'version');
	        if (empty($installedversion)) { // new installation
	            $startcallback($component, true, $verbose);
	
	        /// Install tables if defined
	            if (file_exists($fullplug.'/db/install.xml')) {
	                $DB->get_manager()->install_from_xmldb_file($fullplug.'/db/install.xml');
	            }
	
	        /// store version
	            upgrade_plugin_savepoint(true, $plugin->version, $type, $plug, false);
	
	        /// execute post install file
	            if (file_exists($fullplug.'/db/install.php')) {
	                require_once($fullplug.'/db/install.php');
	                set_config('installrunning', 1, $plugin->fullname);
	                $post_install_function = 'xmldb_'.$plugin->fullname.'_install';
	                $post_install_function();
	                unset_config('installrunning', $plugin->fullname);
	            }
	
	        /// Install various components
	            update_capabilities($component);
	            log_update_descriptions($component);
	            external_update_descriptions($component);
	            events_update_definition($component);
	            message_update_providers($component);
	            if ($type === 'message') {
	                message_update_processors($plug);
	            }
	            vmoodle_upgrade_plugin_mnet_functions($component, $fullplug);

				// fix wrongly twicked paths
                if ($rpc_shifted_defines = $DB->get_records_select('mnet_rpc', " xmlrpcpath LIKE 'vmoodleadminset' ", array())){
                	foreach($rpc_shifted_defines as $rpc){
                		$rpc->xmlrpcpath = str_replace('vmoocleadminset', 'blocks/vmoodle/plugins');
                		$DB->update_record('mnet_rpc', $rpc);
                	}
                }
	
	            purge_all_caches();
	            $endcallback($component, true, $verbose);
	
	        } else if ($installedversion < $plugin->version) { // upgrade
	        /// Run the upgrade function for the plugin.
	            $startcallback($component, false, $verbose);
	
	            if (is_readable($fullplug.'/db/upgrade.php')) {
	                require_once($fullplug.'/db/upgrade.php');  // defines upgrading function
	
	                $newupgrade_function = 'xmldb_'.$plugin->fullname.'_upgrade';
	                $result = $newupgrade_function($installedversion);
	            } else {
	                $result = true;
	            }
	
	            $installedversion = get_config($plugin->fullname, 'version');
	            if ($installedversion < $plugin->version) {
	                // store version if not already there
	                upgrade_plugin_savepoint($result, $plugin->version, $type, $plug, false);
	            }
	
	        /// Upgrade various components
	            update_capabilities($component);
	            log_update_descriptions($component);
	            external_update_descriptions($component);
	            events_update_definition($component);
	            message_update_providers($component);
	            if ($type === 'message') {
	                message_update_processors($plug);
	            }
	            vmoodle_upgrade_plugin_mnet_functions($component, $fullplug);
	
	            purge_all_caches();
	            $endcallback($component, false, $verbose);
	
	        } else if ($installedversion > $plugin->version) {
	            throw new downgrade_exception($component, $installedversion, $plugin->version);
	        }
	    }
	}
}

function vmoodle_uninstall_plugins(){
	global $CFG;
	
	include $CFG->dirroot.'/blocks/vmoodle/db/subplugins.php';

    foreach($subplugins as $type => $subpluginpath){

    	$plugindirs = glob($CFG->dirroot.'/'.$subpluginpath.'/*');
    	
	    foreach ($plugindirs as $dir) {
	    	
			$plug = basename($dir);
			$fullplug = $dir;

	        if ($plug === 'CVS') {   // Someone has unzipped the template, ignore it
	            continue;
	        }
	
	        if ($plug === 'NEWMODULE') {   // Someone has unzipped the template, ignore it
	            continue;
	        }
			
			vmoodle_uninstall_plugin($type, $plug, $fullplug);
		}
	}
}

/**
 * Automatically clean-up all plugin data and remove the plugin DB tables
 *
 * @param string $type The subplugin type, eg. 'vmoodleadminset' etc.
 * @param string $name The subplugin name, eg. 'example', 'generic', etc.
 * @uses global $OUTPUT to produce notices and other messages
 * @return void
 */
function vmoodle_uninstall_plugin($type, $name, $plugindirectory) {
    global $CFG, $DB, $OUTPUT;

    // This may take a long time.
    @set_time_limit(0);

    $component = $type . '_' . $name;  // eg. 'qtype_multichoice' or 'workshopgrading_accumulative' or 'mod_forum'

    $pluginname = $component;
    if (get_string_manager()->string_exists('pluginname', $component)) {
        $strpluginname = get_string('pluginname', $component);
    } else {
        $strpluginname = $component;
    }

    echo $OUTPUT->heading($pluginname);

    $uninstalllib = $plugindirectory . '/db/uninstall.php';
    if (file_exists($uninstalllib)) {
        require_once($uninstalllib);
        $uninstallfunction = 'xmldb_' . $pluginname . '_uninstall';    // eg. 'xmldb_workshop_uninstall()'
        if (function_exists($uninstallfunction)) {
            if (!$uninstallfunction()) {
                echo $OUTPUT->notification('Encountered a problem running uninstall function for '. $pluginname);
            }
        }
    }

    // perform clean-up task common for all the plugin/subplugin types

    //delete the web service functions and pre-built services
    require_once($CFG->dirroot.'/lib/externallib.php');
    external_delete_descriptions($component);

    // delete calendar events
    $DB->delete_records('event', array('modulename' => $pluginname));

    // delete all the logs
    $DB->delete_records('log', array('module' => $pluginname));

    // delete log_display information
    $DB->delete_records('log_display', array('component' => $component));

    // delete the module configuration records
    unset_all_config_for_plugin($pluginname);

    // delete message provider
    message_provider_uninstall($component);

    // delete message processor
    if ($type === 'message') {
        message_processor_uninstall($name);
    }

    // delete the plugin tables
    $xmldbfilepath = $plugindirectory . '/db/install.xml';
    drop_plugin_tables($component, $xmldbfilepath, false);

    // delete the capabilities that were defined by this module
    capabilities_cleanup($component);

    // remove event handlers and dequeue pending events
    events_uninstall($component);

    echo $OUTPUT->notification(get_string('success'), 'notifysuccess');
}

/**
 * upgrades the mnet rpc definitions for the given component.
 * this method doesn't return status, an exception will be thrown in the case of an error
 *
 * @param string $component the plugin to upgrade, eg auth_mnet
 */
function vmoodle_upgrade_plugin_mnet_functions($component, $path) {
    global $DB, $CFG;

    list($type, $plugin) = explode('_', $component);
    
    $publishes = array();
    $subscribes = array();
    if (file_exists($path . '/db/mnet.php')) {
        require_once($path . '/db/mnet.php'); // $publishes comes from this file
    }
    if (empty($publishes)) {
        $publishes = array(); // still need this to be able to disable stuff later
    }
    if (empty($subscribes)) {
        $subscribes = array(); // still need this to be able to disable stuff later
    }

    static $servicecache = array();

    // rekey an array based on the rpc method for easy lookups later
    $publishmethodservices = array();
    $subscribemethodservices = array();
    foreach($publishes as $servicename => $service) {
        if (is_array($service['methods'])) {
            foreach($service['methods'] as $methodname) {
                $service['servicename'] = $servicename;
                $publishmethodservices[$methodname][] = $service;
            }
        }
    }

    // Disable functions that don't exist (any more) in the source
    // Should these be deleted? What about their permissions records?
    foreach ($DB->get_records('mnet_rpc', array('pluginname'=>$plugin, 'plugintype'=>$type), 'functionname ASC ') as $rpc) {
        if (!array_key_exists($rpc->functionname, $publishmethodservices) && $rpc->enabled) {
            $DB->set_field('mnet_rpc', 'enabled', 0, array('id' => $rpc->id));
        } else if (array_key_exists($rpc->functionname, $publishmethodservices) && !$rpc->enabled) {
            $DB->set_field('mnet_rpc', 'enabled', 1, array('id' => $rpc->id));
        }
    }

    // reflect all the services we're publishing and save them
    require_once($CFG->dirroot . '/lib/zend/Zend/Server/Reflection.php');
    static $cachedclasses = array(); // to store reflection information in
    foreach ($publishes as $service => $data) {
        $f = $data['filename'];
        $c = $data['classname'];
        foreach ($data['methods'] as $method) {
            $dataobject = new stdClass();
            $dataobject->plugintype  = $type;
            $dataobject->pluginname  = $plugin;
            $dataobject->enabled     = 1;
            $dataobject->classname   = $c;
            $dataobject->filename    = $f;

            if (is_string($method)) {
                $dataobject->functionname = $method;

            } else if (is_array($method)) { // wants to override file or class
                $dataobject->functionname = $method['method'];
                $dataobject->classname     = $method['classname'];
                $dataobject->filename      = $method['filename'];
            }

            $dataobject->xmlrpcpath = $type.'/'.$plugin.'/'.$dataobject->filename.'/'.$method;
            $dataobject->static = false;

            require_once($path . '/' . $dataobject->filename);
            $functionreflect = null; // slightly different ways to get this depending on whether it's a class method or a function
            if (!empty($dataobject->classname)) {
                if (!class_exists($dataobject->classname)) {
                    throw new moodle_exception('installnosuchmethod', 'mnet', '', (object)array('method' => $dataobject->functionname, 'class' => $dataobject->classname));
                }
                $key = $dataobject->filename . '|' . $dataobject->classname;
                if (!array_key_exists($key, $cachedclasses)) { // look to see if we've already got a reflection object
                    try {
                        $cachedclasses[$key] = Zend_Server_Reflection::reflectClass($dataobject->classname);
                    } catch (Zend_Server_Reflection_Exception $e) { // catch these and rethrow them to something more helpful
                        throw new moodle_exception('installreflectionclasserror', 'mnet', '', (object)array('method' => $dataobject->functionname, 'class' => $dataobject->classname, 'error' => $e->getMessage()));
                    }
                }
                $r =& $cachedclasses[$key];
                if (!$r->hasMethod($dataobject->functionname)) {
                    throw new moodle_exception('installnosuchmethod', 'mnet', '', (object)array('method' => $dataobject->functionname, 'class' => $dataobject->classname));
                }
                // stupid workaround for zend not having a getMethod($name) function
                $ms = $r->getMethods();
                foreach ($ms as $m) {
                    if ($m->getName() == $dataobject->functionname) {
                        $functionreflect = $m;
                        break;
                    }
                }
                $dataobject->static = (int)$functionreflect->isStatic();
            } else {
                if (!function_exists($dataobject->functionname)) {
                    throw new moodle_exception('installnosuchfunction', 'mnet', '', (object)array('method' => $dataobject->functionname, 'file' => $dataobject->filename));
                }
                try {
                    $functionreflect = Zend_Server_Reflection::reflectFunction($dataobject->functionname);
                } catch (Zend_Server_Reflection_Exception $e) { // catch these and rethrow them to something more helpful
                    throw new moodle_exception('installreflectionfunctionerror', 'mnet', '', (object)array('method' => $dataobject->functionname, '' => $dataobject->filename, 'error' => $e->getMessage()));
                }
            }
            $dataobject->profile =  serialize(admin_mnet_method_profile($functionreflect));
            $dataobject->help = $functionreflect->getDescription();

            if ($record_exists = $DB->get_record('mnet_rpc', array('xmlrpcpath'=>$dataobject->xmlrpcpath))) {
                $dataobject->id      = $record_exists->id;
                $dataobject->enabled = $record_exists->enabled;
                $DB->update_record('mnet_rpc', $dataobject);
            } else {
                $dataobject->id = $DB->insert_record('mnet_rpc', $dataobject, true);
            }

            // TODO this API versioning must be reworked, here the recently processed method
            // sets the service API which may not be correct
            foreach ($publishmethodservices[$dataobject->functionname] as $service) {
                if ($serviceobj = $DB->get_record('mnet_service', array('name'=>$service['servicename']))) {
                    $serviceobj->apiversion = $service['apiversion'];
                    $DB->update_record('mnet_service', $serviceobj);
                } else {
                    $serviceobj = new stdClass();
                    $serviceobj->name        = $service['servicename'];
                    $serviceobj->description = empty($service['description']) ? '' : $service['description'];
                    $serviceobj->apiversion  = $service['apiversion'];
                    $serviceobj->offer       = 1;
                    $serviceobj->id          = $DB->insert_record('mnet_service', $serviceobj);
                }
                $servicecache[$service['servicename']] = $serviceobj;
                if (!$DB->record_exists('mnet_service2rpc', array('rpcid'=>$dataobject->id, 'serviceid'=>$serviceobj->id))) {
                    $obj = new stdClass();
                    $obj->rpcid = $dataobject->id;
                    $obj->serviceid = $serviceobj->id;
                    $DB->insert_record('mnet_service2rpc', $obj, true);
                }
            }
        }
    }
    // finished with methods we publish, now do subscribable methods
    foreach($subscribes as $service => $methods) {
        if (!array_key_exists($service, $servicecache)) {
            if (!$serviceobj = $DB->get_record('mnet_service', array('name' =>  $service))) {
                debugging("TODO: skipping unknown service $service - somebody needs to fix MDL-21993");
                continue;
            }
            $servicecache[$service] = $serviceobj;
        } else {
            $serviceobj = $servicecache[$service];
        }
        foreach ($methods as $method => $xmlrpcpath) {
            if (!$rpcid = $DB->get_field('mnet_remote_rpc', 'id', array('xmlrpcpath'=>$xmlrpcpath))) {
                $remoterpc = (object)array(
                    'functionname' => $method,
                    'xmlrpcpath' => $xmlrpcpath,
                    'plugintype' => $type,
                    'pluginname' => $plugin,
                    'enabled'    => 1,
                );
                $rpcid = $remoterpc->id = $DB->insert_record('mnet_remote_rpc', $remoterpc, true);
            }
            if (!$DB->record_exists('mnet_remote_service2rpc', array('rpcid'=>$rpcid, 'serviceid'=>$serviceobj->id))) {
                $obj = new stdClass();
                $obj->rpcid = $rpcid;
                $obj->serviceid = $serviceobj->id;
                $DB->insert_record('mnet_remote_service2rpc', $obj, true);
            }
            $subscribemethodservices[$method][] = $service;
        }
    }

    foreach ($DB->get_records('mnet_remote_rpc', array('pluginname'=>$plugin, 'plugintype'=>$type), 'functionname ASC ') as $rpc) {
        if (!array_key_exists($rpc->functionname, $subscribemethodservices) && $rpc->enabled) {
            $DB->set_field('mnet_remote_rpc', 'enabled', 0, array('id' => $rpc->id));
        } else if (array_key_exists($rpc->functionname, $subscribemethodservices) && !$rpc->enabled) {
            $DB->set_field('mnet_remote_rpc', 'enabled', 1, array('id' => $rpc->id));
        }
    }

    return true;
}

