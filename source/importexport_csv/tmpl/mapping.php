<?php
if(!defined('_JEXEC')) die('Restricted access');

?>

<script type="text/javascript">
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
</script>

<div style="padding:0;border:2px solid #ccc;">
				<form action="<?php echo JRoute::_($currentUrl, false); ?>" method="post" name="adminForm" id="adminForm" >
					<div style="width:100%;background:#6699cc;font-size:16px;color:#fff;padding:7px 0;font-weight:bold;"><span style="margin-left:10px;"><?php echo JText::_('Please map the fields of CSV files to Your Joomla setup.'); ?></span></div>
				<div style="padding:0 10px;">
					<ol>
						<li>Three fields must exists Username, Password, Email for Joomla User Table Fields.</li>
						<li>There must be one to one mapping, one field must be selected for one field of your joomla setup.</li>						
						<li>Date fields in CSV file must be in SQL date formate.</li>
					</ol>			
					<br />
		<?php  
		foreach($columns as $c){
			$c = JString::str_ireplace('"', '', $c);		
			?>
			<div>				
				<div style="width:20%; float:left"><span><?php echo $c;?></span></div>
				<div style="width:70%; float:right">
					<select id="csvField<?php echo $index;?>" name="csvField<?php echo $index;?>">
						<option value=0><?php echo JText::_('None');?></option>
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
		<input type="submit" value="Import Data" onclick="return importMappingCheck();" style="background:#6699cc; padding:5px 0;
		border:1px solid #6699cc;color:#fff;font-weight:bold;cursor:pointer;-webkit-border-radius: 5px;
		-moz-border-radius: 5px; border-radius: 5px;" />	
		</div>
		</form>
		</div>
<?php 
