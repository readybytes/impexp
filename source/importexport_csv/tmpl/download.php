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
$currentUrl=JURI::getInstance();
?>
<div style="padding:0;border:2px solid #ccc;margin-top:30px;">
	    <div style="width:100%;background:#6699cc;font-size:16px;color:#fff;padding:8px 0;font-weight:bold;align:center"><span style="margin-left:10px;">Exporting CSV</span></div>
		 <form action="<?php echo JRoute::_($currentUrl)->toString(); ?>" method="post" name="adminForm" id="adminForm" >
	    	<div style="overflow:hidden;width: 80%;margin: auto;">
				<div style="text-align: center;font-style: italic;font-size: 18px;color: #666;border-bottom: 1px solid #eee;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DO_NOT_CLOSE_THIS_WINDOW_UNTIL_DOWNLOAD_LINK_IS_SHOWN'); echo "<br/><br/>";?>
				</div>
			</div>
		 </form>
	</div>
          