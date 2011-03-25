<?php
if(!defined('_JEXEC')) die('Restricted access');

?>
<div style="overflow:hidden;width: 80%;margin: auto;">
					<div style="text-align: center;font-style: italic;font-size: 18px;color: #666;border-bottom: 1px solid #eee;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_EXCEPT_EXISTING_USERS_ALL_USER_IMPORTED_SUCCESSFULLY'); ?>
					</div>
				
					<div style="width:100%;margin:20px 0;text-align:center;">
						
						
							<a id='importeduser' href="<?php echo JURI::root().'plugins/system/importexport_csv/importuser.csv';?>" 
							style="color:#6699cc;font-weight:bold;cursor:pointer;font-weight:bold;font-size:14px;font-style:italic;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DOWNLOAD_IMPORTED_USERS'); ?></a>
				</div>
					<div style="width:100%;margin:10px 0;text-align:center;">
						
						
							<a id='existuser' href="<?php echo JURI::root().'plugins/system/importexport_csv/existuser.csv'; ?>" 
							style="color:#6699cc;font-weight:bold;cursor:pointer;font-weight:bold;font-size:14px;font-style:italic;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DOWNLOAD_EXISTING_USERS'); ?></a>
						
					</div>
				</div>

<?php 
