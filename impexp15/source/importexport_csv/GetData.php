<?php
/*
* importexport_csv - JomSocial User Import Export
------------------------------------------------------------------------
* copyright	Copyright (C) 2010 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* Author : Team JoomlaXi @ Ready Bytes Software Labs Pvt. Ltd.
* Email  : shyam@joomlaxi.com
* License : GNU-GPL V2
* Websites: www.joomlaxi.com
* Technical Support:  Forum - http://joomlaxi.com/support/forum/47-impexp-1x.html
*/

// no direct access

if(!defined('_JEXEC')) die('Restricted access');

class GetData 
{	
	function _getUserData()
	{
		$db = JFactory::getDBO();
		$userTable = ImpexpPluginHelper::findTableName('#__users');
		$sql = " SELECT * FROM ".$userTable;
		$db->setQuery($sql); 
		$joomlaUsers = $db->loadObjectList('id');

		$valuesTable = ImpexpPluginHelper::findTableName('#__community_fields_values');
		$sql = " SELECT * FROM ".$valuesTable
			  ." ORDER BY `user_id` ASC,".$db->nameQuote('field_id')." ASC";
		$db->setQuery($sql); 
		$jsUserData = $db->loadObjectList();
		
		$userIds = array_keys($joomlaUsers);
		
		$csvUser=array();
		foreach($joomlaUsers as $user){			
			$csvUser[$user->id]['username'] = $user->username;	// first : username
			$csvUser[$user->id]['name'] 	= $user->name;		// second : name
			$csvUser[$user->id]['email'] 	= $user->email;		// third : email
			$csvUser[$user->id]['password'] = $user->password;	// first : password
			$csvUser[$user->id]['usertype'] = $user->usertype;
		}
		
		foreach($jsUserData as $fields){
			if(!array_key_exists($fields->user_id, $csvUser))
				continue;
				
			$csvUser[$fields->user_id][$fields->field_id] =  preg_replace('!\r+!', '\\r', preg_replace('!\n+!', '\\n', $fields->value));
		}
		return $csvUser;	
	}
				
}