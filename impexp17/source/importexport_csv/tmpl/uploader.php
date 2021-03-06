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
<script>
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
</script>
<div style="padding:0;border:2px solid #ccc;">
	<div style="width:100%;background:#6699cc;font-size:16px;color:#fff;padding:7px 0;font-weight:bold;"><span style="margin-left:10px;">CSV Uploder</span></div>
		<form enctype="multipart/form-data"  action="<?php echo JRoute::_($currentUrl); ?>" method="post" name="adminForm" id="adminForm" >
		<div style="padding:20px 5px;">
		<div style="padding:20px 0; margin-bottom:10px; width:100%;font-size:18px;font-weight:bold;border-bottom:1px dotted #cfcfcf;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_PLEASE_UPLOAD_THE_CSV_FILE'); ?></div>
		<input type="file" id="fileUploaded" name="fileUploaded" title="Please Upload your CSV file" />
		<br/><br/><p style="font-size:12px"><a  href= <?php echo JURI::root().'plugins/system/importexport_csv/importexport_csv/dummy.csv'?>><?php echo JText::_('PLG_IMPORTEXPORT_CSV_CLICK_HERE')?></a><?php echo JText::_('PLG_IMPORTEXPORT_CSV_TO_SEE_FORMAT_OF_CSV_FILE')?></p>
		<div style="padding:20px 0;margin-bottom:10px; width:100%;font-size:18px;font-weight:bold;border-bottom:1px dotted #cfcfcf;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_YOU_HAVE_PASSWORD_IN_FORM_OF');?> : </div>
		<select name="passwordFormat" >
			<option value="joomla">Joomla Encrypted</option>
			<option value="plain">Plain</option>			
		</select>
		<input type="hidden" name="importCSVStage" value="fieldMapping" />
		<br /><br />
		<?php echo JText::_('Do you want to overwrite users');?>
		<input type="radio"  name="overwrite" value="0" checked="true">No</input>
		<input type="radio"  name="overwrite" value="1">Yes</input>
		<br /><br />
		<input type="submit" name="btnUpload" value="Upload and Parse file" onclick="return importCSVFormCheck();" 	style="background:#6699cc; padding:5px 0;
		border:1px solid #6699cc;color:#fff;font-weight:bold;cursor:pointer;-webkit-border-radius: 5px;
		-moz-border-radius: 5px; border-radius: 5px;" />
		</div>
		</form>
	</div>
<?php 
