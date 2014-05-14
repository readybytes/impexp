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

class ImpexpJsHelper 
{
	   
	   //store the values in community user table
	  public static function storeCommunityUser($userid, $userValues,$jsFieldMapping)
	   {
			$user = clone(CFactory::getUser($userid));
			if(empty($jsFieldMapping))
				return true;
			
			foreach($jsFieldMapping as $key => $value)
			 {
				if(('_'.$key) == '_params'){
				  if(IMPEXP_JVERSION === '1.5'){
				    $str_value = $userValues[$value];	
				    $str  = explode('\n',$str_value);
				    $data = array();
				    foreach($str as $key1=>$value1){
                       if(!empty($value1)){
                   	     list($key2,$value2) = explode('=',$value1);
                         $data[$key2]=$value2;
                       }
                   	}
                 	$user->_cparams->bind($data);
				  }
				  else{
					  $userValues[$value] = str_replace('\n', ',',$userValues[$value]);
		              $user->_cparams->bind($userValues[$value]);
				  }
				}
				else {
				  $user->set('_'.$key, $userValues[$value]);
				}
			}
			
			if(!$user->save())
				return false;
				
			return true;
		}
		
	  //store the values in community users values table
	  public static function storeCustomFields($userid, $userValues, $customFieldMapping,$mysess)
			{
			   $user = clone(CFactory::getUser($userid));
			   $cModel  = CFactory::getModel('Profile');
			   $data    = array();
			   $db = JFactory::getDBO();	
			   $table = ImpexpPluginHelper::findTableName('#__community_fields');  
		       $strquery = "SELECT `id`,`type` FROM ".$table;
		  	   $db->setQuery($strquery);  
		 	   $fieldsType = $db->loadAssocList('id');
		 	   
				foreach($customFieldMapping as $key => $value){
					if($fieldsType[$key]['type'] == 'birthdate' || $fieldsType[$key]['type'] == 'date'){
						//if birthdate field is left empty then default date will be set i.e 1-1-1970.
                       	if(!empty($userValues[$value]))
                        //change date in considerable format
						$userValues[$value] = date("Y-m-d H:i:s",strtotime($userValues[$value]));
			 	     }	
				//if object is not passed then access won't be set right
			 	$data[$key] 		= new stdClass();
                $data[$key]->value  = JString::str_ireplace("\\r", "\r", JString::str_ireplace("\\n", "\n", $userValues[$value]));
                $data[$key]->access = 0;

              }
              
			return $cModel->saveProfile($userid, $data);		
			}
			

   public static  function insertJsFields($userid,$customFieldMapping)
      {
  	      $db 	  = JFactory::getDBO();
  	       $jsvtable = ImpexpPluginHelper::findTableName('#__community_fields_values');  
  	      $query  = " select `field_id` FROM ".$jsvtable
		            ." WHERE ".$db->nameQuote('user_id')." = ". $userid;
		  $db->setQuery($query);  
		  $fields = $db->loadAssocList('field_id');
		  $values = null;
		   foreach($customFieldMapping as $fieldId => $value){
		  	 if(array_key_exists($fieldId, $fields))
		  	 	continue;
		  	 $values   = "(".$userid.","."$fieldId".")";  
			 $query    = " INSERT INTO ".$jsvtable
		  	            ."(`user_id`,`field_id`) VALUES ".$values;
		  	 $db->setQuery($query); 
		  	 $db->query();
		   }
	  }	
	  
	     /**
	 * Store 'Community_field_values' table data
	 */
   public static  function storeComFieldValues($userTable,$csvUser,$userIds)
	 {
   	    $csvUser[$userTable] = array();
		$db = JFactory::getDBO();
		$jsvtable = ImpexpPluginHelper::findTableName('#__community_fields_values');  
		$condition = "";
	    if(count($userIds)>0){
	    	$matches   = implode(',', $userIds );   
	    	$condition = " WHERE `user_id` IN ($matches) ";
	     }
           
	    $sql = " SELECT * FROM ".$jsvtable
			   .$condition
			   ." ORDER BY 	`user_id` ASC, `field_id` ASC";
		$db->setQuery($sql); 
		$jsUserData = $db->loadObjectList();

		foreach($jsUserData as $fields){
			if(!array_key_exists($fields->user_id, $csvUser['joomla']))
				continue;
			$csvUser[$userTable][$fields->user_id][$fields->field_id] =  preg_replace('!\r+!', '\\r', preg_replace('!\n+!', '\\n', $fields->value));
   		}
   		
   		//if data in field_values table doesn't exist for some users
		//then add a blank array for those users further processing
   		foreach ($userIds as $userid){
   			if(!array_key_exists($userid,$csvUser[$userTable]))
   				$csvUser[$userTable][$userid] = array();
   		}
		return $csvUser;    
	 }
	 
	 
	 
	/**
	 * Select informations from 'community_users' table,store it in $jsUserData variable
	 * @param $userIds-store userid
	 */
   public static function getJsUser($userIds)
   { 
	   	 $db = JFactory::getDBO();
	     $condition = "";
		 if(count($userIds)>0){
				$matches   = implode(',', $userIds );   
				$condition = " WHERE `userid` IN ($matches) ";
		 }
		  
		 $jstable = ImpexpPluginHelper::findTableName('#__community_users');  
		 $sql= "SELECT * FROM ".$jstable
		       .$condition." ORDER BY `userid`";
	     $db->setQuery($sql);
	     $jsUserData = $db->loadAssocList('userid');
	     return $jsUserData;
	 }
	 
/**
	 * Get all the custom fields
	 */
	 public static function getCustomFieldIds()
	 {
		$db	    =  JFactory::getDBO();
		$jsftable = ImpexpPluginHelper::findTableName('#__community_fields');  
		$query  = "  SELECT * "
				  ." FROM ".$jsftable
				  ." WHERE `type` <> ".$db->Quote('group')
				  ." ORDER BY `ordering`";
		$db->setQuery($query);		  
		return $db->loadObjectList('id');	
	}
}
