<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class XiSelTestCase extends PHPUnit_Extensions_SeleniumTestCase
{
  var  $_DBO;
  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = SCREENSHOT_PATH;
  protected $screenshotUrl  = SCREENSHOT_URL;


  protected $collectCodeCoverageInformation = TRUE;
  protected $coverageScriptUrl = 'http://localhost/dummy/phpunit_coverage.php';

  function setUp()
  {
  	$this->parentSetup();
  }

  function parentSetup()
  {
  	$this->setHost(SEL_RC_SERVER);
  	$this->setPort(SEL_RC_PORT);
  	$this->setTimeout(10);

  	//to be available to all childs
    $this->setBrowser("*chrome");
    $this->setBrowserUrl( JOOMLA_LOCATION);

    $filter['debug']=1;
    $filter['error_reporting']=6143;
    $filter['lifetime']=1500;
    $this->updateJoomlaConfig($filter);
  }

  function assertPreConditions()
  {
    // this will be a assert for every test
    if(method_exists($this,'getSqlPath'))
        $this->assertEquals($this->_DBO->getErrorLog(),'');
  }

  function assertPostConditions()
  {
     // if we need DB based setup then do this
     if(method_exists($this,'getSqlPath'))
     {
     	if($this->_DBO->verify()===false){
     		echo "\n Error log : \n " . var_export($this->_DBO->getErrorLog(),true) . " \n\n";
     		$this->assertTrue(false);
     	}
     }
  }

  function click($elem)
  {
  	$this->assertTrue($this->isElementPresent($elem));
  	parent::click($elem);
  }

  function type($elem, $data)
  {
  	$this->assertTrue($this->isElementPresent($elem));
  	parent::type($elem, $data);
  }

  function adminLogin()
  {
    $this->open(JOOMLA_LOCATION."/administrator/index.php?option=com_login");
    $this->waitForPageToLoad("30000");

    $this->type("modlgn_username", JOOMLA_ADMIN_USERNAME);
    $this->type("modlgn_passwd", JOOMLA_ADMIN_PASSWORD);
    $this->click("//form[@id='form-login']/div[1]/div/div/a");

    $this->waitForPageToLoad();
    $this->assertTrue($this->isTextPresent("Logout"));
  }

  function frontLogin($username=JOOMLA_ADMIN_USERNAME, $password= JOOMLA_ADMIN_PASSWORD)
  {
    $this->open(JOOMLA_LOCATION."/index.php");
    $this->waitForPageToLoad("30000");

    $this->type("modlgn_username", $username);
    $this->type("modlgn_passwd", $password);
    $this->click("//form[@id='form-login']/fieldset/input");
    $this->waitForPageToLoad();
    $this->assertEquals("Log out", $this->getValue("//form[@id='form-login']/div[2]/input"));
  }

  function frontLogout()
  {
  	$this->open(JOOMLA_LOCATION."/index.php");
    $this->waitForPageToLoad("30000");
    $this->assertEquals("Log out", $this->getValue("//form[@id='form-login']/div[2]/input"));
    $this->click("//form[@id='form-login']/div[2]/input");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isElementPresent("modlgn_username"));
  }

  function waitPageLoad($time=TIMEOUT_SEC,$errorCheck=true)
  {
      $this->waitForPageToLoad($time);
      // now we just want to verify that
      // page does not have any type of error

      if($errorCheck) {
	      $result	=	$this->isTextPresent("( ! ) Notice:")
	      					|| $this->isTextPresent("( ! ) Fatal error:")
	      					|| $this->isTextPresent("( ! ) Error:")
	      					|| $this->isTextPresent("500 - An error has occurred.");

	      $this->assertFalse($result);
      }
      // a call stack ping due to assert/notice etc.
  }

  function waitForElement($element)
  {
	  //wait for ajax window
  		for ($second = 0; ; $second++) {
	        if ($second >= 10) $this->fail("timeout");
	        try {
	            if ($this->isElementPresent($element)) break;
	        } catch (Exception $e) {}
	        sleep(1);
	    }
  }


  function updateJoomlaConfig($filter)
  {
	  	$config =& JFactory::getConfig();
  		foreach($filter as $key=>$value)
  			$config->setValue($key,$value);

		jimport('joomla.filesystem.file');
		$fname = JPATH_CONFIGURATION.DS.'configuration.php';

		system("sudo chmod 777 $fname");

  		if (!JFile::write($fname,
  				$config->toString('PHP', 'config', array('class' => 'JConfig')) )
  		    )
		{
			echo JText::_('ERRORCONFIGFILE');
		}

  }

  function changePluginState($pluginname, $action=1)
  {

		$db			=& JFactory::getDBO();
		$query	= 'UPDATE ' . $db->nameQuote( '#__plugins' )
				. ' SET '.$db->nameQuote('published').'='.$db->Quote($action)
	          	.' WHERE '.$db->nameQuote('element').'='.$db->Quote($pluginname);

		$db->setQuery($query);

		if(!$db->query())
			return false;

		return true;
  }


  /**
   * Verifies that plugin is in correct state
   * @param $pluginname : Name of plugin
   * @param $enabled : Boolean,
   * @return unknown_type
   */
  function verifyPluginState($pluginname, $folder="system", $enabled=true)
  {

		$db			=& JFactory::getDBO();
		$query	= 'SELECT '.$db->nameQuote('published')
				.' FROM ' . $db->nameQuote( '#__plugins' )
	          	.' WHERE '.$db->nameQuote('element').'='.$db->Quote($pluginname);

		$db->setQuery($query);
		$actualState= (boolean) $db->loadResult();
		$this->assertEquals($actualState, $enabled);
  }


  function changeModuleState($modname, $action=1)
  {

		$db			=& JFactory::getDBO();
		$query	= 'UPDATE ' . $db->nameQuote( '#__modules' )
				. ' SET '.$db->nameQuote('published').'='.$db->Quote($action)
	          	.' WHERE '.$db->nameQuote('module').'='.$db->Quote($modname);

		$db->setQuery($query);

		if(!$db->query())
			return false;

		return true;
  }


  /**
   * Verifies that plugin is in correct state
   * @param $pluginname : Name of plugin
   * @param $enabled : Boolean,
   * @return unknown_type
   */
  function verifyModuleState($modname, $enabled=true)
  {

		$db			=& JFactory::getDBO();
		$query	= 'SELECT '.$db->nameQuote('published')
				.' FROM ' . $db->nameQuote( '#__modules' )
	          	.' WHERE '.$db->nameQuote('module').'='.$db->Quote($modname);

		$db->setQuery($query);
		$actualState= (boolean) $db->loadResult();
		$this->assertEquals($actualState, $enabled);
  }

  function installPkg($pkg)
  {
  	$this->adminLogin();

	// go to installation
	$this->open(JOOMLA_LOCATION."/administrator/index.php?option=com_installer");
	$this->waitPageLoad();

	$this->type("install_url", $pkg);
	$this->click("//form[@name='adminForm']/table[3]/tbody/tr[2]/td[2]/input[2]");
	$this->waitPageLoad();
	$this->assertTrue($this->isTextPresent("Install Component Success"));

	$this->assertFalse($this->isElementPresent("//dl[@id='system-error']/dd/ul/li"));
  }


  function uninstallPkg($pkg)
  {
	// setup default location
	$this->adminLogin();

	// go to installation
	$this->open(JOOMLA_LOCATION."/administrator/index.php?option=com_installer");
	$this->waitPageLoad();

	$this->click("//a[@onclick=\"javascript:document.adminForm.type.value='components';submitbutton('manage');\"]");
	$this->waitPageLoad();

	//now find the component order in uninstall list
	$order = $this->getComponentOrder($pkg);
	$this->click("cb$order");
	$this->click("link=Uninstall");
	$this->waitPageLoad();
	$this->assertTrue($this->isTextPresent("Uninstall Component Success"));
	$this->assertFalse($this->isElementPresent("//dl[@id='system-error']/dd/ul/li"));
  }

  function verifyXiecInstallation()
  {
	$this->verifyPluginState('xiec','system', true);
	//$this->verifyModuleState('mod_subsubmenu',true);

	//Check xisub installation
	//$order = $this->getComponentOrder('com_xisub');
	//$this->assertNotEquals(-1,$order);
  }

  function verifyXiecUninstall()
  {
	$this->verifyPluginState('xiec','system', false);
	//$this->verifyModuleState('mod_subsubmenu',false);
  }


  function getComponentOrder($component)
  {
	$db = JFactory::getDBO();
	$sql = "SELECT * FROM `#__components`
			WHERE `parent` = '0'
			ORDER BY `iscore`, `name`";
	$db->setQuery($sql);
	$results = $db->loadAssocList();

	$i=0;
	foreach($results as $r) {
		if($r['option']==$component)
			return $i;

		$i++;
	}

	return -1;
  }

}
