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
			   ."NOT IN ('Administrator','Super Administrator')";
		$db->setQuery($sql);
		$total_user = $db->loadResult();
        $filePath   = $storagePath.'exportdata.csv';
	    //if existing file is not writable 
		if ( file_exists($filePath) &&
		     !is_writable($filePath)){
		  echo JText::_("PERMISSION_DENIED");
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
			$limit    = $mysess->get('limit',EXP_LIMIT);
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

	function setDataForCsv($users)
	{
        $finalCsv = array();
		foreach($users as $id => $data){
			foreach ($data as $user_id=>$value){
				$finalCsv = self::startCreateCsv($id,$value,$finalCsv,$user_id);
			}
		}
		return $finalCsv;
	}
	
	function getUserData($start,$limit,$mysess)
	{
		$startTime   = JProfiler::getmicrotime();
		$csvUser     = array();
		$joomlaUsers = self::getJoomlaUser($start,$limit);
		if(empty($joomlaUsers)){
			echo JText::_("YOUR_JOOMLA_TABLE_IS_EMPTY");
	        exit();
		}
		//for joomla users' fields
		$csvJoomlaUser = self::storeJsJoomlaUser('joomla',$joomlaUsers,$csvUser,$mysess);
		$userIds = array_keys($joomlaUsers);
	    
		//for jomsocial custom fields 
		$csvComFieldJoomlaUser = self::storeComFieldValues('cFieldValues',$csvJoomlaUser,$userIds);
		
		//for jomsocial users' fields
		$jsUsers 	 = self::getJsUser($userIds);
		$mysess->set('userIds',$userIds);
	    $completeCsv = self::storeJsJoomlaUser('jomsocial',$jsUsers,$csvComFieldJoomlaUser,$mysess);
	 
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
			   ."NOT IN ('Administrator','Super Administrator') LIMIT ".$start.",".$limit;
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
	 function storeJsJoomlaUser($userTable,$jsJoomlaUsers,$csvUser,$mysess)
	 {
		$id='id';
		if($userTable == "jomsocial")
		{
			$id = 'userid';
			//if data in community_users doesn't exist for users
		    //then add a blank array for further processing
			$userIds = $mysess->get('userIds');
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

	    //if data in field_values table doesn't exist for users
		//then add a blank array for further processing
		foreach($userIds as $userid)
	 	{  
		 	$csvUser[$userTable][$userid] = array();
			foreach($jsUserData as $fields){
				if(!array_key_exists($fields->user_id, $csvUser['joomla']))
					continue;
				if($fields->user_id == $userid){
					$csvUser[$userTable][$userid][$fields->field_id] =  preg_replace('!\r+!', '\\r', preg_replace('!\n+!', '\\n', $fields->value));
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
	 	$space = (JProfiler::getMemory()); //consumed space 
	    $limit= (int)(($value->memory_limit/$space)*EXP_LIMIT*0.80); //80% of next possible limit
	 	return $limit;
	 }

  /**
   *store the data in CSV format 
   */
    function startCreateCsv($tableName,$data,&$finalCsv,$key)
    {    
    	if($tableName =='joomla'){
        	$finalCsv[$key]="\n";
       		$joomlaField_name =self::getJsJoomlaField('#__users');
        	foreach ($joomlaField_name as $name){
		    if(!empty($data[$name])){
		    	$finalCsv[$key].='"'.$data[$name].'",';
			 }	
		     else{ 
		    	$finalCsv[$key].='"",';
		     }
           }
	    }
      elseif ($tableName =='cFieldValues') {
  	   $fields = self::getCustomFieldIds();
        foreach($fields as $f){
	        if(array_key_exists($f->id, $data))
				$finalCsv[$key].='"'.$data[$f->id].'",';
			 else 
				$finalCsv[$key].= '"",';
	 	 }
      }
      else{
		 $JSfield_name =self::getJsJoomlaField('#__community_users');
         foreach ($JSfield_name as $name){
          	if($name=='userid')
        	 continue;
		    if(!empty($data[$name])){
		     $finalCsv[$key].='"'.$data[$name].'",';
		    }	
		    else{ 
		     $finalCsv[$key].='"",';
		     }
		 }   
      } 
        return $finalCsv;
      }
 
    /** 
     * Get all the fields from joomla_user amd Community_user table
     * @param $table-Name of table
     */
    function getJsJoomlaField($table)
    {
        $db = JFactory::getDBO();
    	$conf = JFactory::getConfig();
		$database = $conf->getValue('config.db');
        $tableName = $db->replacePrefix($table);
        $sql="SELECT column_name FROM information_schema.columns
                 WHERE table_name = '$tableName'
                 AND table_schema = '$database'";
        $db->setQuery($sql); 
        $joomlaField_name =$db->loadResultArray();
        return $joomlaField_name;
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
	    $joomlaField_name =self::getJsJoomlaField('#__users');
		foreach ($joomlaField_name as $name){
        	$csvFileFields.='"'.$name.'",';
        }
		$fields = self::getCustomFieldIds();
		foreach($fields as $f)
			$csvFileFields.='"'.$f->name.'",';
			
	   $JSfield_name =self::getJsJoomlaField('#__community_users');
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
		  JFactory::getApplication()->render();
		  echo JResponse::toString(JFactory::getApplication()->getCfg('gzip'));
		  exit;		     
    } 
}