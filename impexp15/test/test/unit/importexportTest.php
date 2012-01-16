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
		require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv.php');
	}
	
	function testsetIndexingInSession()
	{	
		$this->includefile();
		$mysess = JFactory::getSession();
		$storagePath = JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS;
		$file 	 = fopen($storagePath.'import.csv', "r");
		$indexing = ImpexpPluginImport::setIndexingInSession($file,$mysess);
		$this->assertTrue($indexing);
	}
	
	function testImportWorking()
	{	
		$testmode=1;
		$this->includefile();
		
		//check whether deleteCSV deleting the contents or not		
		JFile::copy(JPATH_ROOT.DS.'test'.DS.'test'.DS.'admin'.DS.'user.csv',JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'importexport_csv'.DS.'existuser.csv');		
		JFile::copy(JPATH_ROOT.DS.'test'.DS.'test'.DS.'admin'.DS.'user.csv',JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'importexport_csv'.DS.'importuser.csv');
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
						 'csvField32' => 'jsfield_storage','csvField33' => 'jsfield_search_email','csvField34' => 'jsfield_friends','csvField35' => 'jsfield_groups','importCSVStage' => 'importData' );
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
		//$userData = ImpexpPluginExport::getUserData(0,0,$mysess);
		//$this->assertEquals($csvData, $userData);

		//when there is limited data in database
		$this->_DBO->loadSql($this->getSqlPath().DS.'testgetUserDataValue.start.sql');
		$userData = ImpexpPluginExport::getUserData(0,50,$mysess);	
	    $csvData =array('joomla'=>array(64 => array('id'=>'64', 'name' => 'john','username' =>'John','email' => 'john@gmail.com', 'password' => 'password', 'usertype' => 'Publisher','block'=>'0','sendEmail'=>'1','gid'=>'21','registerDate'=>'2011-12-21 08:50:29','lastvisitDate'=>'2011-12-21 08:08:48','activation'=>'a80fceb00c0567c5cf969a12803da007','params'=>'admin_language=\nlanguage=\neditor=\nhelpsite=\ntimezone=0\n'),
	                                    65 => array('id'=>'65', 'name' => 'kelvin','username' => 'kelvin', 'email' => 'kelvin@gmail.com', 'password' => 'password', 'usertype' => 'Manager','block'=>'0','sendEmail'=>'1','gid'=>'23','registerDate'=>'2011-12-21 08:50:29','lastvisitDate'=>'2011-12-21 08:08:48','activation'=>'5f96b9b1ca96de3133761c90f27cd162','params'=>'admin_language=\nlanguage=\neditor=\nhelpsite=\ntimezone=0\n'),
	                                    66 => array('id'=>'66', 'name' => 'kenny','username' => 'kenny',  'email' => 'kenny@gmail.com', 'password' => 'password','usertype' => 'Manager','block'=>'0','sendEmail'=>'1','gid'=>'23','registerDate'=>'2011-12-21 08:50:29','lastvisitDate'=>'2011-12-21 08:08:48','activation'=>'047b247d30b58999854187e35bf61479','params'=>'admin_language=\nlanguage=\neditor=\nhelpsite=\ntimezone=0\n')),                                                        
                  'cFieldValues'=>array(64 => array('2' => 'male', '3' => '1988-11-21 23:59:59', '5' => 'hello', '7' => '8080808080', '8' => '014422240208', '9' => 'c-507,Ashok nagar ', '10' => 'Maharastra', '11' => 'Mumbai', '12' => 'India', '13' => 'www.joomlaxi.com', '15' => 'My College', '16' => '2011'),
	                                    65 => array('2' => 'male', '3' => '1991-11-20 23:59:59', '5' => 'hello', '7' => '8088808080', '8' => '014422240208', '9' => 'D-20,azad nagar ', '10' => 'Rajasthan', '11' => 'bhilwara', '12' => 'India', '13' => 'www.xyz.com', '15' => 'My clg', '16' => '2011'),                
		                                66 => array('2' => 'Female','3' => '1989-11-19 23:59:59', '5' => 'hello', '7' => '9088808080', '8' => '014422240208', '9' => 'G-421,Gandhi nagar ', '10' => 'Rajasthan', '11' => 'udaipur', '12' => 'India', '13' => 'www.abcd.com', '15' => 'College name', '16' => '2011')),
		             'jomsocial'=>array(64 => array('userid'=>'64','status' => 'hieee...:) ','status_access'=>'0','points' => '5', 'posted_on' => '0000-00-00 00:00:00', 'avatar' => 'pic1.png', 'thumb' => 'pic1.png', 'invite' => '5', 'params' => 'notifyEmailSystem=1\nprivacyProfileView=30\nprivacyPhotoView=40\nprivacyFriendsView=30\nprivacyGroupsView=\nprivacyVideoView=0\nnotifyEmailMessage=1\nnotifyEmailApps=0\nnotifyWallComment=0\n','view'=>'0','friendcount'=>'0','alias' => 'abcz','latitude'=>'255','longitude'=>'255', 'profile_id' => '2', 'watermark_hash' => 'pic.png', 'storage' => 'file', 'search_email' => '1', 'friends' => '5', 'groups' => '5'),
                                        65 => array('userid'=>'65','status' => 'hieee...:) ','status_access'=>'0','points' => '5', 'posted_on' => '0000-00-00 00:00:00', 'avatar' => 'pic1.png', 'thumb' => 'pic1.png', 'invite' => '5', 'params' => 'notifyEmailSystem=1\nprivacyProfileView=30\nprivacyPhotoView=40\nprivacyFriendsView=30\nprivacyGroupsView=\nprivacyVideoView=0\nnotifyEmailMessage=1\nnotifyEmailApps=0\nnotifyWallComment=0\n','view'=>'0','friendcount'=>'0','alias' => 'abcz','latitude'=>'255','longitude'=>'255','profile_id' => '2', 'watermark_hash' => 'pic.png', 'storage' => 'file', 'search_email' => '1', 'friends' => '5', 'groups' => '5'),
	                                    66 => array('userid'=>'66','status' => 'hieee...:) ','status_access'=>'0','points' => '5', 'posted_on' => '0000-00-00 00:00:00', 'avatar' => 'pic1.png', 'thumb' => 'pic1.png', 'invite' => '5', 'params' => 'notifyEmailSystem=1\nprivacyProfileView=0\nprivacyPhotoView=0\nprivacyFriendsView=0\nprivacyGroupsView=\nprivacyVideoView=0\nnotifyEmailMessage=1\nnotifyEmailApps=1\nnotifyWallComment=0\n','view'=>'0','friendcount'=>'0','alias' => 'abcz','latitude'=>'255','longitude'=>'255','profile_id' => '2', 'watermark_hash' => 'pic.png', 'storage' => 'file', 'search_email' => '1', 'friends' => '5', 'groups' => '5'))); 
	    $this->assertEquals($csvData, $userData);
		
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
            $obj->options = "Male\nFemale";
            $obj->fieldcode = 'FIELD_GENDER';
            $obj->regshow = 1;
            $obj->registration = 1;
            $obj->params='';
        $field = array(2 => $obj);
		$customfield = ImpexpPluginExport::getCustomFieldIds();
		$this->assertEquals($field,$customfield);
	}
	
	function testcreateCSV()
	{
		$this->includefile();
		$storagePath = JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS;
		$createCSV = new ImpexpPluginExport();
		$mysess = JFactory::getSession();
		$this->assertTrue($createCSV->createCSV($storagePath,$mysess));
	}
	
	function testremoveQuotes()
	{
		$storagePath  = JPATH_ROOT.DS.'test'.DS.'test'.DS.'unit'.DS.'doubleQuote.csv';	
		$file         = fopen($storagePath,"r");
		$columns      = fgetcsv($file, 0, "\n");
		$columns 	  = ImpexpPluginImport::removeQuotes($columns, ',"');
		$checkColumn  = array(0=>"John",1=>'john',2=>'john@gmail.com',3=>'password',4=>'Publisher',5=>'',6=>'',7=>'male',8=>'1988-11-21 23:59:59',9=>'hello',10=>'8080808080',11=>'014422240208',12=>'c-507,Ashok nagar ',13=>'Maharastra',14=>'Mumbai',15=>'India',16=>'www.joomlaxi.com',17=>'My College',18=>'2011',19=>'hieee...:)',20=>'5',21=>'0000-00-00 00:00:00',22=>'pic1.png',23=>'pic1.png',24=>'5',25=>'',26=>'abcz',27=>'2',28=>'pic.png',29=>'file',30=>'1',31=>'5',32=>'5');	
        $this->assertEquals($columns,$checkColumn);
        
        $storagePaths  = JPATH_ROOT.DS.'test'.DS.'test'.DS.'unit'.DS.'comma.csv';	
		$fp            = fopen($storagePaths,"r");
		$columns       = fgetcsv($fp , 0, "\n");
		$columns     = ImpexpPluginImport::removeQuotes($columns, ',');
		$checkColumn = array(0=>"John",1=>'john',2=>'john@gmail.com',3=>'password',4=>'Publisher',5=>'',6=>'',7=>'male',8=>'1988-11-21 23:59:59',9=>'hello',10=>'8080808080',11=>'014422240208',12=>'c-507 Ashok nagar',13=>'Maharastra',14=>'Mumbai',15=>'India',16=>'www.joomlaxi.com',17=>'My College',18=>'2011',19=>'hieee...:)',20=>'5',21=>'0000-00-00 00:00:00',22=>'pic1.png',23=>'pic1.png',24=>'5',25=>'',26=>'abcz',27=>'2',28=>'pic.png',29=>'file',30=>'1',31=>'5',32=>'5');$this->assertEquals($columns,$checkColumn);
        $this->assertEquals($columns,$checkColumn);
	}
}