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
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once(dirname(__FILE__) .DS. 'helper' .DS. 'helper.php');
require_once(dirname(__FILE__) .DS. 'helper' .DS. 'jsHelper.php');
jimport( 'joomla.filesystem.archive' );

class ImpexpPluginExport 
{ 
    function getExportHtml($currentUrl)
	{ 
		$currentUrl = JURI::getInstance()->toString();	
    	ob_start();
		require_once( dirname(__FILE__).DS. 'tmpl' .DS. 'download.php');
		$html = ob_get_contents();
		ob_clean();
		return $html;
    }
    
    function getExportDataTable()
    {
    	$currentUrl = JURI::getInstance()->toString();	
		ob_start();
		require_once( dirname(__FILE__).DS. 'tmpl' .DS. 'exportDataTableHtml.php');
		$html = ob_get_contents();
		ob_clean();
		return $html;
    
    }
    
	function createCSV($storagePath,$mysess)
	{
		$Impexp_JoomlaJs   = JRequest::getVar('exportDataFrom',"JoomlaJS");
		$exportSeparator   = JRequest::getVar('exportSeparator',',','', 'string');
		$writeFields       = JRequest::getVar('writeFields',0);
		if($writeFields == 1){
		  self::writeHeaderInCsv($storagePath,$Impexp_JoomlaJs,$exportSeparator);
		}
        if($Impexp_JoomlaJs !='Joomla'){
		    $isInstalled = ImpexpPluginHelper::jomsocialEnabled();
			if($Impexp_JoomlaJs && $isInstalled == false){
		           $msg = "PLG_IMPORTEXPORT_YOU_DO_NOT_HAVE_JOMSOCIAL_INSTALLED";
		           self::loadHtmlForWarning($msg);
		           exit();
		        }
        }
		
		$db = JFactory::getDBO();
		if(IMPEXP_JVERSION == '1.5')
		{
		$sql = "SELECT COUNT(*) FROM ".$db->nameQuote('#__users')
			   ."WHERE ".$db->nameQuote('block'). "=". "0"." AND ".$db->nameQuote('usertype')
			   ."NOT IN ('Administrator','Super Administrator','deprecated','Super Users')";
		$db->setQuery($sql);
		$total_user = $db->loadResult();
		}
		else 
		{
			$sql = "SELECT g.`user_id` FROM ". $db->quoteName('#__user_usergroup_map')." as g "
					." INNER JOIN". $db->quoteName('#__usergroups')."as u on g. `group_id` = u.`id`	
					WHERE u. `title` IN ('Administrator','Super Administrator','deprecated','Super Users')";
			$db->setQuery($sql);
			$user = $db->loadColumn();
			
			$sql = "SELECT COUNT(*) FROM ".$db->quoteName('#__users')
			   ."WHERE ".$db->quoteName('block'). "=". "0"." AND ".$db->quoteName('id')
			   ."NOT IN (".implode(',', $user).")";
			$db->setQuery($sql);
			$total_user = $db->loadResult();
		}
        $filePath   = $storagePath.'exportdata.csv';
	    //if existing file is not writable 
		if ( file_exists($filePath) &&
		     !is_writable($filePath)){
		  echo JText::_("PLG_IMPORTEXPORT_CSV_PERMISSION_DENIED");
		  exit();
		}
		
		//open a file which contain the data fetched from the database
		$fp     = fopen($filePath,"a");

		//get the starting position from where to process	
		$start  = JRequest::getVar('end',0);	
		//clear variables from session if exist
		if($start == 0 && $mysess->has('limit') && $mysess->has('isSet'))
		{
			$mysess->clear('limit');
			$mysess->clear('isSet');
		}
	    if($start<=$total_user)
		{	
			//get limit from session that is to be used for processing
			$limit    = $mysess->get('limit',IMPEXP_EXP_LIMIT);
			$users	  =	$this->getUserData($start,$limit,$mysess,$Impexp_JoomlaJs);
			$finalCsv = self::setDataForCsv($users, $Impexp_JoomlaJs, NULL ,$exportSeparator);
		    foreach ($finalCsv as $userid=>$result)
			 {
			  $result = rtrim($result, $exportSeparator);
			  fwrite($fp,$result);
			 }
			//for testing purpose
		    if(defined('TESTMODE'))
	    	 {
	    		return true;
	    	 }
	        $end=$start+$limit;
			fclose($fp);
		    $this->refreshExport($end,$Impexp_JoomlaJs,$exportSeparator);
	    }
		fclose($fp);
		$this->setDataInCSV($storagePath);
	}
	
	//write the header fields in the csv file
	function writeHeaderInCsv($storagePath,$Impexp_JoomlaJs,$exportSeparator)
     {
			$csvFileFields = "";
			self::deleteFile($storagePath);
	    	$fp = fopen($storagePath.'exportdata.csv',"a");
	    	$csvFileFields = self::getAllFields($Impexp_JoomlaJs,$exportSeparator);
	    	fwrite($fp,$csvFileFields);
	 }
	 
	 //function to delete temporary file,if exist
	 function deleteFile($storagePath)
	 {
	   $filePath1 = $storagePath.'exportdata.csv';
	   $filePath2 = $storagePath.DS.'exportData.zip';
	   if (file_exists($filePath1))
	       unlink($filePath1);
	   if(file_exists($filePath2))
	       unlink($filePath2);
	 }
	
	
	public static function loadHtmlForWarning($msg)
		{
			?>
			<div style="width:100%;margin:75px 0;text-align:center;color:#6699cc;">
			<a style ="color:#6699cc;" href="http://joomlaxi.com/support/documentation/item/importing-user.html" target="_blank">
			<?php 
			echo JText::_($msg);
			?></a></div>
			<?php 
		}
	
	function getUserData($start,$limit,$mysess,$Impexp_JoomlaJs)
	{
		$startTime   = JProfiler::getmicrotime();
		$csvUser     = array();
		$completeCsv = array();
		$joomlaUsers = self::getJoomlaUser($start,$limit);
		if(empty($joomlaUsers)){
			?>
			<div style="width:100%;margin:70px 0;text-align:center;color:#6699cc;valign:top;">
			<a style ="color:#6699cc;" href="http://joomlaxi.com/support/documentation/item/importing-user.html" target="_blank">
			<?php
			echo JText::_("PLG_IMPORTEXPORT_CSV_YOUR_JOOMLA_TABLE_IS_EMPTY");
			?></a></div>
			<?php 
	        exit();
		}
		//for joomla users' fields
		$completeCsv = self::storeJsJoomlaUser('joomla',$joomlaUsers,$csvUser);
		$userIds = array_keys($joomlaUsers);
	   
		// storing userids in temperory file
		$fp = fopen(IMPEXP_TEMP_FILE_PATH, 'w');
        fwrite($fp, serialize($userIds));
        fclose($fp);

		if($Impexp_JoomlaJs != 'Joomla'){
        	//for jomsocial custom fields 
			$completeCsv = ImpexpJsHelper::storeComFieldValues('cFieldValues',$completeCsv,$userIds);
			//for jomsocial users' fields
			$jsUsers 	 = ImpexpJsHelper::getJsUser($userIds);
		    $completeCsv = self::storeJsJoomlaUser('jomsocial',$jsUsers,$completeCsv);
		}
	   //set final export limit if not set
	   if(!$mysess->has('isSet')){
	     $limit = self::setFinalLimit($startTime);
	     $mysess->set('limit',$limit);
	     $mysess->set('isSet',true);
	   }
	      return $completeCsv;
	}
		
	/**
	 * Select informations from 'user' table,store it in $joomlaUserData variable
	 * @param $start-Store the Starting position from where to process
	 * @param $limit-Store the Number of users to be processed 
	 */
	function getJoomlaUser($start,$limit)
	{
		$db  = JFactory::getDBO();
		if(IMPEXP_JVERSION == '1.5')
		{
		 $sql = "SELECT * FROM ".$db->nameQuote('#__users')
		  	   ."WHERE ".$db->nameQuote('block'). "="."0"." AND ".$db->nameQuote('usertype')
			   ."NOT IN ('Administrator','Super Administrator','deprecated','Super Users') LIMIT ".$start.",".$limit;
		 $db->setQuery($sql);
		 $joomlaUserData = $db->loadAssocList('id');
		 return $joomlaUserData;
		}
		else 
		{
			 $sql = "SELECT g.`user_id` FROM ". $db->quoteName('#__user_usergroup_map')." as g "
						." INNER JOIN". $db->quoteName('#__usergroups')."as u on g. `group_id` = u.`id`	
						WHERE u. `title` IN ('Administrator','Super Administrator','deprecated','Super Users')";
				$db->setQuery($sql);
				$user = $db->loadColumn();
				
			 $sql = "SELECT u .* , group_concat( g.`group_id` ) as usertype FROM ".$db->quoteName('#__users')."as u , ". $db->quoteName('#__user_usergroup_map')." as g "
			  	   ."WHERE u.`id` = g.`user_id` AND ".$db->quoteName('block'). "="."0"." AND ".$db->quoteName('id')
				   ."NOT IN (".implode(',', $user).") GROUP BY g.`user_id` LIMIT ".$start.",".$limit;
			 $db->setQuery($sql);
			 $joomlaUserData = $db->loadAssocList('id');
			 return $joomlaUserData;
		}
	}

    /**
	 * store JS and joomla users' table data
	 * $joomlaUsers - contains Js ans joomla users' table data
	 */
	 function storeJsJoomlaUser($userTable,$jsJoomlaUsers,$csvUser)
	 {
		$id='id';
		if($userTable == "jomsocial")
		{
			$id = 'userid';
			//if data in community_users doesn't exist for users
		    //then add a blank array for further processing
	       $fp      = fopen(IMPEXP_TEMP_FILE_PATH, 'r');
	       $result  = file_get_contents(IMPEXP_TEMP_FILE_PATH);
	       $userIds = unserialize($result);
		   foreach($userIds as $userId){
			  if(!isset($jsJoomlaUsers[$userId]))
			 	$csvUser[$userTable][$userId][] = array();
		    }
		}   
		 foreach ($jsJoomlaUsers as $user){
	    	foreach ($user as $name => $value){ 
	    		$str = trim($value,",");
	    		$csvUser[$userTable][$user[$id]][$name] = preg_replace('!\r+!', '\\r', preg_replace('!\n+!', '\\n', $str));
	    		if($name == 'params' && strrpos($value,',') == true)
	    		{
	    		  $csvUser[$userTable][$user[$id]][$name] = str_ireplace(',','\\n',$str);
	    		} 
	        }
	    }
		return $csvUser;
	 }
		

		
	 /**
      *decide final export limit to be used 
      */
	 function setFinalLimit($startTime)
	 {  
	 	$value = new ImpexpPluginImport();
	 	$space = memory_get_usage(); //consumed space 
	    $limit= (int)(($value->memory_limit/$space)*IMPEXP_EXP_LIMIT*0.80); //80% of next possible limit
	 	return $limit;
	 }

    /**
     *store the data in CSV format 
     */
    function setDataForCsv($users,$Impexp_JoomlaJs,$userIds=null,$exportSeparator)
    {   
       if(!isset($userIds))
       {
	       $fp    = fopen(IMPEXP_TEMP_FILE_PATH, 'r');
	       $result = file_get_contents(IMPEXP_TEMP_FILE_PATH);
	       $userIds = unserialize($result);
       }

    	foreach($userIds as $userId){
        	$finalCsv[$userId]="\n";
       		$joomlaField_name = ImpexpPluginHelper::getJsJoomlaField('#__users');
       		//getting user table values.
        	foreach ($joomlaField_name as $name){
        		//to support multi usergroup to user.
        		if($name == 'usertype')
        		{
	        		$users['joomla'][$userId][$name] = 	str_ireplace(',', '^%%^', $users['joomla'][$userId][$name]);
	        		$users['joomla'][$userId][$name] = '<ut>'.$users['joomla'][$userId][$name].'</ut>';
        		}
		    	if(!empty($users['joomla'][$userId][$name])){
		    		$finalCsv[$userId].='"'.$users['joomla'][$userId][$name].'"'.$exportSeparator;
			 	}	
		     	else{ 
		    	$finalCsv[$userId].='""'.$exportSeparator;
		    	}
        	}
        	if($Impexp_JoomlaJs !='Joomla'){
		  	   $fields = ImpexpJsHelper::getCustomFieldIds();
		  	   //getting community field values's table values
			   foreach($fields as $f){
				    if(array_key_exists($f->id, $users['cFieldValues'][$userId]))
						$finalCsv[$userId].='"'.$users['cFieldValues'][$userId][$f->id].'"'.$exportSeparator;
					else
						$finalCsv[$userId].= '""'.$exportSeparator;
			   }
			   //getting community user's table values
			   $JSfield_name = ImpexpPluginHelper::getJsJoomlaField('#__community_users');
			   foreach ($JSfield_name as $name){
			      	if($name=='userid')
			    		continue;
					if(!empty($users['jomsocial'][$userId][$name]))
						$finalCsv[$userId].='"'.$users['jomsocial'][$userId][$name].'"'.$exportSeparator;	
					else 
					 	$finalCsv[$userId].='""'.$exportSeparator;
			   }
         	}
        } 
        return $finalCsv;
      }
 
 

	 /**
	  * Store the fields of tables in csv format
	  * @param $storagePath-Give the path where user.csv file is saved
	  */  
	function setDataInCSV($storagePath)
	{
		self::createZipFile($storagePath);
	}
	
	//Get all the fields of table
     public static function getAllFields($Impexp_JoomlaJs,$exportSeparator)
	 {
	    $csvFileFields="";
	    $joomlaField_name = ImpexpPluginHelper::getJsJoomlaField('#__users');
		foreach ($joomlaField_name as $name){
        	$csvFileFields.='"'.$name.'"'.$exportSeparator;
        }
        if($Impexp_JoomlaJs != 'Joomla'){
        	$isInstalled = ImpexpPluginHelper::jomsocialEnabled();
         	if($isInstalled == false){
		    	   $msg = "PLG_IMPORTEXPORT_YOU_DO_NOT_HAVE_JOMSOCIAL_INSTALLED";
	               self::loadHtmlForWarning($msg);
	               exit();
            }
			$fields = ImpexpJsHelper::getCustomFieldIds();
			foreach($fields as $f)
				$csvFileFields.='"'.$f->name.'"'.$exportSeparator;
			
		   $JSfield_name = ImpexpPluginHelper::getJsJoomlaField('#__community_users');
		   foreach ($JSfield_name as $name){
				if($name=='userid')
					continue;
				$csvFileFields.='"'.$name.'"'.$exportSeparator;
		     }
       }
        $csvFileFields = rtrim($csvFileFields, $exportSeparator);
        return $csvFileFields;
	}
	
	function createZipFile($storagePath)
	{
		$zip = new JArchive();
		$zip_adapter   =  JArchive::getAdapter('zip'); // compression type
		$zip_file_name = $storagePath."exportData.zip";
		$data = JFile::read($storagePath.DS.'exportdata.csv'); 
		$filesToZip[] = array('name' => 'exportdata.csv', 'data' => $data); 
	    if (!$zip_adapter->create($zip_file_name,$filesToZip)) {
           exit('Error creating zip file'); 
        }
		   // Above code will generate exportData.zip
		   //then send the headers to foce download the zip file
		   if(file_exists($zip_file_name)){
				if(!headers_sent()){
			   		header("Content-type: application/zip");
					header("Content-Disposition: attachment; filename= exportData.zip");
					header("Pragma: no-cache");
					header("Expires: 0");
				}
				readfile("$zip_file_name");
				self::deleteFile($storagePath);
				exit;
            }
	}
	 
	
	function refreshExport($end,$Impexp_JoomlaJs,$exportSeparator)
	{  
		 $currentUrl = JURI::getInstance();
		 $name='writeFields';
		 $currentUrl->delVar($name);
	     $currentUrl->setVar('end',$end);
	     $html=$this->getExportHtml($currentUrl);
	     ?>
			  <script>
			       window.onload = function()
			      {
				    setTimeout("redirect()", 2000);
			      }
			
			      function redirect()
			      {
				   window.location = "<?php echo JRoute::_($currentUrl->toString().'&importCSVStage=createCSV&exportDataFrom='.$Impexp_JoomlaJs.'&exportSeparator='.urlencode($exportSeparator), false); ?>"			
				  }
			 </script>
		  <?php 
		  $document = JFactory::getDocument();
		  $document->setBuffer($html, 'component');
		  echo $html;
		  exit;		
    } 
}
	

