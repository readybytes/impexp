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

<script type="text/javascript">
	function importMappingCheck(userid){
		var element;
		var count=0;
		var str;
		var username='false';
		var pass='false';
		var email='false';
		var id ='false';
		while(1){
		 element= document.getElementById('csvField'+count);
		 if(element==null)
		 	break;
		 str = element.value.toLowerCase();
		 if(userid =='1' && str =='joomla_id'){
		    id ='true';
		 }
		 if(str =='joomla_username')
		 	username ='true';
		 if(str =='joomla_email')
		 	email ='true';
		 if(str =='joomla_password')
		 	pass ='true';
		 count=count+1;
		}
		if(userid=='1' && (username !='true' || email !='true' || 
				   			   pass !='true' || id !='true')){
				alert ('Id, Username, Password or Email field is not map');
				return false;
		}
		if(username!='true' || email!='true' || pass!='true'){		
			alert ('Username, Password or Email field is not map');
			return false;
		}
		return true;
					
	}
</script>

<div style="padding:0;border:2px solid #ccc;">
				<form action="<?php echo JRoute::_($currentUrl, false); ?>" method="post" name="adminForm" id="adminForm" >
					<div style="width:100%;background:#6699cc;font-size:16px;color:#fff;padding:7px 0;font-weight:bold;"><span style="margin-left:10px;"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_PLEASE_MAP_FIELDS_OF_CSV_IN_JOOMLA_SETUP'); ?></span></div>
				<div style="padding:0 10px;  font-size: 12px;">
					<ol>
						<li><?php echo JText::_('PLG_IMPORTEXPORT_CSV_THREE_FIELDS_MUST_EXIST'); ?></li>
						<li><?php echo JText::_('PLG_IMPORTEXPORT_CSV_THERE_MUST_BE_ONE_TO_ONE_MAPPING');?></li>						
						<li><?php echo JText::_('PLG_IMPORTEXPORT_CSV_DATE_FIELD_MUST_BE_IN_SQL_FORMAT');?>
                                                    <a href="http://joomlaxi.com/support/documentation/item/importing-user.html" target="_blank">
						    <?php echo JText::_('PLG_IMPORTEXPORT_CSV_CLICK_HERE');?></a><?php echo JText::_('PLG_IMPORTEXPORT_CSV_TO_SEE_FORMAT');?>
						</li>
                        <li><?php echo JText::_('PLG_IMPORTEXPORT_CSV_CSV_FILE_SHOULD_BE_IN_PROPER_FORMAT')?><a href="http://www.joomlaxi.com/support/documentation/item/importing-user.html" target="_blank"><?php echo JText::_('PLG_IMPORTEXPORT_CSV_CLICK_HERE_FOR_HELP');?></a></li>
					</ol>
					<br />
		<?php  
		foreach($columns as $c){
			?>
			<div style="min-height:40px;">				
				<div style="width:20%; float:left"><span><?php echo $c;?></span></div>
				<div style="width:70%; float:right">
					<select id="csvField<?php echo $index;?>" name="csvField<?php echo $index;?>">
						<option value=0><?php echo JText::_('PLG_IMPORTEXPORT_CSV_NONE');?></option>
						<?php echo $optionHtml;
						$index++;?>
					</select>
				</div>
				<div class='clr'></div>
			</div>			
			<br />
			<?php 
		}
		?>
		<input type="hidden" name="importCSVStage" value="importData" />
		<input type="submit" value="Import Data" onclick="return importMappingCheck('<?php echo $importUserId ?>');" style="background:#6699cc; padding:5px 0;
		border:1px solid #6699cc;color:#fff;font-weight:bold;cursor:pointer;-webkit-border-radius: 5px;
		-moz-border-radius: 5px; border-radius: 5px;" />	
		</div>
		</form>
		</div>
<?php 
