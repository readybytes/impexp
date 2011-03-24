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

    $this->type("mod-login-username", JOOMLA_ADMIN_USERNAME);
    $this->type("mod-login-password", JOOMLA_ADMIN_PASSWORD);
    $this->click("//input[@value='Log in']");

    $this->waitForPageToLoad();
    $this->assertTrue($this->isTextPresent("Logout"));
  }

  function frontLogin($username=JOOMLA_ADMIN_USERNAME, $password= JOOMLA_ADMIN_PASSWORD)
  {
    $this->open(JOOMLA_LOCATION."/index.php");
    $this->waitForPageToLoad("30000");

    $this->type("modlgn-username", $username);
    $this->type("modlgn-passwd", $password);
    $this->click("Submit");
    $this->waitForPageToLoad();
    $this->assertEquals("Log out", $this->getValue("//form[@id='login-form']/div[2]/input[1]"));
  }

  function frontLogout()
  {
  	$this->open(JOOMLA_LOCATION."/index.php");
    $this->waitForPageToLoad("30000");
    $this->assertEquals("Log out", $this->getValue("//form[@id='login-form']/div[2]/input"));
    $this->click("//form[@id='login-form']/div[2]/input");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isElementPresent("modlgn-username"));
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

		$configString = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));
  		
		if (!JFile::write($fname,$configString))
		{
			echo JText::_('ERRORCONFIGFILE');
		}

  }

  function changePluginState($pluginname, $action=1)
  {

		$db			=& JFactory::getDBO();
		$query	= 'UPDATE ' . $db->nameQuote( '#__extensions' )
				. ' SET '.$db->nameQuote('enabled').'='.$db->Quote($action)
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
		$query	= 'SELECT '.$db->nameQuote('enabled')
				.' FROM ' . $db->nameQuote( '#__extensions' )
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
}
