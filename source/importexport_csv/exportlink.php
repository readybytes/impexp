<?php
/**
* @Copyright Ready Bytes Software Labs Pvt. Ltd. (C) 2010- author-Team Joomlaxi
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
class JFormFieldExportlink extends JFormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	public $type = 'Export Link';

	protected function getInput()
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
        $buttonMap->set('text', JText::_('PLG_IMPORTEXPORT_CSV_EXPORT_USER_DATA'));
        $buttonMap->set('name', 'image');
        $buttonMap->set('modalname', 'exportPopup');
        $buttonMap->set('options', "{handler: 'iframe', size: {x: 400, y:180}}");
        $buttonMap->set('link', $link);
        
        $html = '<a style="font-size:12px;font-weight:bold;position:relative; top:14px;"
        			id="'.$buttonMap->modalname.'" '
        	 	.' class="'.$buttonMap->modalname.'" '
        	 	.' title="'.$buttonMap->text.'" '
        	 	.' href ="'.$buttonMap->link.'" '
        	 	.' rel  ="'.$buttonMap->options.'" >'
        	 	.$buttonMap->text.' </a>';
        return $html;
	}
}
