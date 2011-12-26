<?php
define('TESTMODE',1);
class ImportExportTest extends XiUnitTestCase
{
	function getSqlPath()
	{
		return dirname(__FILE__).'/sql/'.__CLASS__;
	}
	
	function includefile()
	{
		require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'importexport_csv.php');
	}
	
	function testsetIndexingInSession()
	{	
		$this->includefile();
		$mysess = JFactory::getSession();
		$storagePath = JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'importexport_csv' .DS;
		$file 	 = fopen($storagePath.'import.csv', "r");
		$indexing = ImpexpPluginImport::setIndexingInSession($file,$mysess);
		$this->assertTrue($indexing);
	}
	
	function testImportWorking()
	{	
		$testmode=1;
		$this->includefile();
		
		//check whether deleteCSV deleting the contents or not		
		JFile::copy(JPATH_ROOT.DS.'test'.DS.'test'.DS.'admin'.DS.'user.csv',JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'importexport_csv'.DS. 'importexport_csv'.DS.'existuser.csv');		
		JFile::copy(JPATH_ROOT.DS.'test'.DS.'test'.DS.'admin'.DS.'user.csv',JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'importexport_csv'.DS. 'importexport_csv'.DS.'importuser.csv');
		$mysess = JFactory::getSession();
		$import = new ImpexpPluginImport();
		$this->assertTrue($import->importData($mysess));
	}
	
	function testgetFieldMapping()
	{
		$this->includefile();
		//test joomla field mapping
		$post = array ( 'csvField0' => 'joomla_username', 'csvField1' => 'joomla_name', 'csvField2' => 'joomla_email', 'csvField3' => 'joomla_password', 'csvField4' => 'joomla_usertype',
						'csvField5' => 'custom_2', 'csvField6' => 'custom_3', 'csvField7' => 'custom_4', 'csvField8' => 'custom_5', 'csvField9' => 'custom_7', 'csvField10' => 'custom_8',
						 'csvField11' => 'custom_9', 'csvField12' => 'custom_10', 'csvField13' => 'custom_11', 'csvField14' => 'custom_12', 'csvField15' => 'custom_13', 'csvField16' => 'custom_15', 'csvField17' => 'custom_16',
		                 'csvField18' => 'jsfield_userid', 'csvField19' => 'jsfield_status','csvField20' => 'jsfield_status_access','csvField21' => 'jsfield_points','csvField22' => 'jsfield_posted_on','csvField23' => 'jsfield_avatar','csvField24' => 'jsfield_thumb',
						 'csvField25' => 'jsfield_invite','csvField26' => 'jsfield_params','csvField27' => 'jsfield_alias','csvField28' => 'jsfield_latitude','csvField29' => 'jsfield_longitude','csvField30' => 'jsfield_profile_id','csvField31' => 'jsfield_watermark_hash',
						 'csvField32' => 'jsfield_storage','csvField33' => 'jsfield_search_email','csvField34' => 'jsfield_friends','csvField35' => 'jsfield_groups', 'importCSVStage' => 'importData' );
		$mapping = ImpexpPluginImport::getFieldMapping($post,'joomla');
		$joomlafield = array ( 'username' => '0', 'name' => '1', 'email' => '2', 'password' => '3', 'usertype' => '4' );
		$this->assertEquals($joomlafield,$mapping);	

		//test jsfields mapping
		$mapping = ImpexpPluginImport::getFieldMapping($post,'jsfield');
		$custom = array ('userid' => '18','status'=>'19','status_access' => '20','points'=>'21','posted_on' =>'22','avatar' => '23','thumb' => '24','invite' => '25','params' => '26','alias' => '27','latitude' => '28','longitude' => '29','profile_id' =>'30','watermark_hash' => '31','storage' => '32',
		                 'search_email' => '33','friends'=>'34','groups'=>'35' );
		$this->assertEquals($custom,$mapping);
		
		//test jscustom fields mapping 
		$mapping = ImpexpPluginImport::getFieldMapping($post,'custom');
		$custom = array ( 2 => '5', 3 => '6', 4 => '7', 5 => '8', 7 => '9', 8 => '10', 9 => '11', 10 => '12', 11 => '13', 12 => '14', 13 => '15', 15 => '16', 16 => '17' );
		$this->assertEquals($custom,$mapping);
	}		
	
	function testgetUserData()
	{
		$this->includefile();

		//when there is no data in users table
		
		$csvData = array();
		$mysess = JFactory::getSession();
		$userData = ImpexpPluginExport::getUserData(0,0,$mysess);
		$this->assertEquals($csvData, $userData);

		//when there is limited data in database
		$this->_DBO->loadSql($this->getSqlPath().DS.'testgetUserDataValue.start.sql');
		$userData = ImpexpPluginExport::getUserData(0,50,$mysess);		
		$csvData = array (43 => array ('id'=>'43', 'username' => 'John', 'name' => 'john', 'email' => 'john@gmail.com', 'password' => 'password', 'usertype' => '5', 'status' => 'hieee...:) ', 'points' => '5', 'posted_on' => '0000-00-00 00:00:00', 'avatar' => 'pic1.png', 'thumb' => 'pic1.png', 'invite' => '5', 'params' => '{"notifyEmailSystem":1\n"privacyProfileView":0\n"privacyPhotoView":0\n"privacyFriendsView":0\n"privacyGroupsView":""\n"privacyVideoView":0\n"notifyEmailMessage":1\n"notifyEmailApps":1\n"notifyWallComment":0}', 'alias' => 'abcz', 'profile_id' => '2', 'watermark_hash' => 'pic.png', 'storage' => 'file', 'search_email' => '1', 'friends' => '5', 'groups' => '5', '2' => 'male', '3' => '1988-11-21 23:59:59', '4' => 'hello', '6' => '8080808080', '7' => '014422240208', '8' => 'c-507,Ashok nagar ', '9' => 'Maharastra', '10' => 'Mumbai', '11' => 'India', '12' => 'www.joomlaxi.com', '14' => 'My College', '15' => '2011'),                                                    
					  	  44 => array ('id'=>'44', 'username' => 'kelvin', 'name' => 'kelvin', 'email' => 'kelvin@gmail.com', 'password' => 'password', 'usertype' => '6', 'status' => 'hieee...:) ', 'points' => '5', 'posted_on' => '0000-00-00 00:00:00', 'avatar' => 'pic1.png', 'thumb' => 'pic1.png', 'invite' => '5', 'params' => '{"notifyEmailSystem":1\n"privacyProfileView":0\n"privacyPhotoView":0\n"privacyFriendsView":0\n"privacyGroupsView":""\n"privacyVideoView":0\n"notifyEmailMessage":1\n"notifyEmailApps":1\n"notifyWallComment":0}', 'alias' => 'abcz', 'profile_id' => '2', 'watermark_hash' => 'pic.png', 'storage' => 'file', 'search_email' => '1', 'friends' => '5', 'groups' => '5', '2' => 'male', '3' => '1991-11-20 23:59:59', '4' => 'hello', '6' => '8088808080', '7' => '014422240208', '8' => 'D-20,azad nagar ', '9' => 'Rajasthan', '10' => 'bhilwara', '11' => 'India', '12' => 'www.xyz.com', '14' => 'My clg', '15' => '2011'),
					      45 => array ('id'=>'45', 'username' => 'kenny', 'name' => 'kenny', 'email' => 'kenny@gmail.com', 'password' => 'password','usertype' => '6', 'status' => 'hieee...:) ', 'points' => '5', 'posted_on' => '0000-00-00 00:00:00', 'avatar' => 'pic1.png', 'thumb' => 'pic1.png', 'invite' => '5', 'params' => '{"notifyEmailSystem":1\n"privacyProfileView":0\n"privacyPhotoView":0\n"privacyFriendsView":0\n"privacyGroupsView":""\n"privacyVideoView":0\n"notifyEmailMessage":1\n"notifyEmailApps":1\n"notifyWallComment":0}', 'alias' => 'abcz', 'profile_id' => '2', 'watermark_hash' => 'pic.png', 'storage' => 'file', 'search_email' => '1', 'friends' => '5', 'groups' => '5', '2' => 'Female', '3' => '1989-11-19 23:59:59', '4' => 'hello', '6' => '9088808080', '7' => '014422240208', '8' => 'G-421,Gandhi nagar ', '9' => 'Rajasthan', '10' => 'udaipur', '11' => 'India', '12' => 'www.abcd.com', '14' => 'College name', '15' => '2011'));
	    $this->assertEquals($csvData, $userData);
		$this->_DBO->loadSql($this->getSqlPath().DS.'testgetUserDataValue.end.sql');
		
	}
	
	function xtestsetDataInCSV()
	{
		$this->includefile();
		$storagePath = JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS;
		$setData = new ImpexpPluginExport();
		$setData->setDataInCSV($storagePath);
		$this->assertTrue(JFile::exists());
		
//		JFile::copy(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS.'exportdata.csv',JPATH_ROOT .DS. 'test' .DS. 'test' .DS. 'unit' .DS. 'export.csv');
//		$storagePath = JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS;
//		$setData = new ImpexpPluginExport();
//		$setData->setDataInCSV($storagePath);
//		$this->assertEquals(JPATH_ROOT .DS. 'test' .DS. 'test' .DS. 'unit' .DS. 'export.csv','home'.DS.'bhavya'.DS.'Downloads'.DS.'user.csv');
	}
	
	function testgetCustomFieldIds()
	{
		$this->includefile();
		$field = array();
		$customfield = ImpexpPluginExport::getCustomFieldIds();
		$this->assertEquals($field,$customfield);

		$this->_DBO->loadSql($this->getSqlPath().DS.'testgetCustomFieldId.start.sql');
		$obj = new stdClass ();
          	$obj->id = 2;
            $obj->type = 'select';
            $obj->ordering = 2;
            $obj->published = 1;
            $obj->min = 10;
            $obj->max = 100;
            $obj->name = 'Gender';
            $obj->tips = 'Select gender';
            $obj->visible = 1;
            $obj->required = 1;
            $obj->searchable = 1;
            $obj->registration = 1;
            $obj->options = "Male\nFemale";
            $obj->fieldcode = 'FIELD_GENDER';
            $obj->params = '';
            
        $field = array(2 => $obj);
		$customfield = ImpexpPluginExport::getCustomFieldIds();
		$this->assertEquals($field,$customfield);
	}
	
	function testcreateCSV()
	{
		$this->includefile();
		$storagePath = JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'importexport_csv' .DS;
		$createCSV = new ImpexpPluginExport();
		$mysess = JFactory::getSession();
		$this->assertTrue($createCSV->createCSV($storagePath,$mysess));
	}
	
 function testdateformats()
  {
  	 $this->assertEquals(date("Y-m-d H:i:s",strtotime("29-07-1989 ")),"1989-07-29 00:00:00");
  	 $this->assertEquals(date("Y-m-d H:i:s",strtotime("12/30/1989 23:59:59 ")),"1989-12-30 23:59:59");
  	 $this->assertEquals(date("Y-m-d H:i:s",strtotime("1988/09/22 ")),"1988-09-22 00:00:00");
  	 $this->assertEquals(date("Y-m-d H:i:s",strtotime("20.07.1997 ")),"1997-07-20 00:00:00");
  }
}