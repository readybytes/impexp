<?php
$version = new JVersion();
define('INCLUDE_IMPEXP',true);
define('IMPEXP_JOOMLA_17',($version->RELEASE === '1.7'));
define('IMPEXP_JOOMLA_15',($version->RELEASE === '1.5'));
define('IMPEXP_BASE_URL',dirname(__FILE__));
define('IMPEXP_LIMIT',1000);
define('IMPEXP_EXP_LIMIT', 200);
define('IMPEXP_TEMP_FILE_PATH',dirname(__FILE__) .DS. 'userid.csv');
define('IMPEXP_MAX_EXEC_TIME' ,30);
define('IMPEXP_BIAS_TIME' , 0.50);
define('IMPEXP_MEM_LIMIT' , 32);
define('IMPEXP_BIAS_MEMORY',0.60);
define('IMPEXP_PERCENTAGE',0.25);