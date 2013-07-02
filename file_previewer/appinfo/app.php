<?php
//add 3rdparty folder to include path
$dir = dirname(dirname(__FILE__)).'/3rdparty';
set_include_path(get_include_path() . PATH_SEPARATOR . $dir);

//load the required files
OCP\Util::addscript('file_previewer', 'loader');

OC::$CLASSPATH['Apache_Solr_Service'] = 'apps/file_previewer/3rdparty/SolrPhpClient/Apache/Solr/Service.php';
OC::$CLASSPATH['OCA\file_previewer\lib\Solr'] = 'apps/file_previewer/lib/solr.php';

//create the configuration file in data directory with default values
$config_file = \OC::$SERVERROOT.'/data/cr8it_config.json';
if(!file_exists($config_file)){
	$fp = fopen($config_file, 'x');
	$entry = array("fascinator" => array("downloadURL" => "http://localhost:9997/portal/default/download/",
								"solr" => array("host" => "localhost", "port" => 9997, "path" => "/solr/fascinator/")));
	fwrite($fp, json_encode($entry));
	fclose($fp);
}
