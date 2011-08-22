<?php

class ExportTest extends XiSelTestCase
{
  function getSqlPath()
  {
      return dirname(__FILE__).'/sql/'.__CLASS__;
  }
  
  function testExport()
  {
  	$this->adminLogin();

	//in joomla1.6 you can not access a resource directly by copy pasting the url
	$this->click("//a[@class='icon-16-plugin']");
    $this->waitPageLoad("30000");
	//search for import export plugin
	$this->type("//input[@id='filter_search']","Import");
    $this->click("//button[@type='submit']");
    $this->waitPageLoad("30000");
	$this->click("link=Import/Export User Data For JS");
	$this->waitPageLoad("60000");
	
	$this->assertTrue($this->isTextPresent("Export User Data"));
	
	$element = " //a[@id='exportPopup']";
    $this->assertTrue($this->isElementPresent($element));
  }
  
  function testImport()
  {
  	$this->adminLogin();

	$this->click("//a[@class='icon-16-plugin']");
    $this->waitPageLoad("30000");
	//search for import export plugin
    $this->type("//input[@id='filter_search']","Import");
    $this->click("//button[@type='submit']");
    $this->waitPageLoad("30000");
	$this->click("link=Import/Export User Data For JS");
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
   	
   	$this->select('csvField0','Username');
   	$this->select('csvField3','Password');
   	$this->select('csvField2','Email');
   	$this->click('//input[@value="Import Data"]');
   	$this->waitPageLoad();
   	sleep(30);
   	
   	$this->isTextPresent('Instead of existing users, All Users are successfully imported.');
   	
   	$element = " //a[@id='existuser']";
    $this->assertTrue($this->isElementPresent($element));
    
    $element = " //a[@id='importeduser']";
    $this->assertTrue($this->isElementPresent($element));
  }
  
   function testimportUser()
   {
   	$this->adminLogin();
   	
  	$this->open(JOOMLA_LOCATION."administrator/index.php?plugin=importexportCSV&task=uploadFile&tmpl=component");
   	$this->waitPageLoad("1000000");
   	
   	$this->type('fileUploaded', JOOMLA_FTP_LOCATION.DS.'test'.DS.'test'.DS.'admin'.DS.'user1.csv'); 
   	$this->click('btnUpload');
   	$this->waitPageLoad();

   	$this->select('csvField0','Username');
   	$this->select('csvField3','Password');
   	$this->select('csvField2','Email');
   	$this->select("csvField4", "Usertype");
   	$this->click('//input[@value="Import Data"]');
   	$this->waitPageLoad();
    sleep(30);
    
   	$element = " //a[@id='existuser']";
    $this->assertTrue($this->isElementPresent($element));
    
    $element = " //a[@id='importeduser']";
    $this->assertTrue($this->isElementPresent($element));
    
    $this->_DBO->addTable('#__user_usergroup_map');
  }
}
