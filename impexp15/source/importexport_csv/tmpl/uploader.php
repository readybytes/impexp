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
JHTML::_('behavior.tooltip');

      	$db = JFactory::getDBO();
       	$enableOrNot = 'enabled';
       	$component = "#__extensions";
       	if(IMPEXP_JVERSION != '1.5'){
       		
       	$query  = 'SELECT '.$db->quoteName($enableOrNot)
                  .' FROM ' .$db->quoteName($component).
                  " WHERE `type` =". $db->Quote('component').
			      " AND `element`=". $db->Quote('com_community');
		}	
      
       	else{
            //joomla1.5+
            $component="#__components";
			$query  = 'SELECT '.$db->nameQuote($enableOrNot)
                  	 .' FROM ' .$db->nameQuote($component).
					 " WHERE `link` =". $db->Quote('option=com_community'). 
       	             " AND `option`=".  $db->Quote('com_community');
       	}
		    $db->setQuery($query);             
            $isInstalled= (boolean) $db->loadResult();
            $isInstalled = isset($isInstalled)? $isInstalled: '0';
            ob_start();
            ?>
             <script>
		    var isInstalled= <?php echo $isInstalled?>
		    </script>
            <?php 
           $html = ob_get_contents();
           ?>
<script>
function importCSVFormCheck(){
	var file = document.getElementById('fileUploaded');
	var str    = file.value.toLowerCase();
	var length = str.length;			
	if(str.search(".csv") == -1 && str.search(".zip") == -1 ){
		alert('Please check the file Uploaded. It must be a CSV file.');
		return false;
	}
	for (var i=0; i < document.adminForm.importDataTo.length; i++)
	   {
	   if (document.adminForm.importDataTo[i].checked)
	      {
	      var rad_val = document.adminForm.importDataTo[i].value;
	      if(rad_val=='Joomla' && isInstalled == 1){
		      var confirmMsg = confirm('<?php echo JText::_("Jomsocial is installed & you have choosen Joomla.This might create some conflicts.Click OK,to continue.")?>');
		      return confirmMsg;
	      }
	      }
	   }		
}
</script>
<div style="padding:0;border:2px solid #ccc;">
	<div style="width:100%;background:#6699cc;font-size:16px;color:#fff;padding:7px 0;font-weight:bold;"><span style="margin-left:10px;">CSV Uploder</span></div>
		<form enctype="multipart/form-data"  action="<?php echo JRoute::_($currentUrl); ?>" method="post" name="adminForm" id="adminForm" >
		<div style="padding:20px 5px;">
		<div style="padding:20px 0; margin-bottom:10px; width:100%;font-size:18px;font-weight:bold;border-bottom:1px dotted #cfcfcf;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_PLEASE_UPLOAD_THE_CSV_FILE'); ?></div>
		<input type="file" id="fileUploaded" name="fileUploaded" title="Please Upload your CSV or ZIP file" />
		<br/><p style="font-size:12px"><a  href= <?php echo  ImpexpPluginHelper::pathFS2URL(IMPEXP_BASE_URL.DS.'dummy.csv');?> ><?php echo JText::_('PLG_IMPORTEXPORT_CSV_CLICK_HERE')?></a><?php echo JText::_('PLG_IMPORTEXPORT_CSV_TO_SEE_FORMAT_OF_CSV_FILE')?></p>
		<br/>
		<div style="font-size:12px"><?php echo JText::_('CSV File Seperator: ')?><input type="text" name="seperator" class="hasTip" 
        title="Enter field seperator.For eg:-If Format is-<br/> 1. 'username','password',..' then add ( ',' ) as seperator <br/>2. username,password,.. then add (,) <br/>as seperator" style="width:50px;" value= '","'/>
		
		</div>
		<div style="border-bottom:1px dotted #cfcfcf;"><br/></div>
		<div><div style="padding:20px 0;margin-bottom:10px; width:33%;font-size:12px;float:left;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_YOU_HAVE_PASSWORD_IN_FORM_OF');?> : </div>
		   <div style="padding:20px 0;float:left; width:67%;font-size:12px;"><select name="passwordFormat" >
			<option value="joomla">Joomla Encrypted</option> 
			<option value="plain">Plain</option>			
		</select></div>
		<input type="hidden" name="importCSVStage" value="fieldMapping" />
        <br/><br/></div>
        
        <div  style="align:left;width:100%;overflow:hidden;font-size:12px; text-">
	    <?php echo JText::_(' Import users data to:-');?>
		<input type="radio"  name="importDataTo" value="Joomla">Joomla</input>
		<input type="radio"  name="importDataTo" value="JoomlaJS" checked>Joomla+Jomsocial</input>
		</div>
		<br />
        <div  style="align:center;width:100%;overflow:hidden;font-size:12px;">
	    <?php echo JText::_(' Do you want to overwrite users?');?>
		<input type="radio"  name="overwrite" value="0" checked>No</input>
		<input type="radio"  name="overwrite" value="1">Yes</input>
		</div>
		<br />
		<div  style="align:left;width:100%;overflow:hidden;font-size:12px;">
	    <?php echo JText::_('Do you want to import userid?');?>
		<input type="radio"  name="userid" value="0" checked="checked">No</input>
		<input type="radio"  name="userid" value="1">Yes</input>
		</div>
		<br /><br />
		<input type="submit" name="btnUpload" value="Upload and Parse file" onclick="return importCSVFormCheck();" 	style="background:#6699cc; padding:5px 0;
		border:1px solid #6699cc;color:#fff;font-weight:bold;cursor:pointer;-webkit-border-radius: 5px;
		-moz-border-radius: 5px; border-radius: 5px;" />
		</div>
		</form>
	</div>
<?php 


