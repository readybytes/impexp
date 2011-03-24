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
		$text = JText::_('Export User Data');        
        
        $html = '<a id="exportPopup" href ="'.$link.'" >'
                .$text
        	 	.'</a>';
        return $html;
	}
}
