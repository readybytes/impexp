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
		$text = JText::_('PLG_IMPORTEXPORT_CSV_EXPORT_USER_DATA');        
        
        $html = '<a style="font-size:12px;font-weight:bold;position:relative; top:16px;" 
        		id="exportPopup" href ="'.$link.'" >'
                .$text
        	 	.'</a>';
        return $html;
	}
}
