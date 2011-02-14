<?php

require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'helper.php');

class ImpexpPluginImport
{
	function getUploaderHtml()
		{
			$currentUrl = JURI::getInstance()->toString();		
			ob_start();
			require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'tmpl' .DS. 'uploader.php');
				
			$html = ob_get_contents();
			ob_clean();
			return $html;
		}
		
	function getMappingHtml($mysess, $storagePath)
	{	
		$fileCSV 	= JRequest::getVar( 'fileUploaded' , '' , 'FILES' , 'array' );
		if(!isset($fileCSV['tmp_name']) || empty($fileCSV['tmp_name'])){
			return $this->getUploaderHtml();
		}
		
		// set password format value in session
		$mysess->set('passwordFormat', JRequest::getVar('passwordFormat','joomla'), 'importCSV');
		
		if(JFile::exists($storagePath.'import.csv'))
			JFile::delete($storagePath.'import.csv'); 
			
		JFile::copy($fileCSV['tmp_name'], $storagePath.'import.csv');
		$file 	 = fopen($storagePath.'import.csv', "r");
		$columns = explode(',', array_shift(fgetcsv($file, 1, "\n")));
		$this->setIndexingInSession($file, $mysess);
		fclose($file);
		
		// get all options of fields
		$optionHtml  = '';
		$optionHtml .= $this->getJoomlaFieldOptions();
		$optionHtml .= $this->getJSFieldOptions();
		$optionHtml .= $this->getCustomFieldOptions();
		
		$index = 0;
		$html  = '';
		$currentUrl = JURI::getInstance()->toString();
		
		// get uploader html
		
		ob_start();
		require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'tmpl' .DS. 'mapping.php');
		
		$content = ob_get_contents();
		ob_clean();
		
		return $content;
	}

		function setIndexingInSession($file , $mysess)
		{
			$index 	= 1;		
			$fileIndex = array();		
			$indexing['start'] = ftell($file);
			
			while(($data = fgetcsv($file, 0, "\n")) !== FALSE){
				if($index % 500 == 0){
					$indexing['end'] = ftell($file);
					array_push($fileIndex, $indexing);
					$indexing['start'] = ftell($file);
				}
				$index++;
			}
			// if end recods % 500 is not 0	
			$indexing['end'] = ftell($file);
			array_push($fileIndex, $indexing);
			
			if($mysess->has('fileIndex', 'importCSV'))
				 $mysess->clear('fileIndex', 'importCSV');
				 
			$mysess->set('fileIndex', $fileIndex, 'importCSV');
			return true;	
		}

		function getJoomlaFieldOptions()
			{
				$db	=& JFactory::getDBO();			
				$userTable = new JTable('#__users','id', $db);
				$allColumns = $userTable->_db->getTableFields('#__users');
				
				$columns = $allColumns['#__users'];
				$html  = '<option disabled="disabled"></option>';
				$html .= '<option disabled="disabled">Joomla User Table Fields</option>';
				foreach(array_keys($columns) as $c){
					$html .= '<option value="joomla_'.$c.'">'.JString::ucfirst($c).'</option>'; 
				}
				
				return $html;
			}
			
		function getJSFieldOptions()
			{
				$db	=& JFactory::getDBO();			
				$userTable = new JTable('#__community_users','userid', $db);
				$allColumns = $userTable->_db->getTableFields('#__community_users');
				
				$columns = $allColumns['#__community_users'];
				$html  = '<option disabled="disabled"></option>';
				$html .= '<option disabled="disabled">Jom Social User Table Fields</option>';
				foreach(array_keys($columns) as $c){
					$html .= '<option value="jsfield_'.$c.'">'.JString::ucfirst($c).'</option>'; 
				}
				
				return $html;
			}
				
		function getCustomFieldOptions()
			{
				$db	=& JFactory::getDBO();
				$query  = "  SELECT * "
						  ." FROM ".$db->nameQuote('#__community_fields')
						  ." ORDER BY ".$db->nameQuote('ordering');
				$db->setQuery($query);		  
				$columns = $db->loadObjectList('id');
				$html  = '<option disabled="disabled"></option>';
				$html .= '<option disabled="disabled">Jom Social Custom Fields</option>';
				foreach($columns as $c){
					if($c->type == 'group') continue;
					$html .= '<option value="custom_'.$c->id.'">'.JString::ucfirst($c->name).'</option>';
				}
				return $html;
			}	
			
		function importData($mysess)
			{
				$post = JRequest::get('post');
				// check for duplicate values 
				// there must be one to one mapping
				$fieldMapping['joomla']  = $this->getFieldMapping($post,'joomla');
				$fieldMapping['jsfield'] = $this->getFieldMapping($post,'jsfield');
				$fieldMapping['custom']  = $this->getFieldMapping($post,'custom');
				
				// save fields mapping in session		
				if($mysess->has('fieldMapping', 'importCSV'))
					{
					 $mysess->clear('fieldMapping', 'importCSV');
					} 
				$mysess->set('fieldMapping', $fieldMapping, 'importCSV');
				
				//Deleting csv files
				ImpexpPluginHelper::deleteCSV('existuser.csv','Username and E-mails which are already exist.');
				ImpexpPluginHelper::deleteCSV('importuser.csv','Username and E-mails which are imported.');
				
				$currentUrl = JURI::getInstance()->toString();
				JFactory::getapplication()->redirect(JRoute::_($currentUrl.'&importCSVStage=createUser', false));
			}	
		
		function getFieldMapping($post, $suffix)
			{
				$fields = array();
				foreach($post as $key => $value){
					// none should not be added so check value of $value
					if(JString::stristr($value, $suffix) && $value)
						$fields[JString::str_ireplace($suffix.'_', '', $value)] = JString::str_ireplace('csvField', '', $key); 
				}
				return $fields;
			}
				
		function createUser($mysess, $storagePath)
			{
				require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
				require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'models'.DS.'profile.php');
				
				$fieldMapping = $mysess->get('fieldMapping', array(), 'importCSV');
				$fileIndex    = $mysess->get('fileIndex', array(), 'importCSV');
				
				// if session expired then what
				if(empty($fieldMapping) || empty($fileIndex)){
					$currentUrl = JURI::getInstance();
					$currentUrl->setVar('importCSVStage', 'complete');
					JFactory::getapplication()->redirect(JRoute::_($currentUrl->toString(), false));
				}
				//error_reporting(E_ALL ^ E_NOTICE); 
				$index 		  = array_shift($fileIndex);
				$mysess->set('fileIndex', $fileIndex, 'importCSV');
				$html='';
				$file = fopen($storagePath.'import.csv', "r");
				fseek($file, $index['start']);
				$count=0;
				$icount=0;
				$existuser=array();
				$importuser=array();
				while(($data = fgetcsv($file, 1, "\n")) !== FALSE && ftell($file) <= $index['end']) {
					$userValues = explode(',', JString::str_ireplace("\"", '', array_shift($data)));
					
					if(empty($userValues)) continue;
					$fieldJ= $fieldMapping['joomla'];
					$useroffset=$fieldJ['username'];
					$emailoffset=$fieldJ['email'];
					$checkUsername	=	ImpexpPluginHelper::checkUsernameinJoomla($userValues[$useroffset],$userValues[$emailoffset]);
					if($checkUsername){
						$existuser[$count]['username'] = $userValues[$useroffset];
						$existuser[$count]['email'] = $userValues[$emailoffset];
						//$existuser[$count]['password']=$userValues[$fieldMapping['joomla']['password']];
						$count++;
						continue;
					}
					
					$importuser[$icount]['username'] = $userValues[$useroffset];
					$importuser[$icount]['email'] = $userValues[$emailoffset];
					//$existuser[$count]['password']=$userValues[$fieldMapping['joomla']['password']];
					$icount++;		
					$newUserId = ImpexpPluginHelper::storeJoomlaUser($userValues, $fieldMapping['joomla'], $mysess);
		
					// TODO : what if enable to save usrs
					if(!$newUserId) continue;
					$cUser  = ImpexpPluginHelper::storeCommunityUser($newUserId, $userValues,$fieldMapping['jsfield']);
					$customFields = ImpexpPluginHelper::storeCustomFields($newUserId, $userValues, $fieldMapping['custom']);
				}
				
				$isEOF = feof($file);
				fclose($file);	
				//Add existed user in file.
				ImpexpPluginHelper::getExistUserInCSV($existuser,'existuser.csv');
				
				//Add imported user in file
				ImpexpPluginHelper::getExistUserInCSV($importuser,'importuser.csv');
				
				$currentUrl = JURI::getInstance();		
				JFactory::getapplication()->redirect(JRoute::_($currentUrl->toString(), false));		
			}
						
		function complete()
		{
			ob_start();			
			require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'tmpl' .DS. 'complete.php');
			
			$content=ob_get_contents();
			ob_clean();
			return $content;
		}
}