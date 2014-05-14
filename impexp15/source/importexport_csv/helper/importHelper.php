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
	public static function checkUsernameinJoomla($username,$email)
		{
			$db = JFactory::getDBO();
	
			$query = 'SELECT id FROM #__users WHERE username = ' . $db->Quote( $username ).
					' OR email = ' . $db->Quote( $email );
			$db->setQuery($query, 0, 1);
			return $db->loadResult();
		}
		
    public static function checkIdinJoomla($id)
	{
		$db =  JFactory::getDBO();
		$query = "SELECT username,email FROM #__users WHERE id = ". $db->Quote( $id );
		$db->setQuery($query, 0, 1);
		return $db->loadAssocList();
	}
		
		//store the user table entry 
	public static function storeJoomlaUser($userValues, $joomlaFieldMapping, $mysess, $overwrite_user_id = null)
		{
	    $db = JFactory::getDBO();
            $user 		   = clone(JFactory::getUser());
            $importUserIds =  $mysess->get('userid');
            $newUsertype = self::getNewUserType($joomlaFieldMapping,$userValues,$user,$overwrite_user_id);
            if(empty($newUsertype))
            {
            	$newUsertype = array('2');
            }
//			$user->set('usertype', $newUsertype);
			$id = self::getUserId($mysess,$overwrite_user_id,$joomlaFieldMapping,$userValues);
			$user->set('id',$id);
			$name = array_key_exists('name',$joomlaFieldMapping) ? $userValues[$joomlaFieldMapping['name']] : $userValues[$joomlaFieldMapping['username']];
			
			if(!array_key_exists($joomlaFieldMapping['username'],$userValues)) 
				return false;
			else if(!array_key_exists($joomlaFieldMapping['email'],$userValues))
				return false;
			else if(!array_key_exists($joomlaFieldMapping['password'],$userValues))
				return false;
				
		   $data = self::getValueFields($userValues,$joomlaFieldMapping,$name);
						 
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
//			 $groups=array();
//			 $groups[$newUsertype]=$newUsertype;
			 JArrayHelper::toInteger($newUsertype);
			 $user->set('groups',$newUsertype);
			}
	
			// Create the user table object
			$table 	= JTable::getInstance('user', 'JTable');
			$table->bind($user->getProperties());
	
			$usrid = $user->get('id');
	         if(isset($usrid))
			     if($importUserIds==1){
			     	self::insertRowInDB($usrid,$user,$overwrite_user_id);
	         }
			//Store the user data in the database
			if (!$table->store())
				return false;
				
		$user->id = $table->get( 'id' );
		if($mysess->get('passwordFormat', 'joomla', 'importCSV') == 'joomla'){
               $table = ImpexpPluginHelper::findTableName('#__users');
               $sql = " UPDATE ".$table
                                ." SET `password`  = ".$db->Quote($userValues[$joomlaFieldMapping['password']])
                                ." WHERE `id` = ".$db->Quote($user->id);
               

               $db->setQuery($sql);
               $db->query();
               }
		
        $table = ImpexpPluginHelper::findTableName('#__user_usergroup_map');
        if(isset($joomlaFieldMapping['usertype']))
        {
			
			 // it is required to delete all the rows first because we cant 
			 //update user entries with different group_id in single query.

        	 $sql = "DELETE FROM". $table." WHERE `user_id` = ". $user->id;
        	 $db->setQuery($sql);
	         $db->query();
	         $sql = 'INSERT INTO '.$table.'(user_id, group_id) VALUES '; 
	         foreach ($newUsertype as $usertype)
	         {
	         	$sqlParams[] = ' ('.($user->id).','.$usertype.')';
	         }
        	    $sql = $sql. implode(',', $sqlParams);
               	$db->setQuery($sql);
	         	$db->query();
        }		
          
			return $user->id;
	   }
	   
	 public static function getNewUserType($joomlaFieldMapping,$userValues,$user,$overwrite_user_id)
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
				// explode the usertype from the pattern "<ut>2^%%^12</ut>"	
				$newUsertype = array_key_exists('usertype',$joomlaFieldMapping) ? $userValues[$joomlaFieldMapping['usertype']] : 'Registered'; 
				$newUsertype = str_ireplace(array('<ut>','</ut>'), '', $newUsertype);
				if(empty($newUsertype)){
						$newUsertype='Registered';
				}
				$newUsertype = explode('^%%^', $newUsertype);		
				$newUsertype= self::getUserTypeId($newUsertype);
			}
			return $newUsertype;
	   }
	   
	   	   	
	    //Set the userid according to the condition that whether the overwrite option is set or not
	    //or want to import userid or not.
	   public static function getUserId($mysess,$overwrite_user_id,$joomlaFieldMapping,$userValues)
	   {
	   	    $Impexp_JoomlaJs  = $mysess->get('importDataTo');
	   		$overwrite     = $mysess->get('overwrite');
			$importUserIds =  $mysess->get('userid');
			if($importUserIds=='0'){
			$getUsersID = 0;
			if($overwrite==true && $overwrite_user_id){
				$getUsersID=$overwrite_user_id;
				
			}
		  }
		  
		if($importUserIds=='1'){	 
			if($overwrite==true && empty($overwrite_user_id)){
			   //check whether username and email of differnet user  with
               //same id exist in the database.If exist then
				$checkUsernameEmail=self::checkIdinJoomla($userValues[$joomlaFieldMapping['id']]);
				if($checkUsernameEmail){
					$replaceCount=$mysess->get('replaceCount',0);
					$replaceCount=self::storeDeleteReplaceUser($userValues,$joomlaFieldMapping,$replaceCount,$Impexp_JoomlaJs);
					$mysess->set('replaceCount',$replaceCount);
					}
			 }
			 $getUsersID=$userValues[$joomlaFieldMapping['id']];
		}
		return $getUsersID;
	   }
	   
	   public static function insertRowInDB($usrid,$user,$overwrite_user_id)
	   {
		    $db =  JFactory::getDBO();
	    $table = ImpexpPluginHelper::findTableName('#__users');
		$sql="SELECT * FROM ".$table."where "."id =".$usrid;
		$db->setQuery($sql);
		$allData=$db->loadAssocList('id');
		if(empty($allData)){
			if($overwrite_user_id)
                       {
                                $sqlQuery="DELETE From ".$table." WHERE `id` =".$overwrite_user_id;
                                $db->setQuery($sqlQuery);
                                $db->query();
                                     $isInstalled = ImpexpPluginHelper::jomsocialEnabled();
	                                if($isInstalled == true){
		                                $jsTable          = ImpexpPluginHelper::findTableName('#__community_users');
			                            $jsvTable          = ImpexpPluginHelper::findTableName('#__community_fields_values');
			                            $sqlQuery = "DELETE From ".$jsTable." WHERE `userid` =".$overwrite_user_id;
				                       $db->setQuery($sqlQuery);
				                       $db->query();
				                       $sqlQuery = "DELETE From ".$jsvTable." WHERE `id` =".$overwrite_user_id;
				                       $db->setQuery($sqlQuery);
				                       $db->query();
	                                }
                            $usergrouptable = ImpexpPluginHelper::findTableName('#__user_usergroup_map');
                           
                         
                        $sql = "DELETE FROM". $usergrouptable." WHERE `user_id` = ".$overwrite_user_id;
                        $db->setQuery($sql);
                        $db->query();
                       }
 
			
			$sql = 'INSERT INTO '.$table.'(id) VALUES ('.($usrid).')';
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
	   
	 public static function getUserTypeId($usertype = array('Registered'))
	  {
		// in joomla1.5, usergroup name exist and in j1.5+ version ids are there.
		// if ids are there, then no need to do further work.
	  	foreach ($usertype as $type)
	  	{
	  		if(is_numeric($type)){
	  			return $usertype;
	  		}
	  	}
	  	$table = ImpexpPluginHelper::findTableName('#__usergroups');
		$db = JFactory::getDBO();
		$query = " SELECT `id` FROM ".$table
		        ." WHERE ".$db->quoteName('title') ." IN ('".implode("',", $usertype)."')";
		$db->setQuery($query, 0, 1);
		return $db->loadColumn();
	  }
	
	  //store all the table(joomla user,jomsocial user,community users value)fields 
	  //value of the user that are replaced.
      public static function storeDeleteReplaceUser($userValues,$joomlaFieldMapping,$replaceCount,$Impexp_JoomlaJs)
	  {     
	        $db          = JFactory::getDBO();
	      	$table  = ImpexpPluginHelper::findTableName('#__users');
	        
	        
			$sqlQuery    = "SELECT * From ".$table." 
						    WHERE `id` =".$userValues[$joomlaFieldMapping['id']];
		    $db->setQuery($sqlQuery);
		    $joomlaUsers   = $db->loadAssocList('id');
		    $csvUser       = array();
		    $completeCsv = ImpexpPluginExport::storeJsJoomlaUser('joomla',$joomlaUsers,$csvUser);

			// function to start creating csv from jomsocial and joomla users' table
			$user_id = array_keys($joomlaUsers);
            $sqlQuery="DELETE * From".$table." WHERE `id` =".$userValues[$joomlaFieldMapping['id']];
		    $db->setQuery($sqlQuery); 

			//if joomla+js selected then only process it
			if($Impexp_JoomlaJs != 'Joomla'){
				//function to process community_field_values table
				$completeCsv = ImpexpJsHelper::storeComFieldValues('cFieldValues',$completeCsv,$user_id);	
				
				//start creating csv for jomsocial and joomla users' fields
				$jsUsers = ImpexpJsHelper::getJsUser($user_id);
			
			    $completeCsv = ImpexpPluginExport::storeJsJoomlaUser('jomsocial',$jsUsers,$completeCsv);
			    $jsTable 	 = ImpexpPluginHelper::findTableName('#__community_users');
			    $jsvTable 	 = ImpexpPluginHelper::findTableName('#__community_fields_values');
			    
			    $sqlQuery = "DELETE From".$jsTable." WHERE `id` =".$userValues[$joomlaFieldMapping['id']];
		        $db->setQuery($sqlQuery);
		        $sqlQuery = "DELETE From".$jsvTable." WHERE `id` =".$userValues[$joomlaFieldMapping['id']];
		        $db->setQuery($sqlQuery);
			}
			   $finalCsv    = ImpexpPluginExport::setDataForCsv($completeCsv,$Impexp_JoomlaJs,$user_id,',');
			    
			foreach ($finalCsv as $userid=>$result){
				  $result = rtrim($result, ',');
				  self::getExistUserInCSV($result,'replaceuser.csv');
				  $replaceCount++;
			 }
		     return $replaceCount;
		}
		//get all the values of joomla user table and store in the database.
		public static function getValueFields($userValues,$joomlaFieldMapping,$name)
		{
			  $joomlaField_name = ImpexpPluginHelper::getJsJoomlaField('#__users');
			  $data = array();
			  foreach ($joomlaField_name as $fieldName){
				if($fieldName == 'id' || $fieldName == 'usertype')
				continue;
				//set default settings if not set in csv file
				if($fieldName=='name'){
				  $data[$fieldName]=$name;
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
		    

	       //remove seperator and store values in form of array
           public static function removeQuotes($data,$seperator)
		      {
				$tempValues = array();
				$value           = explode($seperator,array_shift($data));
				$seperator 		 = trim($seperator);
				$delimeterLength = strlen($seperator);
				$delimeter = "";
				//get second part of delimeter.For eg-,' then get '
				if($delimeterLength >= 2)
				{
					$delimeter = substr($seperator,-1);
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

	public static function getExistUserInCSV($users,$filename)
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
		
	public static function deleteCSV($filename,$content)
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
