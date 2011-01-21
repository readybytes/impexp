<?php
// no direct access
if(!defined('_JEXEC')) die('Restricted access');
jimport( 'joomla.plugin.plugin' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class plgSystemImportExport_csv extends JPlugin
{
	var $_debugMode = 0;
	var $mysess     = null;
	var $storagePath= null;
	function plgSystemimportexport_csv( &$subject, $params )
	{
		$this->mysess = JFactory::getSession();
		$this->storagePath  = JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS;
		parent::__construct( $subject, $params );
	}
	
	function onAfterRoute()
	{
		if(JFactory::getApplication()->isAdmin() == false)
			return true;

		$plugin = JRequest::getVar('plugin',false);
		$task   = JRequest::getVar('task',false);			
		$stage  = JRequest::getVar('importCSVStage','upload');
				
		if($plugin != 'importexportCSV')
			return true;

		if($task=='export'){
			$html = '';
			$users = $this->_getUserData();
			$this->_getDataInCSV($users);
		}		
		if($task=='uploadFile'){
			$html = '';
			if($stage == 'upload')
				$html = $this->_getUploaderHtml();
			else if($stage == 'fieldMapping')
				$html = $this->_getMappingHtml();
			else if($stage == 'importData')
				$html = $this->_importData();
			else if($stage == 'createUser')
				$html = $this->_createUser();
			else if($stage == 'complete'){
				$html="Instead of existing users, All Users are successfully imported. <br />";
				$html.= "Imported users are added in following file:<br />";
				$filename="importuser.csv";
				$html.= "<a href=". JURI::root().DS.'plugins'.DS.'system'.DS.'importexport_csv'.DS.$filename.">Imported Users</a>";
				$html.= "<br />";
				$html.= "Existing users are added in following file:<br />";
				$filename="existuser.csv";
				$html.= "<a href=". JURI::root().DS.'plugins'.DS.'system'.DS.'importexport_csv'.DS.$filename.">Existing Users</a>";
			}
				
						
			$document = JFactory::getDocument();
			$document->setBuffer($html, 'component');
			JFactory::getApplication()->render();
			echo JResponse::toString(JFactory::getApplication()->getCfg('gzip'));
			exit;		
		}
	}
	
	function _getUserData()
	{
		$db = JFactory::getDBO();
		$sql = " SELECT * FROM ".$db->nameQuote('#__users');
		$db->setQuery($sql); 
		$joomlaUsers = $db->loadObjectList('id');

		$sql = " SELECT * FROM ".$db->nameQuote('#__community_fields_values')
			  ." ORDER BY ".$db->nameQuote('user_id')." ASC,".$db->nameQuote('field_id')." ASC";
		$db->setQuery($sql); 
		$jsUserData = $db->loadObjectList();
		
		$userIds = array_keys($joomlaUsers);
		
		$csvUser=array();
		foreach($joomlaUsers as $user){			
			$csvUser[$user->id]['username'] = $user->username;	// first : username
			$csvUser[$user->id]['name'] 	= $user->name;		// second : name
			$csvUser[$user->id]['email'] 	= $user->email;		// third : email
			$csvUser[$user->id]['password'] = $user->password;	// first : password
		}
		
		foreach($jsUserData as $fields){
			if(!array_key_exists($fields->user_id, $csvUser))
				continue;
				
			$csvUser[$fields->user_id][$fields->field_id] =  preg_replace('!\r+!', '\\r', preg_replace('!\n+!', '\\n', $fields->value));
		}
		return $csvUser;	
	}
	
	function _getCustomFieldIds()
	{
		$db	=& JFactory::getDBO();
		$query  = "  SELECT * "
				  ." FROM ".$db->nameQuote('#__community_fields')
				  ." WHERE ".$db->nameQuote('type') ." <> ".$db->Quote('group')
				  ." ORDER BY ".$db->nameQuote('ordering');
		$db->setQuery($query);		  
		return $db->loadObjectList('id');	
		
	}
	
	function _getDataInCSV($users)
	{
		$fields = $this->_getCustomFieldIds();
		ob_start();
		
		header('Content-type: application/csv');
		header("Content-type: application/octet-stream");
    	header("Content-Disposition: attachment; filename=user.csv");
   
		echo '"'.JText::_('username');
		echo '","'.JText::_('name');
		echo '","'.JText::_('email');
		echo '","'.JText::_('password');
		//echo ",".XiusText::_('password');
			
		foreach($fields as $f)
			echo '","'.JText::_($f->name);		
				
		foreach($users as $id => $data){
			// do not export admin user	
			if($data['username'] == 'admin')
				continue;
						
			echo "\n".'"'.$data['username'].'","'.$data['name'].'","'.$data['email'].'","'.$data['password'];
			foreach($fields as $f){
				if(array_key_exists($f->id, $data))
					echo '","'.nl2br($data[$f->id]);
				else 
					echo '", "';
			}
			echo '"';
		}
		exit;
		$content = ob_get_contents();
		ob_clean();
	}
	
	function _createUser()
	{
		require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
		require_once(JPATH_SITE.DS.'components'.DS.'com_community'.DS.'models'.DS.'profile.php');						
		
		$fieldMapping = $this->mysess->get('fieldMapping', array(), 'importCSV');
		$fileIndex    = $this->mysess->get('fileIndex', array(), 'importCSV');
		
		// if session expired then what
		if(empty($fieldMapping) || empty($fileIndex)){
			$currentUrl = JURI::getInstance();
			$currentUrl->setVar('importCSVStage', 'complete');
			JFactory::getapplication()->redirect(JRoute::_($currentUrl->toString(), false));
		}
		//error_reporting(E_ALL ^ E_NOTICE); 
		$index 		  = array_shift($fileIndex);
		$this->mysess->set('fileIndex', $fileIndex, 'importCSV');
		$html='';
		$file = fopen($this->storagePath.'import.csv', "r");
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
			if($this->_checkUsernameinJoomla($userValues[$useroffset],$userValues[$emailoffset])){
				$existuser[$count]['username']=$userValues[$useroffset];
				$existuser[$count]['email']=$userValues[$emailoffset];
				//$existuser[$count]['password']=$userValues[$fieldMapping['joomla']['password']];
				$count++;
				continue;
			}
			
			$importuser[$icount]['username']=$userValues[$useroffset];
			$importuser[$icount]['email']=$userValues[$emailoffset];
			//$existuser[$count]['password']=$userValues[$fieldMapping['joomla']['password']];
			$icount++;			
			$newUserId = $this->_storeJoomlaUser($userValues, $fieldMapping['joomla']);

			// TODO : what if enable to save usrs
			if(!$newUserId) continue;
			$cUser  = $this->_storeCommunityUser($newUserId, $userValues,$fieldMapping['jsfield']);
			$this->_storeCustomFields($newUserId, $userValues, $fieldMapping['custom']);
		}
		
		$isEOF = feof($file);
		fclose($file);	
		//Add existed user in file.
		$this->_getExistUserInCSV($existuser,'existuser.csv');
		//Add imported user in file
		$this->_getExistUserInCSV($importuser,'importuser.csv');
		$currentUrl = JURI::getInstance();		
		JFactory::getapplication()->redirect(JRoute::_($currentUrl->toString(), false));		
	}
	
	function _getExistUserInCSV($users,$filename)
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
	
	function _storeCustomFields($userid, $userValues, $customFieldMapping)
	{
		$cModel = CFactory::getModel('Profile');
		$data =array();
		foreach($customFieldMapping as $key => $value)
			$data[$key] = JString::str_ireplace("\\r", "\r", JString::str_ireplace("\\n", "\n", $userValues[$value]));
		
		return $cModel->saveProfile($userid, $data);		
	}
	
	function _storeCommunityUser($userid, $userValues,$jsFieldMapping)
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
	
	function _storeJoomlaUser($userValues, $joomlaFieldMapping)
	{
		$user 		= clone(JFactory::getUser());
		$authorize	= JFactory::getACL();
		$newUsertype = 'Registered';
		//error_reporting(E_ALL ^ E_NOTICE); 
		//Update user values
		$user->set('id', 0);
		$user->set('usertype', $newUsertype);
		$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));		
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
						'block'		=> 0
					 );
					 
		// Bind the post array to the user object
		if (!$user->bind($data)) {
			JError::raiseError( 500, $user->getError());
		}	
			
		jimport('joomla.user.helper');
			$user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );
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
		if($this->mysess->get('passwordFormat', 'joomla', 'importCSV') == 'joomla'){
			$db = JFactory::getDBO();
			$sql = " UPDATE ".$db->nameQuote('#__users')
				   ." SET ".$db->nameQuote('password') ." = ".$db->Quote($userValues[$joomlaFieldMapping['password']])
				   ." WHERE ".$db->nameQuote('id') ." = ".$db->Quote($user->id);
			$db->setQuery($sql);
			$db->query();			
		}
		return $user->id;
	}
	
	function _importData()
	{
		$post = JRequest::get('post');
		// check for duplicate values 
		// there must be one to one mapping
		$fieldMapping['joomla']  = $this->_getFieldMapping($post,'joomla');
		$fieldMapping['jsfield'] = $this->_getFieldMapping($post,'jsfield');
		$fieldMapping['custom']  = $this->_getFieldMapping($post,'custom');
		
		// save fields mapping in session		
		if($this->mysess->has('fieldMapping', 'importCSV'))
			 $this->mysess->clear('fieldMapping', 'importCSV');
			 
		$this->mysess->set('fieldMapping', $fieldMapping, 'importCSV');
		
		//Deleting csv files
		$this->_deleteCSV('existuser.csv','Username and E-mails which are already exist.');
		$this->_deleteCSV('importuser.csv','Username and E-mails which are imported.');
		
		$currentUrl = JURI::getInstance()->toString();
		JFactory::getapplication()->redirect(JRoute::_($currentUrl.'&importCSVStage=createUser', false));
	}
	
	function _deleteCSV($filename,$content)
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
	
	function _getFieldMapping($post, $suffix)
	{
		$fields = array();
		foreach($post as $key => $value){
			// none should not be added so check value of $value
			if(JString::stristr($value, $suffix) && $value)
				$fields[JString::str_ireplace($suffix.'_', '', $value)] = JString::str_ireplace('csvField', '', $key); 
		}
		return $fields;
	}
	
	function _getMappingHtml()
	{
		$fileCSV 	= JRequest::getVar( 'fileUploaded' , '' , 'FILES' , 'array' );
		if(!isset($fileCSV['tmp_name']) || empty($fileCSV['tmp_name'])){
			return $this->_getUploaderHtml();
		}
		
		// set password format value in session
		$this->mysess->set('passwordFormat', JRequest::getVar('passwordFormat','joomla'), 'importCSV');
		
		if(JFile::exists($this->storagePath.'import.csv'))
			JFile::delete($this->storagePath.'import.csv'); 
			
		JFile::copy($fileCSV['tmp_name'], $this->storagePath.'import.csv');
		$file 	 = fopen($this->storagePath.'import.csv', "r");
		$columns = explode(',', array_shift(fgetcsv($file, 1, "\n")));
		$this->_setIndexingInSession($file);
		fclose($file);
		
		// get all options of fields
		$optionHtml  = '';
		$optionHtml .= $this->_getJoomlaFieldOptions();
		$optionHtml .= $this->_getJSFieldOptions();
		$optionHtml .= $this->_getCustomFieldOptions();
		
		$index = 0;
		$html  = '';
		$currentUrl = JURI::getInstance()->toString();
		$this->_addMappingScript($columns);
		ob_start();
		?>
		<form action="<?php echo JRoute::_($currentUrl, false); ?>" method="post" name="adminForm" id="adminForm" >
		<span><?php echo JText::_('Please map the fields of CSV files to Your Joomla setup.'); ?></span>
		<ol>
			<li>Three fields must exists Username, Password, Email for Joomla User Table Fields.</li>
			<li>There must be one to one mapping, one field must be selected for one field of your joomla setup.</li>						
			<li>Date fields in CSV file must be in SQL date formate.</li>
		</ol>			
		<br />
		<?php  
		foreach($columns as $c){
			$c = JString::str_ireplace('"', '', $c);		
			?>
			<div>				
				<div style="width:20%; float:left"><span><?php echo $c;?></span></div>
				<div style="width:70%; float:right">
					<select id="csvField<?php echo $index;?>" name="csvField<?php echo $index;?>">
						<option value=0><?php echo JText::_('None');?></option>
						<?php echo $optionHtml;
						$index++;?>
					</select>
				</div>
				<div class='clr'></div>
			</div>			
			<br />
			<?php 
		}
		?>
		<input type="hidden" name="importCSVStage" value="importData" />
		<input type="submit" value="Import Data" onclick="return importMappingCheck();" /></input>		
		</form><?php 
		$content = ob_get_contents();
		ob_clean();
		return $content;
	}
	
	function _addMappingScript($coulumn)
	{
		$index=0;
		ob_start();
		?>
			function importMappingCheck(){
				var element;
				var count=0;
				var str;
				var username='false';
				var pass='false';
				var email='false';
				while(1){
				 element= document.getElementById('csvField'+count);
				 if(element==null)
				 	break;
				 str = element.value.toLowerCase();
				 if(str=='joomla_username')
				 	username='true';
				 if(str=='joomla_email')
				 	email='true';
				 if(str=='joomla_password')
				 	pass='true';
				 count=count+1;
				}
				if(username!='true' || email!='true' || pass!='true'){				
					alert ('Username, Password or Email field is not map');
					return false;
				}
				return true;
							
			}
		<?php 
		$content = ob_get_contents();
		ob_clean();
		
		$document = JFactory::getDocument()->addScriptDeclaration($content);
		return true;
	}
	
	function _setIndexingInSession(&$file)
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
		
		if($this->mysess->has('fileIndex', 'importCSV'))
			 $this->mysess->clear('fileIndex', 'importCSV');
			 
		$this->mysess->set('fileIndex', $fileIndex, 'importCSV');
		return true;
	}
	
	function _getJoomlaFieldOptions()
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
	
	function _getJSFieldOptions()
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
	
	function _getCustomFieldOptions()
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
	
	function _getUploaderHtml()
	{
		$currentUrl = JURI::getInstance()->toString();
		$this->_addUploaderScript();
		ob_start();
		?>
		<form enctype="multipart/form-data"  action="<?php echo JRoute::_($currentUrl); ?>" method="post" name="adminForm" id="adminForm" >
		<span><?php echo JText::_('Please upload the CSV File'); ?></span><br />
		<input type="file" id="fileUploaded" name="fileUploaded" title="Please Upload your CSV file" />
		<br /><br />
		<span><?php echo JText::_('You have Password in format of ');?> : </span><br />
		<select name="passwordFormat" >
			<option value="joomla">Joomla Encrypted</option>
			<option value="plain">Plain</option>			
		</select>
		<input type="hidden" name="importCSVStage" value="fieldMapping" />
		<br /><br />
		<input type="submit" name="btnUpload" value="Upload and Parse file" onclick="return importCSVFormCheck();" />
		</form>
		<?php 
		$html = ob_get_contents();
		ob_clean();
		return $html;
	}
	
	function _addUploaderScript()
	{
		ob_start();
		?>
		function importCSVFormCheck(){
			var file = document.getElementById('fileUploaded');
			var str = file.value.toLowerCase();
			var length = str.length;			
			if(str.slice(length-3, length) != 'csv'){
				alert('Please check the file Uploaded. It must be a CSV file.');
				return false;
			}			
			return true;
		}
		<?php 
		$content = ob_get_contents();
		ob_clean();
		
		$document = JFactory::getDocument()->addScriptDeclaration($content);
		return true;
	}
	
	function _checkUsernameinJoomla($username,$email){
		$db = & JFactory::getDBO();

		$query = 'SELECT id FROM #__users WHERE username = ' . $db->Quote( $username ).
				' OR email = ' . $db->Quote( $email );
		$db->setQuery($query, 0, 1);
		return $db->loadResult();
	}
		
}




