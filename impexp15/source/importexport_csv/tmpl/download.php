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
//$currentUrl=JURI::getInstance();
?>
<style type="text/css">
.div1
{
text-align: center;
margin-top:12px;
font-style: italic;
font-size: 15px;
color: #666;
border-bottom: 1px solid #eee;
}
.div2
{
text-align: center;
margin-top:10px;
font-style: italic;
font-size: 15px;
color: #666;
}
</style>
<div style="padding:0;border:1px solid #ccc;margin-top:10px;height:160px; ">
	    <div style="width:100%;background:#6699cc;font-size:16px;color:#fff;padding:5px 0;font-weight:bold;align:center"><span style="margin-left:10px;">Exporting CSV</span></div>
		 <form action="<?php echo JRoute::_($currentUrl); ?>" method="post" name="adminForm" id="adminForm" >
	    	<div style="overflow:hidden;width: 90%;margin: auto;">
				<div class="div1"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DO_NOT_CLOSE_THIS_WINDOW_UNTIL_DOWNLOAD_LINK_IS_SHOWN');?> <br/><br/>
				</div>
				 <div class="div2"><font color="red"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_WARNING');?></font><?php echo JText::_('PLG_IMPORTEXPORT_CSV_ON_EXPORT_FILE_CHOOSE_UTF8_FORMAT');?> <br/><br/>
				</div>
			</div>
		 </form>
	</div>
          