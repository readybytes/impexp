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

class ImpexpPluginImportHelper
{
	function checkUsernameinJoomla($username,$email)
		{
			$db = & JFactory::getDBO();
	
			$query = 'SELECT id FROM #__users WHERE username = ' . $db->Quote( $username ).
					' OR email = ' . $db->Quote( $email );
			$db->setQuery($query, 0, 1);
			return $db->loadResult();
		}
		
    function checkIdinJoomla($id)
	{
		$db = & JFactory::getDBO();
		$query = "SELECT username,email FROM #__users WHERE id = ". $db->Quote( $id );
		$db->setQuery($query, 0, 1);
		return $db->loadAssocList();
	}
		
		//store the user table entry 
	function storeJoomlaUser($userValues, $joomlaFieldMapping, $mysess, $overwrite_user_id = null)
		{
			$db = JFactory::getDBO();
            $user 		   = clone(JFactory::getUser());
            $importUserIds =  $mysess->get('userid');
            $newUsertype = self::getNewUserType($joomlaFieldMapping,$userValues,$user);
			$user->set('usertype', $newUsertype);
			$id = self::getUserId($mysess,$overwrite_user_id,$joomlaFieldMapping,$userValues);
			$user->set('id',$id);
			$name = array_key_exists('name',$joomlaFieldMapping) ? $userValues[$joomlaFieldMapping['name']] : $userValues[$joomlaFieldMapping['username']];
			
			if(!array_key_exists($joomlaFieldMapping['username'],$userValues)) 
				return false;
			else if(!array_key_exists($joomlaFieldMapping['email'],$userValues))
				return false;
			else if(!array_key_exists($joomlaFieldMapping['password'],$userValues))
				return false;
				
		   $data = self::getValueFields($userValues,$joomlaFieldMapping,$name,$newUsertype);
						 
			// Bind the post array to the user object
			if (!$user->bind($data)) {
				JError::raiseError( 500, $user->getError());
			}	
				
			jimport('joomla.user.helper');
			if(IMPEXP_JVERSION === '1.5'){
				$user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );
				$user->params = $user->_params->toString();
			}
			$user->set('block', '0');
			if(IMPEXP_JVERSION != '1.5'){
			 $groups=array();
			 $groups[$newUsertype]=$newUsertype;
			 $user->set('groups',$groups);
			}
	
			// Create the user table object
			$table 	= JTable::getInstance('user', 'JTable');
			$table->bind($user->getProperties());
	
			$usrid = $user->get('id');
	         if(isset($usrid))
			     if($importUserIds==1){
			     	self::insertRowInDB($usrid,$user);
	         }
			//Store the user data in the database
			if (!$table->store())
				return false;
				
			$user->id = $table->get( 'id' );
			
			//if(IMPEXP_JVERSION != '1.5')
			//		self::updateUserGroupMapTable($user,$newUsertype);
			
			if($mysess->get('passwordFormat', 'joomla', 'importCSV') == 'joomla'){
				$sql = " UPDATE ".$db->nameQuote('#__users')
					   ." SET ".$db->nameQuote('password') ." = ".$db->Quote($userValues[$joomlaFieldMapping['password']])
					   ." WHERE ".$db->nameQuote('id') ." = ".$db->Quote($user->id);
				$db->setQuery($sql);
				$db->query();
			}
			return $user->id;
	   }
	   
	   function getNewUserType($joomlaFieldMapping,$userValues,$user)
	   {
	    if(IMPEXP_JVERSION === '1.5')
			{
				$authorize	= JFactory::getACL();
				$newUsertype = array_key_exists('usertype',$joomlaFieldMapping) ? $userValues[$joomlaFieldMapping['usertype']] : 'Registered';
				if($newUsertype=="")
				$newUsertype='Registered';
				$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));	
			}
		   else
			{			
				$newUsertype = array_key_exists('usertype',$joomlaFieldMapping) ? $userValues[$joomlaFieldMapping['usertype']] : '2'; 
				$length=JString::strlen($newUsertype);
				if($length>1){
					$newUsertype= self::getUserTypeId($newUsertype);
				}
				if($newUsertype=="" || $newUsertype==null)
					$newUsertype='2';
			}
			return $newUsertype;
	   }
	   
	   	   	
	    //Set the userid according to the condition that whether the overwrite option is set or not
	    //or want to import userid or not.
	   function getUserId($mysess,$overwrite_user_id,$joomlaFieldMapping,$userValues)
	   {
	   		$overwrite     = $mysess->get('overwrite');
			$importUserIds =  $mysess->get('userid');
			if($importUserIds=='0'){
			$getUsersID = 0;
			if($overwrite==true && $overwrite_user_id)
			$getUsersID=$overwrite_user_id;
		  }
		  
		if($importUserIds=='1'){	 
			if($overwrite==true && empty($overwrite_user_id)){
			   //check whether username and email of differnet user  with
               //same id exist in the database.If exist then
				$checkUsernameEmail=self::checkIdinJoomla($userValues[$joomlaFieldMapping['id']]);
				if($checkUsernameEmail){
					$replaceCount=$mysess->get('replaceCount',0);
					$replaceCount=self::storeDeleteReplaceUser($userValues,$joomlaFieldMapping,$replaceCount);
					$mysess->set('replaceCount',$replaceCount);
					}
			 }
			 $getUsersID=$userValues[$joomlaFieldMapping['id']];
		}
		return $getUsersID;
	   }
	   
	   function insertRowInDB($usrid,$user)
	   {
	    $db =  JFactory::getDBO();
		$sql="SELECT * FROM ".$db->nameQuote('#__users')."where "."id =".$usrid;
		$db->setQuery($sql);
		$allData=$db->loadAssocList('id');
		if(empty($allData)){
			$sql = 'INSERT INTO '.$db->nameQuote('#__users').'(id) VALUES ('.($usrid).')';
			$db->setQuery($sql);
			$db->query();
			//maintain core_acl_aro and core_acl_groups_aro_map table
			if(IMPEXP_JVERSION === '1.5')
			{
				$sql="SELECT `id` FROM ".$db->nameQuote('#__core_acl_aro')."where "."value =".$usrid;
		        $db->setQuery($sql);
		        $getGID=$db->loadResult();
		        if(!empty($getGID)){
			         $sqlQuery= "UPDATE".$db->nameQuote('#__core_acl_aro')." SET `name` =".$user->get('name').",`order_value`= "."0". ",`hidden`= ". "0" ." where "."value =".$usrid;
                     $db->setQuery($sqlQuery);
                     $sqlQuery= "UPDATE".$db->nameQuote('#__core_acl_groups_aro_map')." SET `group_id` =".$user->get('gid')." where "."aro_id  =".$getGID;
                     $db->setQuery($sqlQuery);
		        }
		        else
		        {
					$acl = JFactory::getACL();
					$section_value = 'users';
					$acl->add_object( $section_value,$user->get('name'), $usrid, null, null, 'ARO' );
		            $acl->add_group_object( $user->get('gid'), $section_value, $usrid, 'ARO' );	
			    }
		    }		
		}
	   }
				
//       function updateUserGroupMaptable($user,$newUsertype)
//		    {
//		    	$db = JFactory::getDBO();
//				//map user group
//				$query = "SELECT `group_id` FROM".$db->nameQuote('#__user_usergroup_map')
//					         ."WHERE ".$db->nameQuote('user_id')."=".$db->Quote($user->id);
//	            $db->setQuery($query);
//	            $sql1 = " UPDATE ".$db->nameQuote('#__user_usergroup_map')
//						." SET ".$db->nameQuote('group_id') ." = ".$db->Quote($newUsertype)
//						." WHERE ".$db->nameQuote('user_id') ." = ".$db->Quote($user->id);
//				$db->setQuery($sql1);
//				$db->query();
//				//UserController::_sendMail($user, $password);	
//		    }
	   
	  function getUserTypeId($usertype)
	  {
		$db = JFactory::getDBO();
		$query = " SELECT id FROM ".$db->nameQuote('#__usergroups')
		        ." WHERE ".$db->nameQuote('title') ." = ".$db->Quote($usertype);
		$db->setQuery($query, 0, 1);
		return $db->loadResult();
	  }
	
	  //store all the table(joomla user,jomsocial user,community users value)fields 
	  //value of the user that are replaced.
      function storeDeleteReplaceUser($userValues,$joomlaFieldMapping,$replaceCount)
	  {     
	        $db          = JFactory::getDBO();
			$sqlQuery    = "SELECT * From ".$db->nameQuote('#__users')." 
						    WHERE `id` =".$userValues[$joomlaFieldMapping['id']];
		    $db->setQuery($sqlQuery);
		    $joomlaUsers   = $db->loadAssocList('id');
		    $csvUser       = array();
		    $csvJoomlaUser = ImpexpPluginExport::storeJsJoomlaUser('joomla',$joomlaUsers,$csvUser);
			// function to start creating csv from jomsocial and joomla users' table
			$user_id = array_keys($joomlaUsers);
			//function to process community_field_values table
			$csvComFieldJoomlaUser = ImpexpPluginExport::storeComFieldValues('cFieldValues',$csvJoomlaUser,$user_id);	
			//start creating csv for jomsocial and joomla users' fields
			$jsUsers = ImpexpPluginExport::getJsUser($user_id);
		
		    $completeCsv = ImpexpPluginExport::storeJsJoomlaUser('jomsocial',$jsUsers,$csvComFieldJoomlaUser);	    
			$finalCsv    = ImpexpPluginExport::setDataForCsv($completeCsv,$user_id);
			foreach ($finalCsv as $userid=>$result){
				  $result = rtrim($result, ',');
				  self::getExistUserInCSV($result,'replaceuser.csv');
				  $replaceCount++;
			 }
			
		    $sqlQuery="DELETE * From".$db->nameQuote('#__users')." WHERE `id` =".$userValues[$joomlaFieldMapping['id']];
		    $db->setQuery($sqlQuery);
		    $sqlQuery="DELETE * From".$db->nameQuote('#__community_users')." WHERE `id` =".$userValues[$joomlaFieldMapping['id']];
		    $db->setQuery($sqlQuery);
		    $sqlQuery="DELETE * From".$db->nameQuote('#__community_fields_values')." WHERE `id` =".$userValues[$joomlaFieldMapping['id']];
		    $db->setQuery($sqlQuery);
		    return $replaceCount;
		}
		//get all the values of joomla user table and store in the database.
		function getValueFields($userValues,$joomlaFieldMapping,$name,$newUsertype)
		{
			  $joomlaField_name = ImpexpPluginHelper::getJsJoomlaField('#__users');
			  $data = array();
			  foreach ($joomlaField_name as $fieldName){
				if($fieldName == 'id')
				continue;
				//set default settings if not set in csv file
				if($fieldName=='name'){
				  $data[$fieldName]=$name;
				  continue;
				}
				if($fieldName == 'usertype'){
				  $data[$fieldName] = $newUsertype;
				  continue;
				}
				if($fieldName == 'block'){
				  $data[$fieldName]=0;
				  continue;
				}
				if($fieldName == 'password'){
				  $data['password2']=$userValues[$joomlaFieldMapping[$fieldName]];
				}
				//if the value of the field is not set then continue process 
				if(isset($joomlaFieldMapping[$fieldName])==false)
				  continue;
				$data[$fieldName]= $userValues[$joomlaFieldMapping[$fieldName]];
			}
			return $data;
			}
		    
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
	function storeCustomFields($userid, $userValues, $customFieldMapping)
			{
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
               if(IMPEXP_JVERSION != '1.5'){
		           if(!empty($data))
	 			   self::insertJsFields($userid,$customFieldMapping);
               }
              }
			return $cModel->saveProfile($userid, $data);		
			}
			
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

	       //remove seperator and store values in form of array
           function removeQuotes($data,$seperator)
		      {
				$tempValues = array();
				$value           = explode($seperator,array_shift($data));
				$delimeterLength = strlen($seperator);
				$delimeter = "";
				//get second part of delimeter.For eg-,' then get '
				if($delimeterLength == 2)
				{
				$delimeter = $seperator[1];
				}
		        foreach ($value as $k=>$v){
					$tempValues[$k] = $v;
					if(!empty($tempValues[$k]))
					{
						//removing suffix delimeter.
						if(substr($v,-1,1) == $delimeter){
						  $tempValues[$k]  = substr($v,0,-1);
		                } 
		                //removing prefix delimeter.
						if(substr($tempValues[$k],0,1) == $delimeter){
					      $tempValues[$k] = substr($tempValues[$k],1);
						}
				   }  
			 }
				return $tempValues;
	     }

	function getExistUserInCSV($users,$filename)
		{
			$file = JPATH_ROOT.DS.'cache'.DS.$filename;	
	    	$content="";
			if($filename!='replaceuser.csv'){
			foreach($users as $user=>$data){
				$content.= "\n".'"'.$data['username'].'","'.$data['email'];
				$content.= '"';
				}
			}
			else 
			  $content.= $users;
			$fh = fopen($file, 'a') or die("can't open file");
					fwrite($fh, $content);
					fclose($fh);
			return;		
		}
		
	function deleteCSV($filename,$content)
		{
			$file = JPATH_ROOT.DS.'cache'.DS.$filename;
	    	if(file_exists($file)){
	    		unlink($file);
	    	}
	    	// Add data in file
	    	$content= $content;
			if($filename!='replaceuser.csv'){
				$content.= "\n".'"'.JText::_('username');
				$content.= '","'.JText::_('email').'"';
	    	}
			$fh = fopen($file, 'w') or die("can't open file");
			fwrite($fh, $content);
			fclose($fh);
		}	
}
