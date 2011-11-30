<?php

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
				
	function storeJoomlaUser($userValues, $joomlaFieldMapping, $mysess, $overwrite_user_id = null)
		{
			$overwrite  = $mysess->get('overwrite');
			$user 		= clone(JFactory::getUser());
			
			$newUsertype = array_key_exists('usertype',$joomlaFieldMapping) ? $userValues[$joomlaFieldMapping['usertype']] : '2';
			//error_reporting(E_ALL ^ E_NOTICE); 
		
			//Update user values			
			$length=JString::strlen($newUsertype);
			if($length>1){
				$newUsertype= ImpexpPluginHelper::getUserTypeId($newUsertype);
			}
			
			if($newUsertype=="" || $newUsertype==null)
				$newUsertype='2';
		    //handling overwrite option
			if($overwrite == false)
			{
				 $user->set('id', 0);
			}
			else 
			{
				$user->set('id', $overwrite_user_id);
			}
			
			$user->set('usertype', $newUsertype);
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
				$user->set('block', '0');
	
			// Create the user table object
			$table 	= JTable::getInstance('user', 'JTable');
			//$user->params = $user->get('_params')->toString();
			$table->bind($user->getProperties());
	
			//Store the user data in the database
			if (!$table->store())
				return false;
				
			$user->id = $table->get( 'id' );
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
			
			if($mysess->get('passwordFormat', 'joomla', 'importCSV') == 'joomla'){
				$sql = " UPDATE ".$db->nameQuote('#__users')
					   ." SET ".$db->nameQuote('password') ." = ".$db->Quote($userValues[$joomlaFieldMapping['password']])
					   ." WHERE ".$db->nameQuote('id') ." = ".$db->Quote($user->id);
				$db->setQuery($sql);
				$db->query();
			}
			return $user->id;
		}	
				
	function getUserTypeId($usertype){
		$db = JFactory::getDBO();
		$query = " SELECT id FROM ".$db->nameQuote('#__usergroups')
		       ." WHERE ".$db->nameQuote('title') ." = ".$db->Quote($usertype);
		$db->setQuery($query, 0, 1);
		return $db->loadResult();
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
				  $userValues[$value] = str_replace('\n', ',',$userValues[$value]);
                  $user->_cparams->bind($userValues[$value]);
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
			   $cModel  = CFactory::getModel('Profile');
			   $data    = array();
			   $db = JFactory::getDBO();		  
		       $strquery = "SELECT `id`,`type` FROM ".$db->nameQuote('#__community_fields');
		  	   $db->setQuery($strquery);  
		 	   $fieldsType = $db->loadAssocList('id');
		 	   
				foreach($customFieldMapping as $key => $value){
					if($fieldsType[$key]['type'] == 'birthdate' || 
                       $fieldsType[$key]['type'] == 'date'){
                        //change date in considerable format
						$userValues[$value] = date("Y-m-d H:i:s",strtotime($userValues[$value]));
			 	     }	
                $data[$key] = JString::str_ireplace("\\r", "\r", JString::str_ireplace("\\n", "\n", $userValues[$value]));
				if(!empty($data))
					self::insertJsFields($userid,$customFieldMapping);
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
			$file = Jfactory::getConfig()->get('tmp_path').DS.$filename;
			
			
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
	    	$file = Jfactory::getConfig()->get('tmp_path').DS.$filename;
	    	
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