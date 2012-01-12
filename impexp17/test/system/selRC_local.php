<?php
define('SEL_RC_SERVER','localhost');
define('SEL_RC_PORT',4444);
define('SEL_TIMEOUT',10);

define('SCREENSHOT_PATH','/var/www/selRC');
define('SCREENSHOT_URL','http://'.SEL_RC_SERVER.'/selRC');

define('JOOMLA_HOST','localhost');

jimport('joomla.filesystem.folder');
if(!JFolder::exists(SCREENSHOT_PATH)){
	
	if(!JFolder::create(SCREENSHOT_PATH,0777))
		echo "\nIMP : Please create selRC folder at ".SCREENSHOT_PATH."\n";
}