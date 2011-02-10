<?php
if(!defined('_JEXEC')) die('Restricted access');

?>
<div style="overflow:hidden;width: 80%;margin: auto;">
					<div style="text-align: center;font-style: italic;font-size: 18px;color: #666;border-bottom: 1px solid #eee;">Except existing users, All Users have been imported successfully.
					</div>
				
					<div style="width:100%;margin:20px 0;text-align:center;">
						
						
							<a id='importeduser' href="<?php echo JURI::root().DS.'plugins'.DS.'system'.DS.'importexport_csv'.DS.'importuser.csv';?>" 
							style="color:#6699cc;font-weight:bold;cursor:pointer;font-weight:bold;font-size:14px;font-style:italic;">Download Imported users</a>
						
					</div>
				
					<div style="width:100%;margin:10px 0;text-align:center;">
						
						
							<a id='existuser' href="<?php echo JURI::root().DS.'plugins'.DS.'system'.DS.'importexport_csv' .DS.'existuser.csv'; ?>" 
							style="color:#6699cc;font-weight:bold;cursor:pointer;font-weight:bold;font-size:14px;font-style:italic;">Download Existing users(not imported)</a>
						
					</div>
				</div>

<?php 
