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

class JElementUploader extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Profiletypes';

	function fetchElement($name, $value, &$node, $control_name)
	{			
		$html  = $this->getUploaderLink();

		return $html;
	}
	
	function getUploaderLink()
	{
		$link =JRoute::_('index.php?plugin=importexportCSV&task=uploadFile&tmpl=component', false);
		JHTML::_('behavior.modal', "a.uploaderPopup");
        $buttonMap = new JObject();
        $buttonMap->set('modal', true);
        $buttonMap->set('text', JText::_('UPLOAD_FILE'));
        $buttonMap->set('name', 'image');
        $buttonMap->set('modalname', 'uploaderPopup');
        $buttonMap->set('options', "{handler: 'iframe', size: {x: 600, y: 470}}");
        $buttonMap->set('link', $link);
        
        $html = '<a id="'.$buttonMap->modalname.'" '
        	 	.' class="'.$buttonMap->modalname.'" '
        	 	.' title="'.$buttonMap->text.'" '
        	 	.' href ="'.$buttonMap->link.'" '
        	 	.' rel  ="'.$buttonMap->options.'" >'
        	 	.$buttonMap->text.' </a>';
        return $html;
	}
	
//	function getFileUploadHTML($name,$value,$control_name='params')
//	{	
//		$currentUrl = JURI::getInstance()->toString();
//		$this->_addScript();
//		$html   = '<form action="'.$currentUrl.'" method="post" name="adminForm" id="adminForm" >';
//		$html  .= '<input type="file" id="'.$name.'" name="'.$name.'" title="' . "Upload CSV File" . '::' . "Please Upload ypur CSV file" . '" />';
//		$html  .= '<input type="hidden" name="importCSVStage" value="fileUpload" />';
//		$html  .= '<input type="submit" name="btnUpload" value="Upload and Parse file" onclick="return importCSVFormCheck();" />';
//		$html  .= '</form>';		
//		return $html;
//	}
//	
/*	function _addScript()
	{
		ob_start();
		?>
		function importCSVFormCheck(){
			var file = document.getElementById('fileUploaded');
			var str = file.value.toLowerCase();
			var length = str.length;			
			if(str.slice(length-3, length) != 'csv'){
				alert('Please check the file Uploaded. It must be a CSV file.');
				return false;
			}			
			return true;
		}
		<?php 
		$content = ob_get_contents();
		ob_clean();
		
		$document = JFactory::getDocument()->addScriptDeclaration($content);
		return true;
	}
*/
}
