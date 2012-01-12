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
				
	function storeJoomlaUser($userValues, $joomlaFieldMapping, $mysess, $overwrite_user_id)
		{
			$overwrite  = $mysess->get('overwrite');
            $user 		= clone(JFactory::getUser());
			$authorize	= JFactory::getACL();
			//$newUsertype = 'Registered';
			$newUsertype = array_key_exists('usertype',$joomlaFieldMapping) ? $userValues[$joomlaFieldMapping['usertype']] : 'Registered';
			//error_reporting(E_ALL ^ E_NOTICE); 
			//Update user values
			if($newUsertype=="")
				$newUsertype='Registered';
			if($overwrite == false)
			{
				 $user->set('id', 0);
			}
			else 
			{
				$user->set('id', $overwrite_user_id);
			}
			$user->set('usertype', $newUsertype);
			$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));		
			$name = array_key_exists('name',$joomlaFieldMapping) ? $userValues[$joomlaFieldMapping['name']] : $userValues[$joomlaFieldMapping['username']];
			
			
			if(!array_key_exists($joomlaFieldMapping['username'],$userValues)) 
				return false;
			else if(!array_key_exists($joomlaFieldMapping['email'],$userValues))
				return false;
			else if(!array_key_exists($joomlaFieldMapping['password'],$userValues))
				return false;
				
			$data = array(	'username'	=> $userValues[$joomlaFieldMapping['username']],
							'name'		=> $name,
							'email'		=> $userValues[$joomlaFieldMapping['email']],
							'password'	=> $userValues[$joomlaFieldMapping['password']],
							'password2'	=> $userValues[$joomlaFieldMapping['password']],
							'usertype'	=> $newUsertype,
							'block'		=> 0
						 );
						 
			// Bind the post array to the user object
			if (!$user->bind($data)) {
				JError::raiseError( 500, $user->getError());
			}	
				
			jimport('joomla.user.helper');
				$user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );
				$user->set('block', '0');
	
			// Create the user table object
			$table 	= JTable::getInstance('user', 'JTable');
			$user->params = $user->_params->toString();
			$table->bind($user->getProperties());
	
			//Store the user data in the database
			if (!$table->store())
				return false;
				
			$user->id = $table->get( 'id' );
			//UserController::_sendMail($user, $password);	
			if($mysess->get('passwordFormat', 'joomla', 'importCSV') == 'joomla'){
				$db = JFactory::getDBO();
				$sql = " UPDATE ".$db->nameQuote('#__users')
					   ." SET ".$db->nameQuote('password') ." = ".$db->Quote($userValues[$joomlaFieldMapping['password']])
					   ." WHERE ".$db->nameQuote('id') ." = ".$user->id;
				$db->setQuery($sql);
				$db->query();			
			}
			return $user->id;
		}			
	
	function storeCommunityUser($userid, $userValues,$jsFieldMapping)
		{			
			$user = clone(CFactory::getUser($userid));
			if(empty($jsFieldMapping))
				return true;
			
			foreach($jsFieldMapping as $key => $value)
			 {
				if(('_'.$key) == '_params')
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
				else 
				{
				   $user->set('_'.$key, $userValues[$value]);
				   
				}
			 }
		
             if(!$user->save())
			 return false;
				
			return true;
		}
		
	function storeCustomFields($userid, $userValues, $customFieldMapping)
			{
				$cModel = CFactory::getModel('Profile');
				$data =array();
				$db = JFactory::getDBO();		  
		        $strquery = "SELECT `id`,`type` FROM ".$db->nameQuote('#__community_fields');
		  	    $db->setQuery($strquery);  
		 	    $fieldsType = $db->loadAssocList('id');
				foreach($customFieldMapping as $key => $value){
					if( $fieldsType[$key]['type'] == 'birthdate' ||
					    $fieldsType[$key]['type'] == 'date'){
					      //change date in considerable format
					      $userValues[$value] = date("Y-m-d H:i:s",strtotime($userValues[$value]));
                    }
					$data[$key] = JString::str_ireplace("\\r", "\r", JString::str_ireplace("\\n", "\n", $userValues[$value]));
				}
				return $cModel->saveProfile($userid, $data);		
			}
		
	function getExistUserInCSV($users,$filename)
		{
			$file = JPATH_ROOT.DS.'cache'.DS.$filename;	
	    	$content="";
			foreach($users as $user=>$data){
						
				$content.= "\n".'"'.$data['username'].'","'.$data['email'];
				$content.= '"';
			}
			
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
			$content.= "\n".'"'.JText::_('username');
			$content.= '","'.JText::_('email').'"';
			$fh = fopen($file, 'w') or die("can't open file");
			fwrite($fh, $content);
			fclose($fh);
		}	
	
}
