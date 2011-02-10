<?php

class ImpexpPluginImport
{
	function getUploaderHtml()
		{
			$currentUrl = JURI::getInstance()->toString();		

			ob_start();
			require_once(JPATH_ROOT .DS. 'plugins' .DS. 'system' .DS. 'importexport_csv' .DS. 'tmpl' .DS. 'uploader.php');
			$html = ob_get_contents();
			ob_clean();

			return $html;
		}		
}