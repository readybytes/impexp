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
    
	function createCSV($storagePath)
	{
		$usertype='deprecated';
        $db = JFactory::getDBO();
		$sql = "SELECT COUNT(*) FROM ".$db->nameQuote('#__users');
		$db->setQuery($sql);
		$total_user = $db->loadResult();

		$fields = $this->getCustomFieldIds();
        $fp=fopen($storagePath.'exportdata.csv',"a");
		//fetch limited data from database and store it into a temporary file	
		$start  = JRequest::getVar('end',0);	
		if($start<=$total_user)
		{	
			$users	=	$this->getUserData($start);
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
			        $end=$start+IMPEXP_LIMIT;
			        fclose($fp);                 
				    self::refreshExport($end);
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
			$arrayJUser=array();
		    foreach($joomlaUsers as $joomlaUser)
		    {
		    	$arrayJUser[] = $joomlaUser->id;
		    }
		    
		    $matches = implode(',', $arrayJUser);
		    if(!empty($arrayJUser))
		    {   
		    	$condition=" WHERE ".$db->nameQuote('user_id')." IN ($matches) ";
		    }
		    else 
		    {
		        $condition = "";	
		    } 
		    $sql = " SELECT * FROM ".$db->nameQuote('#__community_fields_values')
				     .$condition." ORDER BY ".$db->nameQuote('user_id')." ASC,".$db->nameQuote('field_id')." ASC";
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
			//jomsocial data
			$db = JFactory::getDBO();
			
			if(!empty($arrayJUser))
		    { 
		    	$condition = " WHERE ".$db->nameQuote('userid')." IN ($matches)";
			}
		    else 
		    {
		        $condition = "";
		    }  
		    $sql = " SELECT * FROM ".$db->nameQuote('#__community_users')
			       .$condition."ORDER BY ".$db->nameQuote('userid')." ASC";
			$db->setQuery($sql); 
		    $JomSocialuser =  $db->loadObjectList('userid');

		   foreach($JomSocialuser as $user)
		   {
		   	    if(!array_key_exists($user->userid, $csvUser)){
					continue;
				}

				$JSfield_name = array('status','points','posted_on','avatar','thumb','invite','params','alias','profile_id','watermark_hash','storage','search_email','friends','groups');
			    
				foreach ($JSfield_name as $name)
				{
		           if($name == 'params')
					{
						if(strrpos($user->$name,',') == true)
						{
					    $csvUser[$user->userid][$name]     = str_ireplace(',','\\n',$str);
						}
						else 
					    $csvUser[$user->userid][$name]     = preg_replace('!\r+!', '\\r', preg_replace('!\n+!', '\\n', $user->$name));
					}
					else 
					{
					$csvUser[$user->userid][$name]     = preg_replace('!\r+!', '\\r', preg_replace('!\n+!', '\\n', $user->$name));
					}
		     } 
		   }
		 
			return $csvUser;
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