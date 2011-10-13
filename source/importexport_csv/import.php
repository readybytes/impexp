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
if(!class_exists('JProfiler'))
require_once(JPATH_ROOT.DS.'libraries'.DS.'joomla'.DS.'error'.DS.'profiler.php');
require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'importexport_csv' .DS. 'helper.php');

class ImpexpPluginImport
{

	public $max_exec_time 	= null;
	public $memory_limit    = null;
	const  MAX_EXEC_TIME	= 30;
	const  BIAS_TIME		= 0.50;
	const  MEM_LIMIT        = 32;
	const  BIAS_MEMORY      = 0.60;
	const  PERCENTAGE       = 0.20;
	
	function __construct()
	{
		//get max_exec_time
		$this->max_exec_time = self::MAX_EXEC_TIME;
		if(function_exists('ini_get')){
			$this->max_exec_time = ini_get('max_execution_time');
		}
		
		if ( (!is_numeric($this->max_exec_time)) || ($this->max_exec_time== 0)) {
			$this->max_exec_time = self::MAX_EXEC_TIME;
		}
         // Apply bias
		$this->max_exec_time = ($this->max_exec_time) * self::BIAS_TIME * 1000000;
		
		//get memory_limit
		$this->memory_limit = self::MEM_LIMIT;
		if(function_exists('ini_get')){
		   $this->memory_limit =(int)substr(ini_get('memory_limit'),0,-1);
		}
		if ( (!is_numeric($this->memory_limit)) || ($this->memory_limit== 0)) {
			$this->memory_limit = self::MEM_LIMIT;
		}
        // Apply bias
		$this->memory_limit = ($this->memory_limit) * self::BIAS_MEMORY * 1048567;
	}
	
	function getUploaderHtml()
		{
			$currentUrl = JURI::getInstance()->toString();		
			ob_start();
			require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'importexport_csv' .DS. 'tmpl' .DS. 'uploader.php');
				
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
		//set overwrite in session
		$overwrite  = JRequest::getVar('overwrite','0');
        $mysess->set('overwrite',$overwrite);
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
		require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'importexport_csv' .DS. 'tmpl' .DS. 'mapping.php');
		
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
				if($index % IMPEXP_LIMIT == 0){
					$indexing['end'] = ftell($file);
					array_push($fileIndex, $indexing);
					$indexing['start'] = ftell($file);
				}
				$index++;
			}
			// if end recods % 500 is not 0	
			$indexing['end'] = ftell($file);
			array_push($fileIndex, $indexing);
			
			if($mysess->has('fileIndex', 'importCSV')){
				 $mysess->clear('fileIndex', 'importCSV');
			}
            //clear varibles offset and impexp_count if exist
			$mysess->clear('offset');
			$mysess->clear('impexp_count');	 
			$mysess->set('fileIndex', $fileIndex, 'importCSV');
			return true;	
		}

		function getJoomlaFieldOptions()
			{
				$db	=& JFactory::getDBO();			
				$allColumns = $db->getTableFields('#__users');
				
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
				$allColumns = $db->getTableFields('#__community_users');
				
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
					 $mysess->clear('impexp_count');
					} 
				$mysess->set('fieldMapping', $fieldMapping, 'importCSV');
				
				//Deleting csv files
				ImpexpPluginHelper::deleteCSV('existuser.csv','Username and E-mails which are already exist.');
				ImpexpPluginHelper::deleteCSV('importuser.csv','Username and E-mails which are imported.');
				
				if(defined('TESTMODE')){
					return true;
				}
					
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
				
		function createUser($mysess, $storagePath,$importuser_count)
			{
				$startTime = JProfiler::getmicrotime();
				require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
				require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'models'.DS.'profile.php');
				
				$fieldMapping = $mysess->get('fieldMapping', array(), 'importCSV');
				$fileIndex    = $mysess->get('fileIndex', array(), 'importCSV');
				 $overwrite    = $mysess->get('overwrite');
				 
				// if session expired then what
				if(empty($fieldMapping) || (empty($fileIndex) && !$mysess->has('offset'))){
					$currentUrl = JURI::getInstance();
					$currentUrl->setVar('importCSVStage', 'complete');
					JFactory::getapplication()->redirect(JRoute::_($currentUrl->toString(), false));
				}
				//error_reporting(E_ALL ^ E_NOTICE); 
				$html='';
				$file = fopen($storagePath.'import.csv', "r");
				
				//check whether offset is set or not:
                //if in the block of 1000, all users have not yet processed then we set offset and its corresponding index 
				if($mysess->has('offset'))
				 {
				   $offset = $mysess->get('offset');
				   $index = $mysess->get('index');
				   $mysess->set('fileIndex', $fileIndex, 'importCSV');
				 }
				else 
				{   
					$index = array_shift($fileIndex);
					$mysess->set('fileIndex', $fileIndex, 'importCSV');
					$offset = $index['start'];
				}
				
				fseek($file,$offset);
				$count=0;
				$icount=0;
				$existuser=array();
				$importuser=array();
				while(($data = fgetcsv($file, 1, "\n")) !== FALSE && ftell($file) <= $index['end']) 
				{    
                    //handling ',' existance in between the value

				     $value= explode(',"',array_shift($data));
				 	foreach ($value as $k=>$v){
					  //$userValues[$k] = JString::str_ireplace("\"",'',$v);
                      if(substr($v,-1,1)=='"')
					    $userValues[$k] =substr($v,0,-1);
					  else 
					    $userValues[$k] =$v;
				 	}
					if(empty($userValues)) continue;
					$fieldJ= $fieldMapping['joomla'];
					$useroffset=$fieldJ['username'];
					$emailoffset=$fieldJ['email'];
					$checkUsername	=	ImpexpPluginHelper::checkUsernameinJoomla($userValues[$useroffset],$userValues[$emailoffset]);
					$overwrite_user_id = $checkUsername;
					if($checkUsername){
						$existuser[$count]['username'] = $userValues[$useroffset];
						$existuser[$count]['email'] = $userValues[$emailoffset];
						//$existuser[$count]['password']=$userValues[$fieldMapping['joomla']['password']];
						$count++;
						$importuser_count++;
                          if($overwrite == true)
						  {                        
							$newUserId    = ImpexpPluginHelper::storeJoomlaUser($userValues, $fieldMapping['joomla'], $mysess,$overwrite_user_id);
							if(!$newUserId) continue;
							$cUser        = ImpexpPluginHelper::storeCommunityUser($newUserId , $userValues,$fieldMapping['jsfield']);
							$customFields = ImpexpPluginHelper::storeCustomFields($newUserId , $userValues, $fieldMapping['custom']);	
						  }
                            continue;
						//$newUserId    = ImpexpPluginHelper::storeJoomlaUser($userValues, $fieldMapping['joomla'], $mysess,$checkUsername);
						//$cUser        = ImpexpPluginHelper::storeCommunityUser($checkUsername, $userValues,$fieldMapping['jsfield']);
						//$customFields = ImpexpPluginHelper::storeCustomFields($checkUsername, $userValues, $fieldMapping['custom']);	
						
				}
					
					$importuser[$icount]['username'] = $userValues[$useroffset];
					$importuser[$icount]['email'] = $userValues[$emailoffset];
					//$existuser[$count]['password']=$userValues[$fieldMapping['joomla']['password']];
					$icount++;	
					$importuser_count++;	
					$newUserId = ImpexpPluginHelper::storeJoomlaUser($userValues, $fieldMapping['joomla'], $mysess);
		
					// TODO : what if enable to save users
					if(!$newUserId) continue;
					$cUser  = ImpexpPluginHelper::storeCommunityUser($newUserId, $userValues,$fieldMapping['jsfield']);
					$customFields = ImpexpPluginHelper::storeCustomFields($newUserId, $userValues, $fieldMapping['custom']);

					//check whether sufficent time and memory is availabe or not
					if(!$this->getCurrentStatus($startTime))continue;
				    
                    //set offset if sufficient time or space is not available
				    $offset=ftell($file);
				    $mysess->set('offset',$offset);
				    $mysess->set('index',$index);
				    break;
				 }
				//if block is finished then clear offset
			    if(ftell($file) >= $index['end'])
			    {
			      $mysess->clear('offset'); 
			    }
				fclose($file);	
				//Add existed user in file.
				ImpexpPluginHelper::getExistUserInCSV($existuser,'existuser.csv');
				
				//Add imported user in file
				ImpexpPluginHelper::getExistUserInCSV($importuser,'importuser.csv');
				  
				echo "<br/><br/>";
				?>
			    <div style="overflow:hidden;width: 80%;margin: auto;">
				 <div style="text-align: center;font-style: italic;font-size: 18px;color: #666;border-bottom: 1px solid #eee;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DO_NOT_CLOSE_THIS_WINDOW_WHILE_IMPORTING_USER_DATA'); echo "<br/><br/>";?>
				 </div>
				 <div style="width:100%;margin:20px 0;text-align:center;color:#6699cc;font-weight:bold;cursor:pointer;font-weight:bold;font-size:14px;font-style:italic;">
				 <?php echo JText::_('PLG_IMPORTEXPORT_CSV_NUMBER_OF_USERS_IMPORTED').$importuser_count;?>		
				 </div>
		        </div>	 
			 <?php 
				
				$mysess->set('impexp_count',$importuser_count);
				$currentUrl = JURI::getInstance();
				?>
				<script>
			   window.onload = function()
			   {
				  setTimeout("redirect()", 3000);
			   }
			
			   function redirect()
			  {
				window.location = "<?php echo JRoute::_($currentUrl->toString()); ?>"			
					
			  }
			   </script>
			   <?php 
			//JFactory::getapplication()->redirect(JRoute::_($currentUrl->toString(), false));		
			}
						
		function complete()
		{
			ob_start();			
			require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'importexport_csv' .DS. 'tmpl' .DS. 'complete.php');
			
			$content=ob_get_contents();
			ob_clean();
			return $content;
		}
		
		//get status of consumed time and space
		function getCurrentStatus($startTime)
		{   
			static $time = 0;
			$time = $time + (JProfiler::getmicrotime()-$startTime);
			$space = JProfiler::getMemory();
			//check the percentage of memory and time remaining
		    if( (1 - $time / $this->max_exec_time) > self::PERCENTAGE  &&  (1 - $space / $this->memory_limit) > self::PERCENTAGE)
			   return false;
		    return true;
		}
}
