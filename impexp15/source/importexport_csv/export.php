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

class ImpexpPluginExport 
{ 
    function getExportHtml()
    {
    	ob_start();
		require_once( dirname(__FILE__).DS. 'tmpl' .DS. 'download.php');
		$html = ob_get_contents();
		ob_clean();
		return $html;
    }
    
	function createCSV($storagePath,$mysess)
	{

		$db = JFactory::getDBO();
		$sql = "SELECT COUNT(*) FROM ".$db->nameQuote('#__users')
			   ."WHERE ".$db->nameQuote('block'). "=". "0"." AND ".$db->nameQuote('usertype')
			   ."NOT IN ('Administrator','Super Administrator','deprecated','Super Users')";
		$db->setQuery($sql);
		$total_user = $db->loadResult();
        $filePath   = $storagePath.'exportdata.csv';
	    //if existing file is not writable 
		if ( file_exists($filePath) &&
		     !is_writable($filePath)){
		  echo JText::_("PLG_IMPORTEXPORT_CSV_PERMISSION_DENIED");
		  exit();
		}
		
		//open a file which contain the data fetched from the database
		$fp     = fopen($filePath,"a");
		$fields = $this->getCustomFieldIds();

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
			$users	  =	$this->getUserData($start,$limit,$mysess);
			$finalCsv = self::setDataForCsv($users);
		    foreach ($finalCsv as $userid=>$result)
			 {
			  $result = rtrim($result, ',');
			  fwrite($fp,$result);
			 }
			//for testing purpose
		    if(defined('TESTMODE'))
	    	 {
	    		return true;
	    	 }
	        $end=$start+$limit;
			fclose($fp);
		    self::refreshExport($end);
	    }
		fclose($fp);
		$this->setDataInCSV($storagePath);
	}
	
	function getUserData($start,$limit,$mysess)
	{
		$startTime   = JProfiler::getmicrotime();
		$csvUser     = array();
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
		$csvJoomlaUser = self::storeJsJoomlaUser('joomla',$joomlaUsers,$csvUser);
		$userIds = array_keys($joomlaUsers);
	    
		//for jomsocial custom fields 
		$csvComFieldJoomlaUser = self::storeComFieldValues('cFieldValues',$csvJoomlaUser,$userIds);
		// storing userids in temperory file
		$fp = fopen(IMPEXP_TEMP_FILE_PATH, 'w');
        fwrite($fp, serialize($userIds));
        fclose($fp);
        
		//for jomsocial users' fields
		$jsUsers 	 = self::getJsUser($userIds);
	    $completeCsv = self::storeJsJoomlaUser('jomsocial',$jsUsers,$csvComFieldJoomlaUser);
	 
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
		 $sql = "SELECT * FROM ".$db->nameQuote('#__users')
		  	   ."WHERE ".$db->nameQuote('block'). "="."0"." AND ".$db->nameQuote('usertype')
			   ."NOT IN ('Administrator','Super Administrator','deprecated','Super Users') LIMIT ".$start.",".$limit;
		 $db->setQuery($sql);
		 $joomlaUserData = $db->loadAssocList('id');
		 return $joomlaUserData;
	}

	/**
	 * Select informations from 'community_users' table,store it in $jsUserData variable
	 * @param $userIds-store userid
	 */
   function getJsUser($userIds)
   { 
	   	 $db = JFactory::getDBO();
	     $condition = "";
		 if(count($userIds)>0){
				$matches   = implode(',', $userIds );   
				$condition = " WHERE ".$db->nameQuote('userid')." IN ($matches) ";
		 }
		 $sql= "SELECT * FROM ".$db->nameQuote('#__community_users')
		       .$condition." ORDER BY ".$db->nameQuote('userid');
	     $db->setQuery($sql);
	     $jsUserData = $db->loadAssocList('userid');
	     return $jsUserData;
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
	    		$str = $value;
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
	 * Store 'Community_field_values' table data
	 */
    function storeComFieldValues($userTable,$csvUser,$userIds)
	 {
   	    $csvUser[$userTable] = array();
		$db = JFactory::getDBO();
		$condition = "";
	    if(count($userIds)>0){
	    	$matches   = implode(',', $userIds );   
	    	$condition = " WHERE ".$db->nameQuote('user_id')." IN ($matches) ";
	     }
           
	    $sql = " SELECT * FROM ".$db->nameQuote('#__community_fields_values')
			   .$condition
			   ." ORDER BY ".$db->nameQuote('user_id')." ASC,".$db->nameQuote('field_id')." ASC";
		$db->setQuery($sql); 
		$jsUserData = $db->loadObjectList();

		foreach($jsUserData as $fields){
			if(!array_key_exists($fields->user_id, $csvUser['joomla']))
				continue;
			$csvUser[$userTable][$fields->user_id][$fields->field_id] =  preg_replace('!\r+!', '\\r', preg_replace('!\n+!', '\\n', $fields->value));
   		}
   		
   		//if data in field_values table doesn't exist for some users
		//then add a blank array for those users further processing
   		foreach ($userIds as $userid){
   			if(!array_key_exists($userid,$csvUser[$userTable]))
   				$csvUser[$userTable][$userid] = array();
   		}
		return $csvUser;    
	 }
		
		
	 /**
      *decide final export limit to be used 
      */
	 function setFinalLimit($startTime)
	 {  
	 	$value = new ImpexpPluginImport();
	 	$space = (JProfiler::getMemory()); //consumed space 
	    $limit= (int)(($value->memory_limit/$space)*IMPEXP_EXP_LIMIT*0.80); //80% of next possible limit
	 	return $limit;
	 }

    /**
     *store the data in CSV format 
     */
    function setDataForCsv($users,$userIds=null)
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
		    	if(!empty($users['joomla'][$userId][$name])){
		    		$finalCsv[$userId].='"'.$users['joomla'][$userId][$name].'",';
			 	}	
		     	else{ 
		    	$finalCsv[$userId].='"",';
		    	}
        	}
	  	   $fields = self::getCustomFieldIds();
	  	   //getting community field values's table values
	       foreach($fields as $f){
		        if(array_key_exists($f->id, $users['cFieldValues'][$userId]))
					$finalCsv[$userId].='"'.$users['cFieldValues'][$userId][$f->id].'",';
				else 
					$finalCsv[$userId].= '"",';
		   }
	       //getting community user's table values
		   $JSfield_name = ImpexpPluginHelper::getJsJoomlaField('#__community_users');
	       foreach ($JSfield_name as $name){
	          	if($name=='userid')
	        		continue;
			    if(!empty($users['jomsocial'][$userId][$name]))
			    	$finalCsv[$userId].='"'.$users['jomsocial'][$userId][$name].'",';	
			    else 
			     	$finalCsv[$userId].='"",';
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
	    $csvFileFields="";
		header('Content-type: application/csv');
		header("Content-type: application/octet-stream");
    	header("Content-Disposition: attachment; filename=user.csv");
    	$csvFileFields=self::getAllFields();
        echo JText::_($csvFileFields);
           
		echo file_get_contents($storagePath.'exportdata.csv');
		//delete exportdata.csv file
		JFile::delete($storagePath.'exportdata.csv');
		exit;
	}
	
	//Get all the fields of table
     function getAllFields()
	 {
	    $csvFileFields="";
	    $joomlaField_name = ImpexpPluginHelper::getJsJoomlaField('#__users');
		foreach ($joomlaField_name as $name){
        	$csvFileFields.='"'.$name.'",';
        }
		$fields = self::getCustomFieldIds();
		foreach($fields as $f)
			$csvFileFields.='"'.$f->name.'",';
			
	   $JSfield_name = ImpexpPluginHelper::getJsJoomlaField('#__community_users');
       foreach ($JSfield_name as $name){
    		if($name=='userid')
    			continue;
    		$csvFileFields.='"'.$name.'",';
        }
        $csvFileFields = rtrim($csvFileFields, ',');
        return $csvFileFields;
	}
	
	/**
	 * Get all the custom fields
	 */
	 function getCustomFieldIds()
	 {
		$db	    =  JFactory::getDBO();
		$query  = "  SELECT * "
				  ." FROM ".$db->nameQuote('#__community_fields')
				  ." WHERE ".$db->nameQuote('type') ." <> ".$db->Quote('group')
				  ." ORDER BY ".$db->nameQuote('ordering');
		$db->setQuery($query);		  
		return $db->loadObjectList('id');	
	
	}
	
	function refreshExport($end)
	{  
		 $currentUrl = JURI::getInstance();
	     $currentUrl->setVar('end',$end);
	     $html=self::getExportHtml();
	     ?>
			  <script>
			       window.onload = function()
			      {
				    setTimeout("redirect()", 2000);
			      }
			
			      function redirect()
			      {
				   window.location = "<?php echo JRoute::_($currentUrl->toString(), false); ?>"			
				  }
			 </script>
		  <?php 
		  $document = JFactory::getDocument();
		  $document->setBuffer($html, 'component');
		  echo $html;
		  exit;		     
    } 
}