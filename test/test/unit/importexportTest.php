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
						 'csvField11' => 'custom_9', 'csvField12' => 'custom_10', 'csvField13' => 'custom_11', 'csvField14' => 'custom_12', 'csvField15' => 'custom_13', 'csvField16' => 'custom_15', 'csvField17' => 'custom_16', 'importCSVStage' => 'importData' );
		$mapping = ImpexpPluginImport::getFieldMapping($post,'joomla');
		$joomlafield = array ( 'username' => '0', 'name' => '1', 'email' => '2', 'password' => '3', 'usertype' => '4' );
		$this->assertEquals($joomlafield,$mapping);	

		//test jsfields mapping
		$mapping = ImpexpPluginImport::getFieldMapping($post,'jsfield');
		$custom = array ( );
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
		$userData = ImpexpPluginExport::getUserData(0);
		$this->assertEquals($csvData, $userData);

		//when there is limited data in database
		$this->_DBO->loadSql($this->getSqlPath().DS.'testgetUserDataValue.start.sql');
		$userData = ImpexpPluginExport::getUserData(0);		
		$csvData = array (64 => array ( 'username' => 'shansmith01', 'name' => 'Shannon', 'email' => 'shannon@nomadsworld.com', 'password' => 'd164259c3e82d79bca1ffc6d6db5986c:EizmMsVD0SrcA1cNNENjUyRRxbGrwfhs', 'usertype' => 'Registered', 21 => 'Free Member', 28 => 'default', 29 => '1', ),
							65 => array ( 'username' => 'pembaris', 'name' => 'pembaris', 'email' => 'pembaris@gmail.com', 'password' => '9c57460b92ecf9741c40e55deb061f6f:h1JXinxGbBEe9jb4wcOaQIzOsw0PVQWO', 'usertype' => 'Registered', 21 => 'Free Member', 28 => 'default', 29 => '1', ),
							66 => array ( 'username' => 'collaborator', 'name' => 'collaborator', 'email' => 'collaborator@bonbon.net', 'password' => 'cf3e4436268bb1cfde6e5f516cad7640:brhPLEExGWTQbMw9CMlIiI7zAdi17i56', 'usertype' => 'Registered', 21 => 'Free Member', 28 => 'default', 29 => '1' ));
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
        $field = array(2 => $obj);
		$customfield = ImpexpPluginExport::getCustomFieldIds();
		$this->assertEquals($field,$customfield);
	}
	
	function testcreateCSV()
	{
		$this->includefile();
		$storagePath = JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS;
		$createCSV = new ImpexpPluginExport();
		$this->assertTrue($createCSV->createCSV($storagePath));
	}
}