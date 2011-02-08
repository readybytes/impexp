<?php

if(!defined('_JEXEC')) die('Restricted access');

class GetData 
{	
	function _getUserData()
	{
		$db = JFactory::getDBO();
		$sql = " SELECT * FROM ".$db->nameQuote('#__users');
		$db->setQuery($sql); 
		$joomlaUsers = $db->loadObjectList('id');

		$sql = " SELECT * FROM ".$db->nameQuote('#__community_fields_values')
			  ." ORDER BY ".$db->nameQuote('user_id')." ASC,".$db->nameQuote('field_id')." ASC";
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