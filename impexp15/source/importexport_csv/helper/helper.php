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

class ImpexpPluginHelper
{
	 //get the fields of the desired table
	public static function getJsJoomlaField($table)
		    {
		        $db = JFactory::getDBO();
		    	$conf = JFactory::getConfig();
		    	if(IMPEXP_JVERSION === '1.5')
		    	{
				$database = $conf->getValue('config.db');
		    	}
		    	else{ 
		    		$database = $conf->get('db');
		    	}
		             $tableName = self::replacePrefix($table);
		             $sql="SELECT column_name FROM information_schema.columns
		                   WHERE table_name = '$tableName'
		                   AND table_schema = '$database'";
		            $db->setQuery($sql); 
				 if(IMPEXP_JVERSION === '1.5'){
				    	 $joomlaField_name =$db->loadResultArray();
				  }
				  else{ 
				    	 $joomlaField_name =$db->loadColumn();
						// to append the usertype to the array if it does not exist.
				    	 if($table == '#__users' && !in_array( 'usertype', $joomlaField_name))
				    	 array_push($joomlaField_name, 'usertype');
				   }
		            return $joomlaField_name;
		    }

	 /**
	   *replace the prefix of the table and add prefix(that is used by the current database) to tablename.
       */
     public static function replacePrefix($table)
       {   
           if(substr($table,0,3) == '#__')
             {
                  $tablePrefix = JFactory::getDBO()->getPrefix();
                  $table = $tablePrefix.substr($table,3);
             }
            return $table;
       }
       
       //determine whether jomsocial is enabled or not.
       public static function jomsocialEnabled()
       {
		   	$db = JFactory::getDBO();
		   	$component = ImpexpPluginHelper::findTableName("#__components");
            //for joomla1.5
		   	$condition = " WHERE `link` = ". $db->Quote('option=com_community'). 
		   	             " AND `option`= ".  $db->Quote('com_community');
			$enableOrNot = 'enabled';
            //for joomla1.5+
			if(IMPEXP_JVERSION != '1.5'){
				$component = ImpexpPluginHelper::findTableName("#__extensions");
				$condition = "  WHERE `type` =". $db->Quote('component').
					         " AND `element`= ". $db->Quote('com_community');
			}	
			$query  = 'SELECT `'.$enableOrNot.'`'
                      .' FROM ' .$component.$condition;
		    $db->setQuery($query);             
            $isInstalled= (boolean) $db->loadResult();
            return $isInstalled;
		}
	
       static function pathFS2URL($fsPath='')
       {    
       	// get reference path from root
       	    if(IMPEXP_JVERSION === '1.5'){
               $urlPath        = JString::str_ireplace( JPATH_ROOT .DS , '', $fsPath);
       	    }
       	    else
              $urlPath        = self::str_ireplace( JPATH_ROOT .DS , '', $fsPath);
               // replace all DS to URL-slash
               $urlPath        = JPath::clean($urlPath, '/');
               
               // prepend URL-root
               return JURI::root().$urlPath;
       }
       
       /**
        * Clonning function Due to bug in utf8_ireplace function
        */
       static public function str_ireplace($search, $replace, $str, $count = NULL)
       {
               
               if ( !is_array($search) ) {
       
               $slen = strlen($search);
               if ( $slen == 0 ) {
                   return $str;
               }
       
               $lendif = strlen($replace) - strlen($search);
               $search = utf8_strtolower($search);
       
               $search = preg_quote($search,"/");
               $lstr = utf8_strtolower($str);
               $i = 0;
               $matched = 0;
               while ( preg_match('/(.*)'.$search.'/Us',$lstr, $matches) ) {
                   if ( $i === $count ) {
                       break;
                   }
                   $mlen = strlen($matches[0]);
                   $lstr = substr($lstr, $mlen);
                   $str = substr_replace($str, $replace, $matched+strlen($matches[1]), $slen);
                   $matched += $mlen + $lendif;
                   $i++;
               }
               return $str;
       
           } else {
       
               foreach ( array_keys($search) as $k ) {
       
                   if ( is_array($replace) ) {
       
                       if ( array_key_exists($k,$replace) ) {
       
                           $str = utf8_ireplace($search[$k], $replace[$k], $str, $count);
       
                       } else {
       
                           $str = utf8_ireplace($search[$k], '', $str, $count);
       
                       }
       
                   } else {
       
                       $str = utf8_ireplace($search[$k], $replace, $str, $count);
       
                   }
               }
               return $str;
       
           }
       }
       
       static public function findTableName($tableName)
       {
        	$db          = JFactory::getDBO();
	        if(IMPEXP_JVERSION === '1.5')
	        {
	        	return $db->nameQuote($tableName);
	        }
	        else {
	        	return $db->quoteName($tableName);
	        	
	        }
       }
}
