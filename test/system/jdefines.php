<?php
require_once dirname(__FILE__). '/joomlaFramework.php';

// which selenium server to pick
$whichSelRC = getenv('useSelRC'); // can be local / network
if($whichSelRC == FALSE)
{
	echo "\n Environment variable not set picking up localhost as useSelRC";
	$whichSelRC = 'local';
}

require_once dirname(__FILE__). "/selRC_$whichSelRC.php";

define('JOOMLA_LOCATION',	'http://'.JOOMLA_HOST.'/@joomla.folder@/');
define('JOOMLA_FTP_LOCATION', 	JPATH_BASE);

$_SERVER['HTTP_HOST'] = JOOMLA_LOCATION;

define('TIMEOUT_SEC',30000);
define('JOOMLA_ADMIN_USERNAME', 'admin');
define('JOOMLA_ADMIN_PASSWORD',	'ssv445');
define('XIEC_TEST_MODE',	true);
//these files should have been copied by phing during setup of joomla
define('IMPEXP_PKG',		JOOMLA_LOCATION.'/impexp.zip');

// include xiec specific define files
require_once dirname(__FILE__). DS. "xiec.php";