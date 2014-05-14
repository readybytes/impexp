<?php
/*
* importexport_csv - JomSocial User Import Export
------------------------------------------------------------------------
* copyright	Copyright (C) 2010 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* Author : Team JoomlaXi @ Ready Bytes Software Labs Pvt. Ltd.
* Email  : shyam@joomlaxi.com
* License : GNU-GPL V2
* Websites: www.joomlaxi.com
* Technical Support:  Forum - http://joomlaxi.com/support/forum/47-impexp-1x.html
*/

// no direct access
if(!defined('_JEXEC')) die('Restricted access');
jimport( 'joomla.plugin.plugin' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
if (!defined('DS'))
  {
     define('DS', DIRECTORY_SEPARATOR);
  }
if(defined('INCLUDE_IMPEXP')===false)
require_once(dirname(__FILE__) .DS. 'importexport_csv' .DS. 'includes.php');

//includes file containing functions and html code
require_once(dirname(__FILE__) .DS. 'importexport_csv' .DS. 'export.php');
require_once(dirname(__FILE__) .DS. 'importexport_csv' .DS. 'import.php');

class plgSystemImportExport_csv extends JPlugin
{
	var $_debugMode = 0;
	var $mysess     = null;
	var $storagePath= null;
	function __construct( &$subject, $config )
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		$this->mysess = JFactory::getSession();
		if(IMPEXP_JVERSION ==='1.5'){
			$this->storagePath  = JFactory::getConfig()->getValue('tmp_path').DS;
		}
		else {
			$this->storagePath  = Jfactory::getConfig()->get('tmp_path').DS;
		}

		// To strip additional / or \ in a path name.
		$this->storagePath  = JPath::clean($this->storagePath);
		$this->export       = new ImpexpPluginExport();
		$this->import       = new ImpexpPluginImport();
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

		if($task == 'export'){
			if($stage == 'upload'){
			   $html = $this->export->getExportDataTable();  
			   $document = JFactory::getDocument();
			   $document->setBuffer($html, 'component');
		       echo $html;
		       exit;		
			} 
			else if($stage == 'createCSV'){
			   $this->export->createCSV($this->storagePath,$this->mysess);
			}
		}

		if($task == 'uploadFile'){
			//xitodo: y to use??
			JRequest::setVar('option','');
			
			if($stage == 'upload')
				$html = $this->import->getUploaderHtml();
				
			else if($stage == 'fieldMapping')
				$html = $this->import->getMappingHtml($this->mysess, $this->storagePath);
			
			else if($stage == 'importData')
				$html = $this->import->importData($this->mysess);
			
			else if($stage == 'createUser'){
				$importuser_count = $this->mysess->get('impexp_count',0);
				$html = $this->import->createUser($this->mysess, $this->storagePath, $importuser_count);				
			}
			
			else if($stage == 'complete')
			{
			    $mysess       = JFactory::getSession();
				$count        = $mysess->get('count',0);
                $discardCount = $mysess->get('discardCount',0);
                $icount       = $mysess->get('icount',0);
                $sizeCount    = $mysess->get('sizeCount',0);
                $replaceCount = $mysess->get('replaceCount',0);
				$html	=	$this->import->complete($count,$icount,$replaceCount,$sizeCount,$discardCount);
			}
						
			$document = JFactory::getDocument();
			$document->setBuffer($html, 'component');
			echo $html;
			echo JResponse::toString(JFactory::getApplication()->get('gzip'));
			exit;		
		}
	}	
}
