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
   	 
  	$db			= JFactory::getDBO();
	$query	= 'SELECT '.$db->nameQuote('extension_id')
				.' FROM ' . $db->nameQuote( '#__extensions' )
	          	.' WHERE '.$db->nameQuote('element').'= "importexport_csv"';

	$db->setQuery($query);
	$pid= $db->loadResult();
//   	$this->open(JOOMLA_LOCATION."administrator/index.php?option=com_plugins&view=plugin&layout=edit&extension_id=".$pid);
//   	$this->waitPageLoad("60000");

	//in joomla1.6 you can not access a resource directly by copy pasting the url
	$this->click("link=Plug-in Manager");
    $this->waitPageLoad("30000");
	$this->type("filter_search", "Import");
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
   	 
  	$db			=& JFactory::getDBO();
	$query	= 'SELECT '.$db->nameQuote('extension_id')
				.' FROM ' . $db->nameQuote( '#__extensions' )
	          	.' WHERE '.$db->nameQuote('element').'= "importexport_csv"';

	$db->setQuery($query);
	$pid= $db->loadResult();
//   	$this->open(JOOMLA_LOCATION."administrator/index.php?option=com_plugins&view=plugin&layout=edit&extension_id=".$pid);
//   	$this->waitPageLoad("60000");
	//XITODO: replace with XPath
	$this->click("link=Plug-in Manager");
    $this->waitPageLoad("30000");
	$this->type("filter_search", "Import");
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
   	
   	$this->isTextPresent('Instead of existing users, All Users are successfully imported.');
   	
   	$element = " //a[@id='existuser']";
    $this->assertTrue($this->isElementPresent($element));
    
    $element = " //a[@id='importeduser']";
    $this->assertTrue($this->isElementPresent($element));
  }
}
