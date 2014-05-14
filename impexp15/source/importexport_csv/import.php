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
require_once(dirname(__FILE__) .DS.'helper' .DS. 'importHelper.php');
jimport( 'joomla.filesystem.archive' );

class ImpexpPluginImport
{
    public $max_exec_time 	= null;
	public $memory_limit    = null;

	
	function __construct()
	{
		//get max_exec_time
		$this->max_exec_time = IMPEXP_MAX_EXEC_TIME;
		if(function_exists('ini_get')){
			$this->max_exec_time = ini_get('max_execution_time');
		}
		
		if ( (!is_numeric($this->max_exec_time)) || ($this->max_exec_time== 0)) {
			$this->max_exec_time = IMPEXP_MAX_EXEC_TIME;
		}
         // Apply bias
		$this->max_exec_time = ($this->max_exec_time) * IMPEXP_BIAS_TIME * 1000000;
		
		//get memory_limit
		$this->memory_limit = IMPEXP_MEM_LIMIT;
		if(function_exists('ini_get')){
		   $this->memory_limit =(int)substr(ini_get('memory_limit'),0,-1);
		}
		if ( (!is_numeric($this->memory_limit)) || ($this->memory_limit== 0)) {
			$this->memory_limit = IMPEXP_MEM_LIMIT;
		}
        // Apply bias
		$this->memory_limit = ($this->memory_limit) * IMPEXP_BIAS_MEMORY * 1048567;
	}
	
	function getUploaderHtml()
	{
		$currentUrl = JURI::getInstance()->toString();		
		ob_start();
		require_once(dirname(__FILE__) .DS.  'tmpl' .DS. 'uploader.php');
			
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
		 $destination=$storagePath.'importData';
		 JFolder::create($destination, 0777);
         //perform extract function only when it is zip file.
		if($fileCSV['type']=='application/zip')
			$this->extractZipFile($fileCSV,$destination);
		
		$seperator    = JRequest::getVar('seperator','","');
        $overwrite    = JRequest::getVar('overwrite','0');
        $importUserId = JRequest::getVar('userid','0');
        $Impexp_JoomlaJs = JRequest::getVar('importDataTo','JoomlaJS');
        
        $mysess->set('overwrite',$overwrite);
		$mysess->set('seperator',$seperator);
		$mysess->set('userid',$importUserId);
		$mysess->set('Impexp_JoomlaJs',$Impexp_JoomlaJs);
		$isInstalled = ImpexpPluginHelper::jomsocialEnabled();
	
		if($Impexp_JoomlaJs !='Joomla'){
	    if($isInstalled == false){
	    	   $msg = "PLG_IMPORTEXPORT_YOU_DO_NOT_HAVE_JOMSOCIAL_INSTALLED";
               self::loadHtmlForWarning($msg);
               exit();
            }
		}
		
		// set password format value in session
		$mysess->set('passwordFormat', JRequest::getVar('passwordFormat','joomla'), 'importCSV');
		$csvType = array('text/csv', 'text/comma-separated-values','application/csv','application/excel','application/octet-stream','application/vnd.ms-excel', 'application/vnd.msexcel');
		// if($fileCSV['type'] == 'text/csv' || $fileCSV['type'] == 'text/comma-separated-values'){
		if(in_array($fileCSV['type'], $csvType)){
		  if(JFile::exists($destination.DS.'import.csv'))
			      JFile::delete($destination.DS.'import.csv');
			 JFile::copy($fileCSV['tmp_name'], $destination.DS.'import.csv');
		}
		$file 	 = fopen($destination.DS.'import.csv', "r");

        //fgetsv removes the doubleQuotes from the first field of string.
		$columns = fgetcsv($file, 0, "\n");

	  /**XITODO:
        * Why check this condition manually,Try to clean this code
        */
		if(strlen($seperator) >= 3){
			$seperator = substr($seperator,1); //remove first letter for eg-"," as ,"
			$mysess->set('seperator',$seperator);
		}
	    $getSizeOfColumns = explode($seperator,(string)$columns[0]);
		//If only one array found after exploding string then show error msg
		if(sizeof($getSizeOfColumns)==1){
			$sepMsg = "PLG_IMPORTEXPORT_CSV_SEPERATOR_DOES_NOT_MATCH";
			self::loadHtmlForWarning($sepMsg);
			exit();
		}
		$columns = ImpexpPluginImportHelper::removeQuotes($columns, $seperator);
		$sizeOfFieldArray = count($columns);
		$mysess->set('sizeOfFieldArray',$sizeOfFieldArray);
		$this->setIndexingInSession($file, $mysess);
		fclose($file);
		
		// get all options of fields
		$optionHtml  = '';
		$optionHtml .= $this->getJoomlaFieldOptions();
		if($Impexp_JoomlaJs !='Joomla'){
			$optionHtml .= $this->getJSFieldOptions();
			$optionHtml .= $this->getCustomFieldOptions();
		}
		$index = 0;
		$html  = '';
		$currentUrl = JURI::getInstance()->toString();
		
		// get uploader html
		ob_start();
		require_once(dirname(__FILE__) .DS. 'tmpl' .DS. 'mapping.php');
	
		$content = ob_get_contents();
		ob_clean();
		
		return $content;
	}
	
	//function to extract file.
	function extractZipFile($fileCSV,$destination)
	{
		 $zip   = new JArchive();
		 $files = JFolder::files($destination);
		 //delete files in importdata folder if exist
		 foreach ($files as $file)
		    unlink($destination.DS.$file);
		 JFile::copy($fileCSV['tmp_name'],$destination.DS.'import.zip');
		 //extract file to destination
		 $result = $zip->extract($destination.DS.'import.zip',$destination );
         if ($result === false) 
           JError::raiseError(500, 'Error unzipping file.');
		 unlink($destination.DS.'import.zip');
		   
		 $file = JFolder::files($destination);
		 if(count($file)!= 1 || substr($file[0],-4)!= '.csv')
		   {
		    	$msg = "PLG_IMPORTEXPORT_CSV_EXTRACTED_FILE_NOT_IN_CSV_FORMAT";
		    	self::loadHtmlForWarning($msg);
		   	    exit();
		   }
		   $oldName = $destination.DS.$file[0];
		   $newName = $destination.DS.'import.csv';
		   rename($oldName,$newName);
	}
	
	function loadHtmlForWarning($msg)
	{
		?>
		<div style="width:100%;margin:200px 0;text-align:center;color:#6699cc;">
		<a style ="color:#6699cc;" href="http://joomlaxi.com/support/documentation/item/importing-user.html" target="_blank">
		<?php 
		echo JText::_($msg);
		?></a></div>
		<?php 
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
			
			if($mysess->has('fileIndex', 'importCSV'))
				 $mysess->clear('fileIndex', 'importCSV');

            //clear offset and impexp_count,count,discardCount,icount,sizeCount,replaceCount if exist
			 $mysess->clear('offset');
			 $mysess->clear('impexp_count');	
			 $mysess->clear('count');
             $mysess->clear('discardCount');
             $mysess->clear('icount');
             $mysess->clear('sizeCount');
             $mysess->clear('replaceCount');
			 $mysess->set('fileIndex', $fileIndex, 'importCSV');
			return true;	
		}

		function getJoomlaFieldOptions()
			{
				$db	= JFactory::getDBO();	
				if(IMPEXP_JVERSION <= '2.5')
				{
				$allColumns = $db->getTableFields('#__users');
				$columns = $allColumns['#__users'];
				}
				else 
				{
					$columns = $db->getTableColumns('#__users');
				}
				// append usertype while showing the fields if it does not exist.
				if(!in_array('usertype', $columns))
				{
					$columns['usertype'] = 'text';
				}
				$html  = '<option disabled="disabled"></option>';
				$html .= '<option disabled="disabled">Joomla User Table Fields</option>';
				foreach(array_keys($columns) as $c){
					$html .= '<option value="joomla_'.$c.'">'.JString::ucfirst($c).'</option>'; 
				}
				
				return $html;
			}
			
		function getJSFieldOptions()
			{
				$db	= JFactory::getDBO();		
				if(IMPEXP_JVERSION <= '2.5')
				{	
						$allColumns = $db->getTableFields('#__community_users');
						$columns = $allColumns['#__community_users'];
				}
				else
				{
						$columns = $db->getTableColumns('#__community_users');
				} 
				
				$html  = '<option disabled="disabled"></option>';
				$html .= '<option disabled="disabled">Jom Social User Table Fields</option>';
				foreach(array_keys($columns) as $c){
					$html .= '<option value="jsfield_'.$c.'">'.JString::ucfirst($c).'</option>'; 
				}
				
				return $html;
			}
				
		function getCustomFieldOptions()
			{
				$db	= JFactory::getDBO();
				$table = ImpexpPluginHelper::findTableName('#__community_fields');
				
				$query  = "  SELECT * "
						  ." FROM ".$table
						  ." ORDER BY `ordering`";
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
				$Impexp_JoomlaJs=$mysess->get('Impexp_JoomlaJs');
				// check for duplicate values 
				// there must be one to one mapping
				$fieldMapping['joomla']  = $this->getFieldMapping($post,'joomla');
				if($Impexp_JoomlaJs != 'Joomla'){
				   $fieldMapping['jsfield'] = $this->getFieldMapping($post,'jsfield');
				   $fieldMapping['custom']  = $this->getFieldMapping($post,'custom');
				}
				// save fields mapping in session		
				if($mysess->has('fieldMapping', 'importCSV'))
					{
					 $mysess->clear('fieldMapping', 'importCSV');
					 $mysess->clear('impexp_count');
					} 
				$mysess->set('fieldMapping', $fieldMapping, 'importCSV');
				
				//Deleting csv files
				ImpexpPluginImportHelper::deleteCSV('existuser.csv','Username and E-mails which are already exist.');
				ImpexpPluginImportHelper::deleteCSV('importuser.csv','Username and E-mails which are imported.');
				ImpexpPluginImportHelper::deleteCSV('discardusers.csv','Username and E-mails which are not imported,as userid already exist.');
				$allFields = ImpexpPluginExport::getAllFields($Impexp_JoomlaJs,",");
		        ImpexpPluginImportHelper::deleteCSV('replaceuser.csv',$allFields);
		        ImpexpPluginImportHelper::deleteCSV('sizediscarduser.csv','Username and E-mails which are not imported,as size of fields is not equal to data');
				//for testing purpose
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
				$Impexp_JoomlaJs = $mysess->get('Impexp_JoomlaJs');
				if($Impexp_JoomlaJs != 'Joomla'){
					require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
					require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'models'.DS.'profile.php');
				}
				$fieldMapping = $mysess->get('fieldMapping', array(), 'importCSV');
				$fileIndex    = $mysess->get('fileIndex', array(), 'importCSV');
				
				// if session expired then what
				if(empty($fieldMapping) || (empty($fileIndex) && !$mysess->has('offset'))){
					$currentUrl = JURI::getInstance();
					$currentUrl->setVar('importCSVStage', 'complete');
					JFactory::getapplication()->redirect(JRoute::_($currentUrl->toString(), false));
				}
				
				$html='';
				$file = fopen($storagePath.'importData'.DS.'import.csv', "r");
				
				//check whether offset is set or not because in 1 block(1000) of
				// users,if all users can't be processed in one request then offset
				// and index of the uncompleted block is set for further processing.
				list($offset, $index) = self::checkOffset($mysess,$fileIndex);
				fseek($file,$offset);
				list($count,$discardCount,$icount,$sizeCount) = self::getCounts($mysess);
			    $existuser       = array();
				$importuser      = array();
				$discardUser     = array();
				$sizeDiscardUser = array();
					while(($data = fgetcsv($file, 0, "\n")) !== FALSE && ftell($file) <= $index['end'])
				    {
					 	$userValues=ImpexpPluginImportHelper::removeQuotes($data,$mysess->get('seperator'));
					 	//if their is empty row then
					 	if(count($userValues)==1) continue;
					 	//if(isset($userValues[0]) && empty($userValues[0])) continue;
					 	$fieldJ           = $fieldMapping['joomla'];
						$useroffset       = $fieldJ['username'];
						$emailoffset      = $fieldJ['email'];
					 	$sizeOfFieldArray = $mysess->get('sizeOfFieldArray');
					 	//if size of field value is not equal data value then
					 	if(count($userValues)!=$sizeOfFieldArray){
					 		self::storeDiscardUser($sizeDiscardUser,$sizeCount,$userValues,$fieldMapping,$useroffset,$emailoffset);
						    continue;
					 	}
						$checkUsername	   = ImpexpPluginImportHelper::checkUsernameinJoomla($userValues[$useroffset],$userValues[$emailoffset]);
	                    $overwrite_user_id = $checkUsername;
						if($checkUsername){
						   $this->storeUser($mysess,$userValues,$useroffset,$emailoffset,$overwrite_user_id,$fieldMapping,$count,$existuser);
				           $importuser_count++;
	                       continue;
	                     } 
	                     if( $mysess->get('overwrite') == false && $mysess->get('userid') == '1'){
	                       $checkId=ImpexpPluginImportHelper::checkIdinJoomla($userValues[$fieldJ['id']]);
	                       //check whether username and email of differnet user 
	                       // with same id exist in the database.If exist then,
	                       if($checkId){
	                       	self::storeDiscardUser($discardUser,$discardCount,$userValues,$fieldMapping,$useroffset,$emailoffset);
		                    continue;
	                       }
	                     }
	                     $this->storeUser($mysess,$userValues,$useroffset,$emailoffset,$overwrite_user_id,$fieldMapping,$icount,$importuser);
				         $importuser_count++; 
				   
					    //check whether sufficent time and memory is availabe or not
						if(!$this->getCurrentStatus($startTime))continue;
					    
	                    //set offset if sufficient time or space is not available
					    $offset=ftell($file);
					    $mysess->set('offset',$offset);
					    $mysess->set('index',$index);
					    break;
				 }
				//if block is finished then clear offset
			    if(ftell($file) >= $index['end']){
			      $mysess->clear('offset'); 
			    }
				fclose($file);	
				//Add users in csv file.
				self::writeDataInCSV($existuser,$importuser,$discardUser,$sizeDiscardUser);
				//set values in session
				self::setValueInSession($mysess,$count,$icount,$discardCount,$sizeCount,$importuser_count);
				self::loadHtml($importuser_count,$mysess);
				self::refreshImport();	
		      }	
		      
		      function checkOffset($mysess,$fileIndex)
		      {
		      if($mysess->has('offset'))
				 {
				   $offset = $mysess->get('offset');
				   $index  = $mysess->get('index');
				   $mysess->set('fileIndex', $fileIndex, 'importCSV');
				 }
				else 
				{   
					$index = array_shift($fileIndex);
					$mysess->set('fileIndex', $fileIndex, 'importCSV');
					$offset = $index['start'];  
				 }
				 return array($offset,$index);
		      }
		      
		      function getCounts($mysess)
		      {
		      	$count           = $mysess->get('count',0);
                $discardCount    = $mysess->get('discardCount',0);
                $icount          = $mysess->get('icount',0);
                $sizeCount       = $mysess->get('sizeCount',0);
                return array($count,$discardCount,$icount,$sizeCount);
		      }
		      
		      function storeDiscardUser(&$DiscardUser,&$dCount,$userValues,$fieldMapping,$useroffset,$emailoffset)
		      {
		      	$DiscardUser[$dCount]['username'] = $userValues[$useroffset];
			    $DiscardUser[$dCount]['email']    = $userValues[$emailoffset];
			    $dCount++;
		      }
		      
		     function writeDataInCSV($existuser,$importuser,$discardUser,$sizeDiscardUser)
		     {
		     	ImpexpPluginImportHelper::getExistUserInCSV($existuser,'existuser.csv'); 
		     	ImpexpPluginImportHelper::getExistUserInCSV($importuser,'importuser.csv');
		     	ImpexpPluginImportHelper::getExistUserInCSV($discardUser,'discardusers.csv');  
		     	ImpexpPluginImportHelper::getExistUserInCSV($sizeDiscardUser,'sizediscarduser.csv');
		     }
		     
			function setValueInSession($mysess,$count,$icount,$discardCount,$sizeCount,$importuser_count)
			{	  	
				$mysess->set('count',$count);
				$mysess->set('icount', $icount);
		    	$mysess->set('discardCount', $discardCount);
		    	$mysess->set('sizeCount', $sizeCount);
		    	$mysess->set('impexp_count',$importuser_count);
			}
		/**
		 * store the user data in database
		 */
       function storeUser($mysess,$userValues,$useroffset,$emailoffset,$overwrite_user_id,$fieldMapping,&$count,&$userData)
	    {
	     $Impexp_JoomlaJs = $mysess->get('Impexp_JoomlaJs');
		 $userData[$count]['username'] = $userValues[$useroffset];
		 $userData[$count]['email']    = $userValues[$emailoffset];
		 $count++;
		 //$existuser[$count]['password']=$userValues[$fieldMapping['joomla']['password']];
		 $overwrite = $mysess->get('overwrite');
             if($overwrite == true || empty($overwrite_user_id))
			  {                        
			   $newUserId    = ImpexpPluginImportHelper::storeJoomlaUser($userValues, $fieldMapping['joomla'], $mysess,$overwrite_user_id);
			   //$session->get('resultData');
			   if(!$newUserId ) continue;
			   if($Impexp_JoomlaJs != 'Joomla'){
			   $cUser        = ImpexpJsHelper::storeCommunityUser($newUserId , $userValues,$fieldMapping['jsfield']);
			   $customFields = ImpexpJsHelper::storeCustomFields($newUserId , $userValues, $fieldMapping['custom'],$mysess);	
			   }
			  }
	    }	
	   
       function loadHtml($importuser_count,$mysess)
	    {
			?>
			<br/><br/>
			<div style="overflow:hidden;width: 80%;margin: auto;">
			<div style = "text-align: center;font-style: italic;font-size: 18px;color: #666;border-bottom: 1px solid #eee;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DO_NOT_CLOSE_THIS_WINDOW_WHILE_IMPORTING_USER_DATA'); echo "<br/><br/>";?>
			</div>
			<div style = "width:100%;margin:20px 0;text-align:center;color:#6699cc;font-weight:bold;cursor:pointer;font-weight:bold;font-size:14px;font-style:italic;">
			<?php echo JText::_('PLG_IMPORTEXPORT_CSV_NUMBER_OF_USERS_IMPORTED').$importuser_count;?>		
			</div>
		    </div>	 
			<?php
	    }  

      function refreshImport()
	    {
		  $currentUrl   = JURI::getInstance();
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
	   }
	    
		function complete($count,$icount,$replaceCount,$sizeCount,$discardCount)
		{
			ob_start();			
		  	require_once(dirname(__FILE__) .DS. 'tmpl' .DS. 'complete.php');
			$content=ob_get_contents();
			ob_clean();
			return $content;
		}
        
		//get status of consumed time and space
		function getCurrentStatus($startTime)
		{   static $time = 0;
			$time = $time + (microtime()-$startTime);
			$space = memory_get_usage();
			//check the percentage of memory and time remaining
		    if( (1 - $time / $this->max_exec_time) > IMPEXP_PERCENTAGE  &&  (1 - $space / $this->memory_limit) > IMPEXP_MEM_PERCENT_LEFT)
			   return false;
		    return true;
		}
}


