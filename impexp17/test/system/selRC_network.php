<?php 

define('SEL_RC_SERVER','10.0.0.14');
define('SEL_RC_PORT',4444);
define('SEL_TIMEOUT',10);
define('SCREENSHOT_PATH','/var/www/selRC');
define('SCREENSHOT_URL','http://'.SEL_RC_SERVER.'/selRC');

//we should calculate our own IP rather then hardcoding
define('JOOMLA_HOST',$_SERVER['SERVER_ADDR']);
