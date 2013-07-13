<?php

if ( ! defined('USERPHONE_ADDON_NAME'))
{
	define('USERPHONE_ADDON_NAME',         'UserPhone');
	define('USERPHONE_ADDON_VERSION',      '0.1.0');
}

$config['name']=USERPHONE_ADDON_NAME;
$config['version']=USERPHONE_ADDON_VERSION;

$config['nsm_addon_updater']['versions_xml']='http://www.intoeetive.com/index.php/update.rss/291';