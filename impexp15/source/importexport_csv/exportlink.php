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
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementExportlink extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Export Link';

	function fetchElement($name, $value, &$node, $control_name)
	{			
		$html  = $this->getExportLink();

		return $html;
	}
	
	function getExportLink()
	{
		$link =JRoute::_('index.php?plugin=importexportCSV&task=export&tmpl=component', false);
        JHTML::_('behavior.modal', "a.exportPopup");
        $buttonMap = new JObject();
        $buttonMap->set('modal', true);
        $buttonMap->set('text', JText::_('EXPORT_USER_DATA'));
        $buttonMap->set('name', 'image');
        $buttonMap->set('modalname', 'exportPopup');
        $buttonMap->set('options', "{handler: 'iframe', size: {x: 400, y:180}}");
        $buttonMap->set('link', $link);
        
        $html = '<a id="'.$buttonMap->modalname.'" '
        	 	.' class="'.$buttonMap->modalname.'" '
        	 	.' title="'.$buttonMap->text.'" '
        	 	.' href ="'.$buttonMap->link.'" '
        	 	.' rel  ="'.$buttonMap->options.'" >'
        	 	.$buttonMap->text.' </a>';
        	 	$currentUrl=JURI::getInstance()->toString();

        return $html;
	}
}
