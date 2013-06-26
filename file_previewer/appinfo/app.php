<?php
//add 3rdparty folder to include path
$dir = dirname(dirname(__FILE__)).'/3rdparty';
set_include_path(get_include_path() . PATH_SEPARATOR . $dir);

//load the required files
OCP\Util::addscript('file_previewer', 'loader');

OC::$CLASSPATH['Apache_Solr_Service'] = 'apps/file_previewer/3rdparty/SolrPhpClient/Apache/Solr/Service.php';
OC::$CLASSPATH['OCA\file_previewer\lib\Solr'] = 'apps/file_previewer/lib/solr.php';
