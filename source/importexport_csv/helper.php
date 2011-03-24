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
				
	function storeJoomlaUser($userValues, $joomlaFieldMapping, $mysess)
		{
			$user 		= clone(JFactory::getUser());
			
			$newUsertype = array_key_exists('usertype',$joomlaFieldMapping) ? $userValues[$joomlaFieldMapping['usertype']] : 'Registered';
			//error_reporting(E_ALL ^ E_NOTICE); 
			//Update user values
			if($newUsertype=="")
				$newUsertype=2;
			$user->set('id', 0);
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
					   ." WHERE ".$db->nameQuote('id') ." = ".$db->Quote($user->id);
				$db->setQuery($sql);
				$db->query();

				$sql1 = " UPDATE ".$db->nameQuote('#__user_usergroup_map')
					   ." SET ".$db->nameQuote('group_id') ." = ".$db->Quote($newUsertype)
					   ." WHERE ".$db->nameQuote('user_id') ." = ".$db->Quote($user->id);
				$db->setQuery($sql1);
				$db->query();
			}
			return $user->id;
		}			
	
	function storeCommunityUser($userid, $userValues,$jsFieldMapping)
		{
			$user = clone(CFactory::getUser($userid));
			if(empty($jsFieldMapping))
				return true;
				
			foreach($jsFieldMapping as $key => $value){
				$user->set($key, $userValues[$value]);
			}
			
			if(!$user->save())
				return false;
				
			return true;
		}
		
	function storeCustomFields($userid, $userValues, $customFieldMapping)
			{
				$cModel = CFactory::getModel('Profile');
				$data =array();
				foreach($customFieldMapping as $key => $value)
					$data[$key] = JString::str_ireplace("\\r", "\r", JString::str_ireplace("\\n", "\n", $userValues[$value]));
				
				return $cModel->saveProfile($userid, $data);		
			}
		
	function getExistUserInCSV($users,$filename)
		{
			$file = JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'importexport_csv'.DS.$filename;
	
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
			$file = JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'importexport_csv'.DS.$filename;
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