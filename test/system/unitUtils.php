<?php
require_once 'PHPUnit/Framework.php';

class XiUnitTestCase extends PHPUnit_Framework_TestCase
{
  var  $_DBO;
  function setUp()
  {
  	$filter['debug']=1;
    $filter['error_reporting']=6143;
    $this->updateJoomlaConfig($filter);
  	XiFactory::getErrorObject(true);
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
         $this->assertTrue($this->_DBO->verify());
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


function verifyPluginState($pluginname, $folder="system", $enabled=true)
  {

		$db			=& JFactory::getDBO();
		$query	= 'SELECT '.$db->nameQuote('published')
				.' FROM ' . $db->nameQuote( '#__plugins' )
	          	.' WHERE '.$db->nameQuote('element').'='.$db->Quote($pluginname)
	          	. ' AND ' . $db->nameQuote('folder').'='.$db->Quote($folder)
	          	;

		$db->setQuery($query);
		$actualState= (boolean) $db->loadResult();
		$this->assertEquals($actualState, $enabled);
  }

  function verifyModuleState($name, $enabled=true)
  {

		$db			=& JFactory::getDBO();
		$query	= 'SELECT '.$db->nameQuote('published')
				.' FROM ' . $db->nameQuote( '#__modules' )
	          	.' WHERE '.$db->nameQuote('module').'='.$db->Quote($name)
	          	;

		$db->setQuery($query);
		$actualState= (boolean) $db->loadResult();
		$this->assertEquals($actualState, $enabled);
  }

  function cleanWhiteSpaces($str)
	{
		$str = preg_replace('#[\\n\\b\\s\\t]+#','' , $str);
		return $str;
	}
	
	function cleanStaticCache()
	{
		XiFactory::cleanStaticCache(true);
	}
}
