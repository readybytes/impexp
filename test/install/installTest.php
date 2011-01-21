<?php

class InstallTest extends XiSelTestCase
{
  function getSqlPath()
  {
      return dirname(__FILE__).'/sql/'.__CLASS__;
  }

  protected $collectCodeCoverageInformation = FALSE;

  function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl( JOOMLA_LOCATION."/administrator/index.php?option=com_login");

    //verify tables setup
    $this->assertEquals($this->_DBO->getErrorLog(),'');
  }

  function waitPageLoad($time=TIMEOUT_SEC)
  {
      $this->waitForPageToLoad($time);
  }


  function testXiecInstall()
  {
	$this->installPkg(XIEC_PKG);
	$this->verifyXiecInstallation();
  }


  function testXiecUpgrade()
  {
	$this->installPkg(XIEC_PKG);
 	$this->verifyXiecInstallation();
  }


  function testXiecUninstallReinstall()
  {
   	$this->uninstallPkg('com_xiec');
	$this->verifyXiecUninstall();

	$this->installPkg(XIEC_PKG);
    $this->verifyXiecInstallation();
  }

  function testChmodAll()
  {
  		chmod( JPATH_ROOT,0777);
  }
}
