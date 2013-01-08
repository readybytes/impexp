<?php
// no direct access
if(!defined('_JEXEC')) die('Restricted access');
?>

<div style="padding:0;border:1px solid #ccc;margin-top:10px;height:180px; ">
	    <div style="width:100%;background:#6699cc;font-size:16px;color:#fff;padding:5px 0;font-weight:bold;align:center"><span style="margin-left:10px;">Exporting CSV</span></div>
	<form enctype="multipart/form-data"  action="<?php echo JRoute::_($currentUrl.'&writeFields=1'); ?>" method="post" name="tableName" id="tableName"  >
		<input type="hidden" name="importCSVStage" value="createCSV" />
	    <div style="align:center;width:100%;overflow:hidden;padding:15px 0;font-size:12px;">
	        <?php echo JText::_('PLG_IMPORTEXPORT_EXPORT_USER_DATA_FROM');?>
		<input type="radio"  name="exportDataFrom" id="Joomla" value="Joomla">Joomla </input>
		<input type="radio"  name="exportDataFrom" id="JoomlaJs" value="JoomlaJS" checked >Joomla+Jomsocial</input>
            </div>
		<div style="align:center;width:100%;overflow:hidden;padding:10px 0;font-size:12px;" class="hasTip" title='Choose the separator, used to separate the exported data. Eg:-If comma(,) is used, then exported data will be "username","password"...'>
		  <?php echo JText::_('Export Separator :-');?>
		  <input maxlength="7" type="text"  name="exportSeparator" value=','></input>
		 </div>
	    <div align="center">
		<input type="submit" name="formbutton" value="Export Data" style="background:#6699cc; padding:3px 0;
		border:1px solid #6699cc;color:#fff;font-weight:bold;cursor:pointer;-webkit-border-radius: 5px;
		-moz-border-radius: 5px; border-radius: 5px;" />
	    </div>
	</form>
</div>
