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
//     function tableExistOrNot()
//	 {
//	    $db = JFactory::getDBO();
//		$query = "SELECT * FROM information_schema.tables".
//	             "WHERE table_name =".$db->nameQuote('#__community_users');
//	    $db->setQuery($query, 0, 1);
//		return $db->loadResult();
//	 }
//     function checkJsptEnabled()
//	 {
//	     $db =  JFactory::getDBO();
//	     $enableOrNot = 'published';
//	     $plugin = '#__plugins';
//	     if(IMPEXP_JVERSION != '1.5'){
//	     	$enableOrNot = 'enabled';
//	     	$plugin 	 = '#__extensions';
//	     }
//		 $query  = 'SELECT '.$db->nameQuote($enableOrNot)
//                  .' FROM ' . $db->nameQuote($plugin)
//                  .' WHERE '.$db->nameQuote('element').'='.$db->Quote('xipt_system');
//
//          $db->setQuery($query);             
//          $actualState= (boolean) $db->loadResult();
//          if($actualState == false)
//              return $actualState;
//          $query  = 'SELECT '.$db->nameQuote($enableOrNot)
//                    .' FROM ' . $db->nameQuote( $plugin )
//                    .' WHERE '.$db->nameQuote('element').'='.$db->Quote('xipt_community');
//          $db->setQuery($query);                
//          $actualState = (boolean) $db->loadResult();
//         
//          return $actualState;
//	   }
	   
	   //store the values in community user table
	   function storeCommunityUser($userid, $userValues,$jsFieldMapping)
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
	  function storeCustomFields($userid, $userValues, $customFieldMapping,$mysess)
			{
			   $user = clone(CFactory::getUser($userid));
			   $cModel  = CFactory::getModel('Profile');
			   $data    = array();
			   $db = JFactory::getDBO();		  
		       $strquery = "SELECT `id`,`type` FROM ".$db->nameQuote('#__community_fields');
		  	   $db->setQuery($strquery);  
		 	   $fieldsType = $db->loadAssocList('id');
		 	   
				foreach($customFieldMapping as $key => $value){
					if($fieldsType[$key]['type'] == 'birthdate' || $fieldsType[$key]['type'] == 'date'){
						//if birthdate field is left empty then default date will be set i.e 1-1-1970.
                       	if(!empty($userValues[$value]))
                        //change date in considerable format
						$userValues[$value] = date("Y-m-d H:i:s",strtotime($userValues[$value]));
			 	     }	
                $data[$key] = JString::str_ireplace("\\r", "\r", JString::str_ireplace("\\n", "\n", $userValues[$value]));
//               if(IMPEXP_JVERSION != '1.5'){
//		           if(!empty($data))
//	 			   self::insertJsFields($userid,$customFieldMapping);
//                }
              }
              
//		     if($mysess->get('JSPTEnable') == 1){
//		     	$data = self::jsptEnabledWork($data,$user);
//			 }
			return $cModel->saveProfile($userid, $data);		
			}
			
//	  function jsptEnabledWork($data,$user)
//	  {
//		   $db = JFactory::getDBO();
//		   require_once (JPATH_ROOT.DS.'components'.DS.'com_xipt'.DS.'includes.php');
//		   $this->_pluginHandler = XiptFactory::getPluginHandler();
//		   
//		   //get the id of profiletype
//		   $query = "SELECT `id` FROM".$db->nameQuote('#__community_fields').
//					"WHERE `name`='Profiletype'";
//		   $db->setQuery($query);
//	 	   $profileId = $db->loadResult();
//		   
//           //get the default profile type if profiletype field is not map
//		   // or no data is there about profiletype.
//		   if(isset($data[$profileId])){
//	  	   	    if(XiptLibProfiletypes::validateProfiletype($data[$profileId]) == false)
//		   	       $data[$profileId] = XiptLibProfiletypes::getDefaultProfiletype();
//		   }
//		   else{
//		         $data[$profileId]  = XiptLibProfiletypes::getDefaultProfiletype();
//		   }
//		    //set the value  of profiletype in session. 
//		    $this->_pluginHandler->setDataInSession('SELECTED_PROFILETYPE_ID',$data[$profileId]);
//			$dispatcher = JDispatcher::getInstance();
//			$dispatcher->trigger( 'onAfterStoreUser', array( $user->getProperties(), true, true, false ) );
//			$this->_pluginHandler->resetDataInSession('SELECTED_PROFILETYPE_ID');
//		    return $data;
//		}
//		
    function insertJsFields($userid,$customFieldMapping)
      {
  	      $db 	  = JFactory::getDBO();
  	      $query  = " select `field_id` FROM ".$db->nameQuote('#__community_fields_values')
		            ." WHERE ".$db->nameQuote('user_id')." = ". $userid;
		  $db->setQuery($query);  
		  $fields = $db->loadAssocList('field_id');
		  $values = null;
		   foreach($customFieldMapping as $fieldId => $value){
		  	 if(array_key_exists($fieldId, $fields))
		  	 	continue;
		  	 $values   = "(".$userid.","."$fieldId".")";  
			 $query    = " INSERT INTO ".$db->nameQuote('#__community_fields_values')
		  	            ."(`user_id`,`field_id`) VALUES ".$values;
		  	 $db->setQuery($query); 
		  	 $db->query();
		   }
	  }	
	  
	     /**
	 * Store 'Community_field_values' table data
	 */
    function storeComFieldValues($userTable,$csvUser,$userIds)
	 {
   	    $csvUser[$userTable] = array();
		$db = JFactory::getDBO();
		$condition = "";
	    if(count($userIds)>0){
	    	$matches   = implode(',', $userIds );   
	    	$condition = " WHERE ".$db->nameQuote('user_id')." IN ($matches) ";
	     }
           
	    $sql = " SELECT * FROM ".$db->nameQuote('#__community_fields_values')
			   .$condition
			   ." ORDER BY ".$db->nameQuote('user_id')." ASC,".$db->nameQuote('field_id')." ASC";
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
   function getJsUser($userIds)
   { 
	   	 $db = JFactory::getDBO();
	     $condition = "";
		 if(count($userIds)>0){
				$matches   = implode(',', $userIds );   
				$condition = " WHERE ".$db->nameQuote('userid')." IN ($matches) ";
		 }
		 $sql= "SELECT * FROM ".$db->nameQuote('#__community_users')
		       .$condition." ORDER BY ".$db->nameQuote('userid');
	     $db->setQuery($sql);
	     $jsUserData = $db->loadAssocList('userid');
	     return $jsUserData;
	 }
	 
/**
	 * Get all the custom fields
	 */
	 function getCustomFieldIds()
	 {
		$db	    =  JFactory::getDBO();
		$query  = "  SELECT * "
				  ." FROM ".$db->nameQuote('#__community_fields')
				  ." WHERE ".$db->nameQuote('type') ." <> ".$db->Quote('group')
				  ." ORDER BY ".$db->nameQuote('ordering');
		$db->setQuery($query);		  
		return $db->loadObjectList('id');	
	}
}