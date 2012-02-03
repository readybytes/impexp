<?php

jimport('joomla.html.toolbar.button.popup');

class ExportTest extends XiSelTestCase
{
  function getSqlPath()
  {
      return dirname(__FILE__).'/sql/'.__CLASS__;
  }
  
  function testExport()
  {
  	$this->adminLogin();
   	 
  	$db			= JFactory::getDBO();
	$query	= 'SELECT '.$db->nameQuote('id')
				.' FROM ' . $db->nameQuote( '#__plugins' )
	          	.' WHERE '.$db->nameQuote('element').'= "importexport_csv"';

	$db->setQuery($query);
	$pid= $db->loadResult();
   	$this->open(JOOMLA_LOCATION."administrator/index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]=".$pid);
   	$this->waitPageLoad("60000");
   	 
	$this->assertTrue($this->isTextPresent("Export User Data"));
	
	$element = " //a[@id='exportPopup']";
    $this->assertTrue($this->isElementPresent($element));
  }
  
  function testImport()
  {
  	$this->adminLogin();
   	 
  	$db			=& JFactory::getDBO();
	$query	= 'SELECT '.$db->nameQuote('id')
				.' FROM ' . $db->nameQuote( '#__plugins' )
	          	.' WHERE '.$db->nameQuote('element').'= "importexport_csv"';

	$db->setQuery($query);
	$pid= $db->loadResult();
   	$this->open(JOOMLA_LOCATION."administrator/index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]=".$pid);
   	$this->waitPageLoad("60000");
   	 
	$this->assertTrue($this->isTextPresent("Upload CSV File"));
	
	$element = " //a[@id='uploaderPopup']";
    $this->assertTrue($this->isElementPresent($element));
  }
  
  function testImportWorking()
  {
  	$this->open(JOOMLA_LOCATION."administrator/index.php?plugin=importexportCSV&task=uploadFile&tmpl=component");
   	$this->waitPageLoad("1000000");
   	
   	$this->type('fileUploaded', JOOMLA_FTP_LOCATION.DS.'test'.DS.'test'.DS.'admin'.DS.'user.csv'); 
   	$this->click('btnUpload');
   	$this->waitPageLoad();

  	$this->assertTrue($this->isTextPresent("Username"));
   	$this->select('csvField0','Username');
   	$this->select('csvField1','Name');
   	$this->click('//input[@value="Import Data"]');
   	$this->assertAlert('Username, Password or Email field is not map');
   	
   	$this->select('csvField0','Username');
   	$this->select('csvField3','Password');
   	$this->click('//input[@value="Import Data"]');
   	$this->assertAlert('Username, Password or Email field is not map');
   	
   	$this->select('csvField0','Name');
   	$this->select('csvField3','Password');
   	$this->select('csvField2','Email');
   	$this->click('//input[@value="Import Data"]');
   	$this->assertAlert('Username, Password or Email field is not map');
   	
  }
  
  function testAllFieldWorking()
  {
    $this->open(JOOMLA_LOCATION."administrator/index.php?plugin=importexportCSV&task=uploadFile&tmpl=component");
   	$this->waitPageLoad("60000");
   	$this->type('fileUploaded', JOOMLA_FTP_LOCATION.DS.'test'.DS.'test'.DS.'admin'.DS.'user.csv'); 
   	$this->click('btnUpload');
   	$this->waitPageLoad();	
  	
   	$this->select("csvField0",  "label=Username");
    $this->select("csvField1",  "label=Name");
    $this->select("csvField2",  "label=Email");
    $this->select("csvField3",  "label=Password");
    $this->select("csvField4",  "label=Usertype");
    $this->select("csvField5",  "label=Gender");
    $this->select("csvField9",  "label=Mobile phone");
    $this->select("csvField10", "label=Land phone");
    $this->select("csvField11", "label=Address");
    $this->select("csvField12", "label=State");
    $this->select("csvField13", "label=City / Town");
    $this->select("csvField14", "label=Country");
    $this->select("csvField15", "label=Website");
    $this->select("csvField16", "label=College / University");
    $this->select("csvField17", "label=Graduation Year");
    $this->select("csvField18", "label=Status");
    $this->select("csvField19", "label=Points");
    $this->select("csvField20", "label=Posted_on");
    $this->select("csvField21", "label=Avatar");
    $this->select("csvField22", "label=Thumb");
    $this->select("csvField23", "label=Invite");
    $this->select("csvField24", "label=Params");
    $this->select("csvField25", "label=Alias");
    $this->select("csvField26", "label=Latitude");
    $this->select("csvField27", "label=Longitude");
    $this->select("csvField28", "label=Profile_id");
    $this->select("csvField29", "label=Watermark_hash");
    $this->select("csvField30", "label=Storage");
    $this->select("csvField31", "label=Search_email");
    $this->select("csvField32", "label=Friends");
    $this->select("csvField33", "label=Groups");
   	$this->click('//input[@value="Import Data"]');
   	$this->waitPageLoad();
   	sleep(60);
   	
    $element = " //a[@id='importeduser']";
    $this->assertTrue($this->isElementPresent($element));
  }
  
  function testExistUser()
  {
  	$this->open(JOOMLA_LOCATION."administrator/index.php?plugin=importexportCSV&task=uploadFile&tmpl=component");
   	$this->waitPageLoad("60000");
   	$this->type('fileUploaded', JOOMLA_FTP_LOCATION.DS.'test'.DS.'test'.DS.'admin'.DS.'test.csv'); 
    $this->click("btnUpload");
    $this->waitForPageToLoad("30000");
    $this->select("csvField0", "label=Username");
    $this->select("csvField2", "label=Email");
    $this->select("csvField3", "label=Password");
   	$this->click('//input[@value="Import Data"]');
   	$this->waitPageLoad("1000");
   	sleep(20);
   	
   	$this->isTextPresent('Instead of existing users, All Users are successfully imported.');
    $element = " //a[@id='existuser']";
    $this->assertTrue($this->isElementPresent($element));
  }
  
  function testSizeDiscardUser()
  {
  	$this->open(JOOMLA_LOCATION."administrator/index.php?plugin=importexportCSV&task=uploadFile&tmpl=component");
   	$this->waitPageLoad("60000");
   	$this->type('fileUploaded', JOOMLA_FTP_LOCATION.DS.'test'.DS.'test'.DS.'admin'.DS.'comma.csv');
   	$this->type("seperator", ",");
    $this->click("btnUpload");
    $this->waitForPageToLoad("30000");
    $this->select("csvField2", "label=Username");
    $this->select("csvField3", "label=Email");
    $this->select("csvField4", "label=Password");
    $this->click("//input[@value='Import Data']");
    $this->waitPageLoad("1000");
    sleep(20);
   
    $element = " //a[@id='sizediscardusers']";
    $this->assertTrue($this->isElementPresent($element));
  }
  
  function testReplacedUser()
  {
  	$this->open(JOOMLA_LOCATION."administrator/index.php?plugin=importexportCSV&task=uploadFile&tmpl=component");
   	$this->waitPageLoad("60000");
   	$this->type('fileUploaded', JOOMLA_FTP_LOCATION.DS.'test'.DS.'test'.DS.'admin'.DS.'testFile.csv');
   	$this->click("//input[@name='userid' and @value='1']");
    $this->click("//input[@name='overwrite' and @value='1']");
    $this->click("btnUpload");
    $this->waitForPageToLoad("30000");
    $this->select("csvField0", "label=Id");
    $this->select("csvField2", "label=Username");
    $this->select("csvField3", "label=Email");
    $this->select("csvField4", "label=Password");
    $this->click("//input[@value='Import Data']");
    $this->waitForPageToLoad("10000");
    sleep(20);
   
    $element = " //a[@id='importeduser']";
    $this->assertTrue($this->isElementPresent($element));
    $element = " //a[@id='replaceusers']";
    $this->assertTrue($this->isElementPresent($element));
  }
  
  function testDiscardUser()
  {
	  	$this->open(JOOMLA_LOCATION."administrator/index.php?plugin=importexportCSV&task=uploadFile&tmpl=component");
	   	$this->waitPageLoad("60000");
	   	$this->type('fileUploaded', JOOMLA_FTP_LOCATION.DS.'test'.DS.'test'.DS.'admin'.DS.'testFile.csv');
	   	$this->type("seperator", ",");
	   	$this->click("//input[@name='userid' and @value='1']");
	    $this->click("btnUpload");
	    $this->waitForPageToLoad("30000");
	    $this->select("csvField0", "label=Id");
	    $this->select("csvField2", "label=Username");
	    $this->select("csvField3", "label=Email");
	    $this->select("csvField4", "label=Password");
	    $this->click("//input[@value='Import Data']");
	    $this->waitPageLoad("1000");
	    sleep(20);
	   
	    $element = " //a[@id='discardusers']";
	    $this->assertTrue($this->isElementPresent($element));
  }
  
  function testSeperatorMatch()
  {
  	$this->open(JOOMLA_LOCATION."administrator/index.php?plugin=importexportCSV&task=uploadFile&tmpl=component");
   	$this->waitPageLoad("1000000");
   	
   	$this->type('fileUploaded', JOOMLA_FTP_LOCATION.DS.'test'.DS.'test'.DS.'admin'.DS.'comma.csv');
   	$this->type("seperator", '","');
   	$this->click('btnUpload');
   	$this->waitPageLoad();
   	$this->assertTrue($this->isTextPresent("Seperator does not match."));
  }
  
  function testImportUserID()
  {
    $this->open(JOOMLA_LOCATION."administrator/index.php?plugin=importexportCSV&task=uploadFile&tmpl=component");
   	$this->waitPageLoad("1000000");
   	
   	$this->type('fileUploaded', JOOMLA_FTP_LOCATION.DS.'test'.DS.'test'.DS.'admin'.DS.'comma.csv');
   	$this->type("seperator", ",");
    $this->click("//input[@name='userid' and @value='1']");
    $this->click("btnUpload");
    $this->waitForPageToLoad("30000");
    $this->select("csvField2", "label=Username");
    $this->select("csvField3", "label=Email");
    $this->select("csvField4", "label=Password");
    $this->click("//input[@value='Import Data']");
    $this->assertEquals("Id, Username, Password or Email field is not map", $this->getAlert());
  }
  
   function testDatabaseEmpty()
  {
    $this->adminLogin();
  	$db		= JFactory::getDBO();
	$query	= 'SELECT '.$db->nameQuote('id')
				.' FROM ' . $db->nameQuote( '#__plugins' )
	          	.' WHERE '.$db->nameQuote('element').'= "importexport_csv"';

	$db->setQuery($query);
	$pid= $db->loadResult();
   	$this->open(JOOMLA_LOCATION."administrator/index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]=".$pid);
   	$this->waitPageLoad("60000");
  	$this->click("exportPopup");
  	$this->waitPageLoad("10000");
  	$this->assertTrue($this->isTextPresent("Your joomla user table is empty."));
  }
}
