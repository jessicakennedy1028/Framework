<?php
	# Test server for resources needed to operate
	$loaded_ext = array('bcmath', 'bz2', 'curl', 'date', 'dom', 'ereg', 'exif', 'gd', 'mbstring', 'mcrypt', 'session', 'xml', 'xmlreader', 'xmlrpc', 'xmlwriter', 'zip', 'zlib');
	foreach ($loaded_ext as $isloaded) {
		if (!extension_loaded ($isloaded)) {
			SysError ('critical', 'Your PHP does not have '.$isloaded.' installed.<br />Use: sudo apt install php7.0-'.$isloaded, 'fw1000');
		}
	}

    # Check for system files needed to operate
    if (file_exists('bin/config.php')) {
        require_once ("bin/config.php");
    }
    else {
    	if (file_exists ('install/install.php')) {
    		require_once ("install/install.php");
	    }
	    else {
    		SysError ('critical', 'No config file was found and the installation file does not exists, please reinstall framework', 'fw2000');
	    }
    }

    if (file_exists (SYSDIR.DS.'system.php')) {
		require(SYSDIR.DS.'system.php');
    }
    else {
	    SysError ('critical', 'System file was found, please reinstall framework', 'fw1001');
    }

    # Check the environment
    switch (ENVIRONMENT) {
        case "PRODUCTION":{
	        ini_set('display_errors', 0);
	        ini_set('display_startup_errors', 0);
	        error_reporting(NONE);
            break;
        }
        case "DEBUG":{
	        ini_set('display_errors', 1);
	        ini_set('display_startup_errors', 1);
	        error_reporting(E_ALL);
            break;
        }
        case "DEVELOPMENT":{
	        ini_set('display_errors', 1);
	        ini_set('display_startup_errors', 1);
	        error_reporting(E_ALL);
            break;
        }
        default:{
            break;
        }
    }

    # Check for any new updates
		# Future

    # Test user defined etc folder for any modules, plugins and components that was added
	$dirs = array_filter(glob(ETCDIR.DS.'*'), 'is_dir');
	foreach ($dirs as $dir) {
		switch (substr($dir, strlen(ETCDIR)+1, 3)){
			case 'plg':{
				$plgName = substr($dir, strlen(ETCDIR)+5, strlen($dir));
				(LOADPLUGINS ? loadPlugin ($plgName, $dir) : '');
				break;
			}
			case 'mod':{
				# Future use
				break;
			}
			case 'cmp':{
				# Future use
				break;
			}
		}
	}

	function loadPlugin($plgName, $plgDir){
		$config[] = '';
		if (! file_exists ($plgDir.DS.'plg_'.$plgName.'.php')) {
			$message = 'Loading plugin <span style="color: blue;">' . $plgName . '</span> from the Plugin Directory <span style="color: blue;">' . $plgDir . '</span> ';
			$message .= 'looking for file <span style="color: blue;">' . $plgDir . DS . 'plg_' . $plgName . '.php' . '</span> is ' . (file_exists ($plgDir . DS . 'plg_' . $plgName . '.php') ? '<span style="color: green;">found</span>' : '<span style="color: red;">not found</span>');
			SysError ('warning', $message, '100');
		}
		else {
			if (! file_exists ($plgDir.DS.'plg_'.$plgName.'_config.php')) {
				$message = 'Loading plugin <span style="color: blue;">' . $plgName . '</span> from the Plugin Directory <span style="color: blue;">' . $plgDir . '</span> ';
				$message .= 'looking for file <span style="color: blue;">' . $plgDir . DS . 'plg_' . $plgName . '_config.php' . '</span> is ' . (file_exists ($plgDir . DS . 'plg_' . $plgName . '_config.php') ? '<span style="color: green;">found</span>' : '<span style="color: red;">not found</span>');
				SysError ('critical', $message, '101');
			} else {
				include ($plgDir.DS.'plg_'.$plgName.'_config.php');
			}
			if ($config['LOAD'] == 'TRUE') include ($plgDir.DS.'plg_'.$plgName.'.php');
		}
	}
	function SysError($level, $message, $errno){
		$backtrace = debug_backtrace();
		$file = $backtrace['file'];
		$function = $backtrace['function'];
		$errFrom = '';
		switch (strtolower ($level)){
			case 'warning':{

				if (ENVIRONMENT == 'production') {
					# Log this to an error file only
				}
				else {
					if (ob_get_level() == 0) ob_start();
					echo str_pad ('', 4096);
					echo 'There is a warning from '.$errFrom.' error number ['.$errno.'] - '.$message.'<br />';
					ob_flush ();
					flush ();
					ob_end_flush ();
				}
				break;
			}
			case 'notify':{
				break;
			}
			case 'critical':{
				die('Unable to continue with critical error number ['.$errno.'] - '.$message.'<br />');
				break;
			}
			case 'deprecated':{
				break;
			}
			case 'recoverable':{
				break;
			}
		}
	}
