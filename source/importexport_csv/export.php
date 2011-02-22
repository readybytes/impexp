<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );

class ImpexpPluginExport 
{
	function createCSV($storagePath)
	{
		$usertype=array('Administrator','Super Administrator');

		$db = JFactory::getDBO();
		$sql = "SELECT COUNT(*) FROM ".$db->nameQuote('#__users');
		$db->setQuery($sql);
		$total_user = $db->loadResult();

		//open a file which contain the data fetched from the database
		$fp=fopen($storagePath.'exportdata.csv',"w");
		$fields = $this->getCustomFieldIds();

		//fetch limited data from database and store it into a temporary file	
		for($start=0;$start<=$total_user;$start=$start+IMPEXP_LIMIT)
		{	
			$users	=	$this->getUserData($start);
			foreach($users as $id => $data)
			{
				// do not export admin user
				if(!in_array($data['usertype'],$usertype))
				{
					$csvdata="\n".'"'.$data['username'].'", "'.$data['name'].'", "'.$data['email'].'", "'.$data['password'].'", "'.$data['usertype'];
					foreach($fields as $f)
					{
						if(array_key_exists($f->id, $data))
							$csvdata.='","'.$data[$f->id];
						
						else 
							$csvdata.= '", "';
					}
					$csvdata.= '"';
					fwrite($fp,$csvdata);
				}
			}
	    }
	    	if(defined('TESTMODE'))
	    	{
	    		return true;
	    	}
			fclose($fp);
			$this->setDataInCSV($storagePath);
	}
	
	function getUserData($start)
		{
			//get limited User data from database
			$db = JFactory::getDBO();
			$sql = " SELECT * FROM ".$db->nameQuote('#__users')
					." WHERE ".$db->nameQuote('block'). "=". "0"
					." LIMIT ".$start.",".IMPEXP_LIMIT;
			$db->setQuery($sql); 
			$joomlaUsers = $db->loadObjectList('id');
			
			$sql = " SELECT * FROM ".$db->nameQuote('#__community_fields_values')
				  ."ORDER BY ".$db->nameQuote('user_id')." ASC,".$db->nameQuote('field_id')." ASC ";
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
	
	function setDataInCSV($storagePath)
		{
			$fields = $this->getCustomFieldIds();
			ob_start();
		
			header('Content-type: application/csv');
			header("Content-type: application/octet-stream");
	    	header("Content-Disposition: attachment; filename=user.csv");
	   
			echo '"'.JText::_('username');
			echo '","'.JText::_('name');
			echo '","'.JText::_('email');
			echo '","'.JText::_('password');
			echo '","'.JText::_('UserType');
			
			foreach($fields as $f)
				echo '","'.JText::_($f->name);

			echo file_get_contents($storagePath.'exportdata.csv');
			 
			exit;
			$content = ob_get_contents();
			ob_clean();
		}
	function getCustomFieldIds()
	{
			$db	=& JFactory::getDBO();
			$query  = "  SELECT * "
					  ." FROM ".$db->nameQuote('#__community_fields')
					  ." WHERE ".$db->nameQuote('type') ." <> ".$db->Quote('group')
					  ." ORDER BY ".$db->nameQuote('ordering');
			$db->setQuery($query);		  
			return $db->loadObjectList('id');	
	
	}
}