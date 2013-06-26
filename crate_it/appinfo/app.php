<?php

//add 3rdparty folder to include path
$dir = dirname(dirname(__FILE__)).'/3rdparty';
set_include_path(get_include_path() . PATH_SEPARATOR . $dir);

OC::$CLASSPATH['OCA\crate_it\lib\BagItManager'] = 'apps/crate_it/lib/bagit_manager.php';
OC::$CLASSPATH['BagIt'] = 'apps/crate_it/3rdparty/BagIt/bagit.php';
OC::$CLASSPATH['BagItManifest'] = 'apps/crate_it/3rdparty/BagIt/bagit_manifest.php';
OC::$CLASSPATH['BagItFetch'] = 'apps/crate_it/3rdparty/BagIt/bagit_fetch.php';

OC::$CLASSPATH['OCA\file_previewer\lib\Solr'] = 'apps/file_previewer/lib/solr.php';

//load the required files
OCP\Util::addscript( 'crate_it', 'loader');
OCP\Util::addscript('crate_it', 'crate');
OCP\Util::addscript('crate_it/3rdparty', 'jeditable/jquery.jeditable');
OCP\Util::addStyle('crate_it', 'crate');
//OCP\Util::addscript('crate_it', 'organiser');
//OCP\Util::addscript('crate_it/3rdparty', 'tree/jquery.tree.min');
//OCP\Util::addStyle('crate_it/3rdparty/js/themes/default', 'style');

OCP\App::addNavigationEntry( array( "id" => "crate",
									"order" => 250,
									"href" => OCP\Util::linkTo( "crate_it", "index.php" ),
									"icon" => OCP\Util::imagePath( "crate_it", "milk-crate-grey.png" ),
									"name" => "Cr8It" ));
