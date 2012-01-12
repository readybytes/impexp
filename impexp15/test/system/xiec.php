<?php

// include files specific to our components
$comPath = JPATH_ROOT.DS.'components'.DS.'com_xiec';
$comAdminPath = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_xiec';

if(JFolder::exists($comPath))
{
	require_once $comPath.DS.'includes'.DS.'includes.php';
	require_once $comAdminPath.DS.'includes'.DS.'includes.php';

	if(!defined('JPATH_COMPONENT'))
	{
		define( 'JPATH_COMPONENT',					JPATH_BASE.DS.'components'.DS.XIEC_COMPONENT_NAME);
		define( 'JPATH_COMPONENT_SITE',				JPATH_SITE.DS.'components'.DS.XIEC_COMPONENT_NAME);
		define( 'JPATH_COMPONENT_ADMINISTRATOR',	JPATH_ADMINISTRATOR.DS.'components'.DS.XIEC_COMPONENT_NAME);
	}

	require_once dirname(__FILE__).DS.'..'.DS.'test'.DS.'unit'.DS.'common.php';
}
