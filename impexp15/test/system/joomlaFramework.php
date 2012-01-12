<?php


// Define expected Joomla constants.
define('DS',			DIRECTORY_SEPARATOR);
define('_JEXEC',		1);

if (!defined('JPATH_BASE'))
{
	// JPATH_BASE can be defined in init.php
	// This gets around problems with soft linking the unittest folder into a Joomla tree,
	// or using the unittest framework from a central location.
	define('JPATH_BASE',	dirname(dirname(dirname(__FILE__))));
}

// Include relative constants, JLoader and the jimport and jexit functions.
require_once JPATH_BASE.DS.'includes'.DS.'defines.php';
require_once JPATH_LIBRARIES.DS.'joomla'.DS.'import.php';
require_once JPATH_BASE .DS.'includes'.DS.'framework.php';
require_once JPATH_BASE .DS.'libraries'.DS.'joomla'.DS.'factory.php';
// Initalize frontend framework
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();
