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
  
  function xtestImportWorking()
  {
  	$this->open(JOOMLA_LOCATION."administrator/index.php?plugin=importexportCSV&task=uploadFile&tmpl=component");
   	$this->waitPageLoad("1000000");
   	
   	$this->type('fileUploaded', JOOMLA_FTP_LOCATION.'/user.csv'); 
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
