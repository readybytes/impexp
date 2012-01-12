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
   	
  }
  
   function xtestimportUser()
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
  
function testAllFieldWorking()
  {
  	
    $this->open(JOOMLA_LOCATION."administrator/index.php?plugin=importexportCSV&task=uploadFile&tmpl=component");
   	$this->waitPageLoad("60000");
   	$this->type('fileUploaded', JOOMLA_FTP_LOCATION.DS.'test'.DS.'test'.DS.'admin'.DS.'user.csv'); 
   	$this->click('btnUpload');
   	$this->waitPageLoad();	
  	
   $this->select('csvField0','Username');
   	$this->select('csvField3','Password');
   	$this->select('csvField2','Email');
   	$this->select('csvField1','Name');
   	$this->select('csvField4','Usertype');
   	$this->select('csvField5','Gender');
   	$this->select('csvField6','Birthdate');
   	$this->select('csvField7','About me');
   	$this->select('csvField8','Mobile phone');
   	$this->select('csvField9','Land phone');
   	$this->select('csvField10','Address');
   	$this->select('csvField11','State');
   	$this->select('csvField12','City / Town');
   	$this->select('csvField13','Country');
   	$this->select('csvField14','Website');
   	$this->select('csvField15','College / University');
   	$this->select('csvField16','Graduation Year');
   	$this->select('csvField17','Status');
   	$this->select('csvField18','Points');
   	$this->select('csvField19','Posted_on');
   	$this->select('csvField20','Avatar');
   	$this->select('csvField21','Thumb');
   	$this->select('csvField22','Invite');
   	$this->select('csvField23','Params');
   	$this->select('csvField24','Alias');
   	$this->select('csvField25','Profile_id');
   	$this->select('csvField26','Watermark_hash');
   	$this->select('csvField27','Storage');
   	$this->select('csvField28','Search_email');
   	$this->select('csvField29','Friends');
   	$this->select('csvField30','Groups');
   $this->click('//input[@value="Import Data"]');
   $this->waitPageLoad();
   sleep(60);
   	
   	$this->isTextPresent('Instead of existing users, All Users are successfully imported.');
   	
   	$element = " //a[@id='existuser']";
    $this->assertTrue($this->isElementPresent($element));
    
    $element = " //a[@id='importeduser']";
    $this->assertTrue($this->isElementPresent($element));
  }
}
