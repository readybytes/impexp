<?php
/**
* @Copyright Ready Bytes Software Labs Pvt. Ltd. (C) 2010- author-Team Joomlaxi
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
if (!defined('DS'))
  {
     define('DS', DIRECTORY_SEPARATOR);
  }
require_once(dirname(__FILE__)  .DS. 'elements' .DS. 'field.php');
require_once(dirname(__FILE__)  .DS. 'elements' .DS. 'impexpElement.php');
jimport('joomla.html.parameter.element');
class JElementUploader extends impexpElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	public $type = "Uploader";

	function fetchElement($name, $value, &$node, $control_name)
	{			
		$html  = self::getUploaderLink();

		return $html;
	}
	
	function getUploaderLink()
	{
		$link =JRoute::_('index.php?plugin=importexportCSV&task=uploadFile&tmpl=component', false);
		JHTML::_('behavior.modal', "a.uploaderPopup");
        $buttonMap = new JObject();
        $buttonMap->set('modal', true);
        $buttonMap->set('text', JText::_('PLG_IMPORTEXPORT_CSV_UPLOAD_FILE'));
        $buttonMap->set('name', 'image');
        $buttonMap->set('modalname', 'uploaderPopup');
        $buttonMap->set('options', "{handler: 'iframe', size: {x: 610, y: 550}}");
        $buttonMap->set('link', $link);
        
        $html = '<a style="font-size:12px;font-weight:bold;position:relative; line-height:25px;"
        			id="'.$buttonMap->modalname.'" '
        	 	.' class="'.$buttonMap->modalname.'" '
        	 	.' title="'.$buttonMap->text.'" '
        	 	.' href ="'.$buttonMap->link.'" '
        	 	.' rel  ="'.$buttonMap->options.'" >'
        	 	.$buttonMap->text.' </a>';
        return $html;
	}
}

class JFormFieldUploader extends impexpField
{
	public $type='uploader';
	function getInput()
	{			
		$html  = self::getUploaderLink();

		return $html;
	}
	
	function getUploaderLink()
		{
			$link =JRoute::_('index.php?plugin=importexportCSV&task=uploadFile&tmpl=component', false);
			JHTML::_('behavior.modal', "a.uploaderPopup");
	        $buttonMap = new JObject();
	        $buttonMap->set('modal', true);
	        $buttonMap->set('text', JText::_('PLG_IMPORTEXPORT_CSV_UPLOAD_FILE'));
	        $buttonMap->set('name', 'image');
	        $buttonMap->set('modalname', 'uploaderPopup');
	        $buttonMap->set('options', "{handler: 'iframe', size: {x: 610, y: 558}}");
	        $buttonMap->set('link', $link);
	        
	        $html = '<a style="font-size:12px;font-weight:bold;position:relative; line-height:25px;"
	        			id="'.$buttonMap->modalname.'" '
	        	 	.' class="'.$buttonMap->modalname.'" '
	        	 	.' title="'.$buttonMap->text.'" '
	        	 	.' href ="'.$buttonMap->link.'" '
	        	 	.' rel  ="'.$buttonMap->options.'" >'
	        	 	.$buttonMap->text.' </a>';
	        return $html;
		}
}
