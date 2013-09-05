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

?>
<div style="overflow:hidden;width: 80%;margin: auto;">
					<div style="text-align: center;font-style: italic;font-size: 18px;color: #666;border-bottom: 1px solid #eee;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_EXCEPT_EXISTING_USERS_ALL_USER_IMPORTED_SUCCESSFULLY'); ?>
					</div>
				<?php //count for import
					if($icount != 0):?>
					<div style="width:100%;margin:20px 0;text-align:center;">
						
						
							<a id='importeduser' href="<?php echo JURI::root().'cache'.DS.'importuser.csv';?>" 
							style="color:#6699cc;font-weight:bold;cursor:pointer;font-weight:bold;font-size:14px;font-style:italic;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DOWNLOAD_IMPORTED_USERS'); ?></a>
				</div>
				<?php endif;
				//count for export
				if($count != 0):
				?>
					<div style="width:100%;margin:20px 0;text-align:center;">
						
						
							<a id='existuser' href="<?php echo JURI::root().'cache'.DS.'existuser.csv'; ?>" 
							style="color:#6699cc;font-weight:bold;cursor:pointer;font-weight:bold;font-size:14px;font-style:italic;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DOWNLOAD_EXISTING_USERS'); ?></a>
						
					</div>
					<?php endif;
					if($discardCount != 0):
					?>
					<div style="width:100%;margin:20px 0;text-align:center;">
							<a id='discardusers' href="<?php echo JURI::root().'cache'.DS.'discardusers.csv'; ?>" 
							style="color:#6699cc;font-weight:bold;cursor:pointer;font-weight:bold;font-size:14px;font-style:italic;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DOWNLOAD_DISCARD_USERS'); ?></a>                     
					</div>
					<?php endif;

					if($replaceCount != 0):
					?>
					<div style="width:100%;margin:20px 0;text-align:center;">
							<a id='replaceusers' href="<?php echo JURI::root().'cache'.DS.'replaceuser.csv'; ?>" 
							style="color:#6699cc;font-weight:bold;cursor:pointer;font-weight:bold;font-size:14px;font-style:italic;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DOWNLOAD_REPLACED_USERS'); ?></a>
					</div>
					<?php endif;

					if($sizeCount!=0):?>
					<div style="width:100%;margin:20px 0;text-align:center;">
							<a id='sizediscardusers' href="<?php echo JURI::root().'cache'.DS.'sizediscarduser.csv'; ?>" 
							style="color:#6699cc;font-weight:bold;cursor:pointer;font-weight:bold;font-size:14px;font-style:italic;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DOWNLOAD_SIZE_DISCARD_USERS'); ?></a>
					</div>
				<?php endif;?>
				</div>

<?php 

