<?php
// no direct access
if(!defined('_JEXEC')) die('Restricted access');
jimport( 'joomla.plugin.plugin' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

define('IMPEXP_LIMIT',1000);

//includes file containing functions and html code
require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'export.php');
require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'import.php');

class plgSystemImportExport_csv extends JPlugin
{
	var $_debugMode = 0;
	var $mysess     = null;
	var $storagePath= null;
	function __construct( &$subject, $params )
	{
		parent::__construct( $subject, $params );
		$this->mysess = JFactory::getSession();
		$this->storagePath  = JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS;
		$this->export = new ImpexpPluginExport();
		$this->import = new ImpexpPluginImport();
	}
	
	function onAfterRoute()
	{
		if(JFactory::getApplication()->isAdmin() == false)
			return true;

		$plugin = JRequest::getVar('plugin',false);
		$task   = JRequest::getVar('task',false);			
		$stage  = JRequest::getVar('importCSVStage','upload');
				
		if($plugin != 'importexportCSV')
			return true;

		if($task=='export'){
			$this->export->createCSV($this->storagePath);
		}

		if($task=='uploadFile'){
			
			if($stage == 'upload')
				$html = $this->import->getUploaderHtml();
				
			else if($stage == 'fieldMapping')
				$html = $this->import->getMappingHtml($this->mysess, $this->storagePath);
			
			else if($stage == 'importData')
				$html = $this->import->importData($this->mysess);
			
			else if($stage == 'createUser'){
				$importuser_count = $this->mysess->get('count',0);
				$html = $this->import->createUser($this->mysess, $this->storagePath, $importuser_count);				
			}
			
			else if($stage == 'complete')
				$html	=	$this->import->complete();
						
			$document = JFactory::getDocument();
			$document->setBuffer($html, 'component');
			JFactory::getApplication()->render();
			echo JResponse::toString(JFactory::getApplication()->getCfg('gzip'));
			exit;		
		}
	}	
}