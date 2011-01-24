<?php

class InstallTest extends XiSelTestCase
{
  function getSqlPath()
  {
      return dirname(__FILE__).'/sql/'.__CLASS__;
  }

  protected $collectCodeCoverageInformation = FALSE;

  function testImpExpInstall()
  {
  	 // setup default location 
    $this->adminLogin();
    
  	 // go to installation
    $this->open(JOOMLA_LOCATION."/administrator/index.php?option=com_installer");
    $this->waitPageLoad("60000");
      
	$this->type("install_url", IMPEXP_PKG);
    $this->click("//form[@name='adminForm']/table[3]/tbody/tr[2]/td[2]/input[2]");
   
    $this->waitPageLoad();
    $this->assertTrue($this->isTextPresent("Install Plugin Success"));
    $this->assertFalse($this->isElementPresent("//dl[@id='system-error']/dd/ul/li"));
	
  } 
   
   function testImpExpEnable()
   {
   	 $this->adminLogin();
   	 
   	 $this->open(JOOMLA_LOCATION."administrator/index.php?option=com_plugins");
   	 $this->waitPageLoad("60000");
   	 
   	 $this->assertTrue($this->changePluginState('importexport_csv',1));
   	 
   }
}
