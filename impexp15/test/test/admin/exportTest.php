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
  	
   	$this->select('csvField0','Username');
   	$this->select('csvField3','Password');
   	$this->select('csvField2','Email');
   	$this->select('csvField1','Name');
   	$this->select('csvField4','Usertype');
   	$this->select('csvField5','');
   	$this->select('csvField6','');
   	$this->select('csvField7','Gender');
   	$this->select('csvField8','Birthday');
   	$this->select('csvField9','About me');
   	$this->select('csvField10','Mobile phone');
   	$this->select('csvField11','Land phone');
   	$this->select('csvField12','Address');
   	$this->select('csvField13','State');
   	$this->select('csvField14','City / Town');
   	$this->select('csvField15','Country');
   	$this->select('csvField16','Website');
   	$this->select('csvField17','College / University');
   	$this->select('csvField18','Graduation Year');
   	$this->select('csvField19','Status');
   	$this->select('csvField20','Points');
   	$this->select('csvField21','Posted_on');
   	$this->select('csvField22','Avatar');
   	$this->select('csvField23','Thumb');
   	$this->select('csvField24','Invite');
   	$this->select('csvField25','Params');
   	$this->select('csvField26','Alias');
   	$this->select('csvField27','Profile_id');
   	$this->select('csvField28','Watermark_hash');
   	$this->select('csvField29','Storage');
   	$this->select('csvField30','Search_email');
   	$this->select('csvField31','Friends');
   	$this->select('csvField32','Groups');
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
