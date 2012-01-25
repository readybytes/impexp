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

class ImpexpPluginHelper
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
			$db = & JFactory::getDBO();
			$overwrite  = $mysess->get('overwrite');
			$userIds    =  $mysess->get('userid');
            $user 		= clone(JFactory::getUser());
			
			//Update user values
			if(IMPEXP_JOOMLA_17)
			{			
				$newUsertype = array_key_exists('usertype',$joomlaFieldMapping) ? $userValues[$joomlaFieldMapping['usertype']] : '2'; 
				$length=JString::strlen($newUsertype);
				if($length>1){
					$newUsertype= self::getUserTypeId($newUsertype);
				}
				if($newUsertype=="" || $newUsertype==null)
					$newUsertype='2';
			}
			
			if(IMPEXP_JOOMLA_15)
			{
				$authorize	= JFactory::getACL();
				$newUsertype = array_key_exists('usertype',$joomlaFieldMapping) ? $userValues[$joomlaFieldMapping['usertype']] : 'Registered';
				if($newUsertype=="")
				$newUsertype='Registered';
				$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));	
			}
			
		    //Set the userid according to the condition that whether the overwrite option is set or not
		    //or want to import userid or not.
			if($userIds=='0'){
			$user->set('id',0);
			if($overwrite==true && $overwrite_user_id)
				$user->set('id',$overwrite_user_id);
		  }
		  
		if($userIds=='1'){	 
			if($overwrite==true && empty($overwrite_user_id)){
			   //check whether username and email of differnet user  with
               //same id exist in the database.If exist then
				$checkUsernameEmail=self::checkIdinJoomla($userValues[$joomlaFieldMapping['id']]);
				if($checkUsernameEmail){
					$replaceCount=$mysess->get('replaceCount',0);
					$user->set('id',$userValues[$joomlaFieldMapping['id']]);
					$replaceCount=self::storeDeleteReplaceUser($userValues,$joomlaFieldMapping,$replaceCount);
					$mysess->set('replaceCount',$replaceCount);
					}
			 }

		 $user->set('id',$userValues[$joomlaFieldMapping['id']]);
		}
			
			$user->set('usertype', $newUsertype);
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
			if(IMPEXP_JOOMLA_15){
				$user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );
				$user->params = $user->_params->toString();
			}
			$user->set('block', '0');
	
			// Create the user table object
			$table 	= JTable::getInstance('user', 'JTable');
			$table->bind($user->getProperties());
	
			$usrid = $user->get('id');
	         if(isset($usrid)){ 
			    if($userIds==1){
			        $db = & JFactory::getDBO();
					$sql="SELECT * FROM ".$db->nameQuote('#__users')."where "."id =".$usrid;
					$db->setQuery($sql);
					$allData=$db->loadAssocList('id');
					if(empty($allData)){
						$sql = 'INSERT INTO '.$db->nameQuote('#__users').'(id) VALUES ('.($usrid).')';
						$db->setQuery($sql);
						$db->query();				
					}
	            } 
			 }
			//Store the user data in the database
			if (!$table->store())
				return false;
				
			$user->id = $table->get( 'id' );
			
			if(IMPEXP_JOOMLA_17)
			{
				$db = JFactory::getDBO();
				//map user group
				$query = "SELECT `group_id` FROM".$db->nameQuote('#__user_usergroup_map')
					         ."WHERE ".$db->nameQuote('user_id')."=".$db->Quote($user->id);
	            $db->setQuery($query);
	            $sql1 = " UPDATE ".$db->nameQuote('#__user_usergroup_map')
						." SET ".$db->nameQuote('group_id') ." = ".$db->Quote($newUsertype)
						." WHERE ".$db->nameQuote('user_id') ." = ".$db->Quote($user->id);
				$db->setQuery($sql1);
				$db->query();
				//UserController::_sendMail($user, $password);	
			}
			if($mysess->get('passwordFormat', 'joomla', 'importCSV') == 'joomla'){
				$sql = " UPDATE ".$db->nameQuote('#__users')
					   ." SET ".$db->nameQuote('password') ." = ".$db->Quote($userValues[$joomlaFieldMapping['password']])
					   ." WHERE ".$db->nameQuote('id') ." = ".$db->Quote($user->id);
				$db->setQuery($sql);
				$db->query();
			}
			return $user->id;
		}	
				
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
			  $joomlaField_name = self::getJsJoomlaField('#__users');
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
	
			//get the fields of the desired table
		   function getJsJoomlaField($table)
		    {
		        $db = JFactory::getDBO();
		    	$conf = JFactory::getConfig();
				$database = $conf->getValue('config.db');
		             $tableName = self::replacePrefix($table);
		             $sql="SELECT column_name FROM information_schema.columns
		                   WHERE table_name = '$tableName'
		                   AND table_schema = '$database'";
		            $db->setQuery($sql); 
		            $joomlaField_name =$db->loadResultArray();
		            return $joomlaField_name;
		    }
		    
	    //store the values in community user table
	   function storeCommunityUser($userid, $userValues,$jsFieldMapping)
	   {
			$user = clone(CFactory::getUser($userid));
			if(empty($jsFieldMapping))
				return true;
			
			foreach($jsFieldMapping as $key => $value)
			 {
				if(('_'.$key) == '_params')
				{
				  if(IMPEXP_JOOMLA_17)
				  {
				  $userValues[$value] = str_replace('\n', ',',$userValues[$value]);
                  $user->_cparams->bind($userValues[$value]);
				  }
				  if(IMPEXP_JOOMLA_15)
				  {
				    $str_value = $userValues[$value];	
				    $str  = explode('\n',$str_value);
				    $data = array();
				    foreach($str as $key1=>$value1)
                    {
                       if(!empty($value1))
                       {
                   	     list($key2,$value2) = explode('=',$value1);
                         $data[$key2]=$value2;
                       }
                   }
                 $user->_cparams->bind($data);
				  }
				}
				else 
				{
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
               if(IMPEXP_JOOMLA_17)
               {
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
		
	 /**
	   *replace the prefix of the table and add prefix(that is used by the current database) to tablename.
       */
     function replacePrefix($table)
       {   
           if(substr($table,0,3) == '#__')
             {
                  $tablePrefix = JFactory::getDBO()->getPrefix();
                  $table = $tablePrefix.substr($table,3);
             }
            return $table;
       }
	
       static function pathFS2URL($fsPath='')
       {    
       	// get reference path from root
       	    if(IMPEXP_JOOMLA_15){
               $urlPath        = JString::str_ireplace( JPATH_ROOT .DS , '', $fsPath);
       	    }
       	    if(IMPEXP_JOOMLA_17)
               $urlPath        = self::str_ireplace( JPATH_ROOT .DS , '', $fsPath);
               // replace all DS to URL-slash
               $urlPath        = JPath::clean($urlPath, '/');
               
               // prepend URL-root
               return JURI::root().$urlPath;
       }
       
       /**
        * Clonning function Due to bug in utf8_ireplace function
        */
       static public function str_ireplace($search, $replace, $str, $count = NULL)
       {
               
               if ( !is_array($search) ) {
       
               $slen = strlen($search);
               if ( $slen == 0 ) {
                   return $str;
               }
       
               $lendif = strlen($replace) - strlen($search);
               $search = utf8_strtolower($search);
       
               $search = preg_quote($search,"/");
               $lstr = utf8_strtolower($str);
               $i = 0;
               $matched = 0;
               while ( preg_match('/(.*)'.$search.'/Us',$lstr, $matches) ) {
                   if ( $i === $count ) {
                       break;
                   }
                   $mlen = strlen($matches[0]);
                   $lstr = substr($lstr, $mlen);
                   $str = substr_replace($str, $replace, $matched+strlen($matches[1]), $slen);
                   $matched += $mlen + $lendif;
                   $i++;
               }
               return $str;
       
           } else {
       
               foreach ( array_keys($search) as $k ) {
       
                   if ( is_array($replace) ) {
       
                       if ( array_key_exists($k,$replace) ) {
       
                           $str = utf8_ireplace($search[$k], $replace[$k], $str, $count);
       
                       } else {
       
                           $str = utf8_ireplace($search[$k], '', $str, $count);
       
                       }
       
                   } else {
       
                       $str = utf8_ireplace($search[$k], $replace, $str, $count);
       
                   }
               }
               return $str;
       
           }
       }
}
