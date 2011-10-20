<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );

class ImpexpPluginExport 
{
    function getExportHtml()
    {
    	ob_start();
		require_once(dirname(__FILE__) . DS .'tmpl'. DS .'download.php');
		$html = ob_get_contents();
		ob_clean();
		return $html;
    }
    
	function createCSV($storagePath,$mysess)
	{
		$usertype='deprecated';
        $db = JFactory::getDBO();
		$sql = "SELECT COUNT(*) FROM ".$db->nameQuote('#__users');
		$db->setQuery($sql);
		$total_user = $db->loadResult();

		$fields = $this->getCustomFieldIds();
		
		//if file is not writable 
		if (!is_writable($storagePath.'exportdata.csv'))
		{ 
		  echo JText::_("PERMISSION_DENIED");
		  exit();
		}
		
		//open a file which contain the data fetched from the database
		$fp=fopen($storagePath.'exportdata.csv',"a");
		//get the starting position from where to start processing 	
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
			$limit = $mysess->get('limit',EXP_LIMIT);
			$users	=	$this->getUserData($start,$limit,$mysess);
			foreach($users as $id => $data)
			{
				// do not export admin user
				if($data['usertype'] != $usertype)
				{
					$csvdata="\n".'"'.$data['username'].'","'.$data['name'].'","'.$data['email'].'","'.$data['password'].'","'.$data['usertype'];
					foreach($fields as $f)
					{
						if(array_key_exists($f->id, $data))
							$csvdata.='","'.$data[$f->id];
						
						else 
							$csvdata.= '","';
					}
					//export js fields 
					$JSfield_name = array('status','points','posted_on','avatar','thumb','invite','params','alias','profile_id','watermark_hash','storage','search_email','friends','groups');
					foreach ($JSfield_name as $name)
					{
					    if(!empty($data[$name])){
					    	$csvdata.='","'.$data[$name];
						}	
					    else{ 
					    	$csvdata.='","';
						}
					}
					$csvdata.= '"';
                    fwrite($fp,$csvdata);
				}
			}
			
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
			$startTime = JProfiler::getmicrotime();
			$csvUser = array();
			$db = JFactory::getDBO();
			
		    //select desired fields from #__users and #__community_users tables
			$sql = " SELECT juser.`id`, juser.`username`, juser.`name`,juser.`email`, juser.`password`, juser.`usertype`,"
			        ." cuser.`status`,cuser.`points`,cuser.`posted_on`,cuser.`avatar`,cuser.`thumb`,cuser.`invite`,cuser.`params`,cuser.`alias`,cuser.`profile_id`,cuser.`watermark_hash`,cuser.`storage`,cuser.`search_email`,cuser.`friends`,cuser.`groups`"
			        ." FROM ".$db->nameQuote('#__users')."as juser"
                    ." LEFT JOIN `#__community_users` AS cuser ON (juser.`id` = cuser.`userid`)"
					." WHERE juser.".$db->nameQuote('block'). "=". "0"
					." LIMIT ".$start.",".$limit;
					
			$db->setQuery($sql); 
			$joomlaUsers = $db->loadAssocList('id');
			$userIds = array_keys($joomlaUsers);
		    
			//start creating csv for jomsocial and joomla users' fields
		    foreach ($joomlaUsers as $user)
		    {
		    	foreach ($user as $name => $value)
		    	{ 
		    		$str = $value;
		    		$csvUser[$user['id']][$name] = preg_replace('!\r+!', '\\r', preg_replace('!\n+!', '\\n', $str));
		    		if($name == 'params' && strrpos($value,',') == true)
		    		{
		    		  $csvUser[$user['id']][$name] = str_ireplace(',','\\n',$str);
		    		} 
		    	    
		        }
		    }
			
			//process community_field_values table
			$condition = "";
		    if(count($userIds)>0){
		    	$matches = implode(',', $userIds );   
		    	$condition=" WHERE ".$db->nameQuote('user_id')." IN ($matches) ";
		    }

		    $sql = " SELECT * FROM ".$db->nameQuote('#__community_fields_values')
				   .$condition
				   ." ORDER BY ".$db->nameQuote('user_id')." ASC,".$db->nameQuote('field_id')." ASC";
			$db->setQuery($sql); 
			$jsUserData = $db->loadObjectList();
			foreach($jsUserData as $fields){
				if(!array_key_exists($fields->user_id, $csvUser))
					continue;
					
				$csvUser[$fields->user_id][$fields->field_id] =  preg_replace('!\r+!', '\\r', preg_replace('!\n+!', '\\n', $fields->value));
			}

          //set final export limit if not set
		   if(!$mysess->has('isSet')){
		     $limit = self::setFinalLimit($startTime);
		
		     $mysess->set('limit',$limit);
		     $mysess->set('isSet',true);
		   }
		 
			return $csvUser;
		}
		
	 //decide final export limit to be used 
	 function setFinalLimit($startTime)
	 {  
	 	$value = new ImpexpPluginImport();
	 	$space = (JProfiler::getMemory()); //consumed space 
	    $limit= (int)(($value->memory_limit/$space)*EXP_LIMIT*0.80); //80% of next possible limit
	 	return $limit;
	 }
	
	 function setDataInCSV($storagePath)
	 {
			$fields = $this->getCustomFieldIds();
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
		    
		   $JSfield_name = array('status','points','posted_on','avatar','thumb','invite','params','alias','profile_id','watermark_hash','storage','search_email','friends','groups');
			foreach ($JSfield_name as $name)
		        echo '","'.JText::_($name);

			echo file_get_contents($storagePath.'exportdata.csv');
			//delete exportdata.csv file
			JFile::delete($storagePath.'exportdata.csv');
			exit;
			
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
				   window.location = "<?php echo JRoute::_($currentUrl->toString()); ?>"			
				  }
			 </script>
		  <?php 
		  $document = JFactory::getDocument();
		  $document->setBuffer($html,'component');
          //JFactory::getApplication()->render();
      	  echo $html;
		  //echo JResponse::toString(JFactory::getApplication()->getCfg('gzip'));
		  exit;		     
    }
}
