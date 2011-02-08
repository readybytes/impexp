<?php

if(!defined('_JEXEC')) die('Restricted access');

class UploadHTML 
{

	function _getUploaderHtml()
     {
		$currentUrl = JURI::getInstance()->toString();
		$this->_addUploaderScript();

		ob_start();
		?>

		<div style="padding:0;border:2px solid #ccc;">
			<div style="width:100%;background:#6699cc;font-size:16px;color:#fff;padding:7px 0;font-weight:bold;"><span style="margin-left:10px;">CSV Uploder</span></div>
				<form enctype="multipart/form-data"  action="<?php echo JRoute::_($currentUrl); ?>" method="post" name="adminForm" id="adminForm" >
					<div style="padding:20px 5px;">
					<div style="padding:20px 0; margin-bottom:10px; width:100%;font-size:18px;font-weight:bold;border-bottom:1px dotted #cfcfcf;"><?php echo JText::_('Please Upload the CSV File'); ?></div>
					<input type="file" id="fileUploaded" name="fileUploaded" title="Please Upload your CSV file" />
					<br /><br />
					<div style="padding:20px 0;margin-bottom:10px; width:100%;font-size:18px;font-weight:bold;border-bottom:1px dotted #cfcfcf;"><?php echo JText::_('You have Password in format of ');?> : </div>
					<select name="passwordFormat" >
						<option value="joomla">Joomla Encrypted</option>
						<option value="plain">Plain</option>			
					</select>
					<input type="hidden" name="importCSVStage" value="fieldMapping" />
					<br /><br />
					<input type="submit" name="btnUpload" value="Upload and Parse file" onclick="return importCSVFormCheck();" 	style="background:#6699cc; padding:5px 0;
					border:1px solid #6699cc;color:#fff;font-weight:bold;cursor:pointer;-webkit-border-radius: 5px;
					-moz-border-radius: 5px; border-radius: 5px;" />
					</div>
				</form>
			</div>
		<?php 
		$html = ob_get_contents();
		ob_clean();
		return $html;
	}
	
	function _addUploaderScript()
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
	
	function _addMappingScript($coulumn)
		{
			$index=0;
			ob_start();
			?>
				function importMappingCheck(){
					var element;
					var count=0;
					var str;
					var username='false';
					var pass='false';
					var email='false';
					while(1){
					 element= document.getElementById('csvField'+count);
					 if(element==null)
					 	break;
					 str = element.value.toLowerCase();
					 if(str=='joomla_username')
					 	username='true';
					 if(str=='joomla_email')
					 	email='true';
					 if(str=='joomla_password')
					 	pass='true';
					 count=count+1;
					}
					if(username!='true' || email!='true' || pass!='true'){				
						alert ('Username, Password or Email field is not map');
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
	
	function complete()
	{
		ob_start(); ?>
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
			$html = ob_get_contents();
			ob_clean();
			return $html;
	}
	
}