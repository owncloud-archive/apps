<?php

//add 3rdparty folder to include path
$dir = dirname(dirname(__FILE__)).'/3rdparty';
set_include_path(get_include_path() . PATH_SEPARATOR . $dir);

OC::$CLASSPATH['OCA\crate_it\lib\BagItManager'] = 'apps/crate_it/lib/bagit_manager.php';
OC::$CLASSPATH['BagIt'] = 'apps/crate_it/3rdparty/BagIt/bagit.php';
OC::$CLASSPATH['BagItManifest'] = 'apps/crate_it/3rdparty/BagIt/bagit_manifest.php';
OC::$CLASSPATH['BagItFetch'] = 'apps/crate_it/3rdparty/BagIt/bagit_fetch.php';

//load the required files
OCP\Util::addscript( 'crate_it', 'loader');

OCP\App::addNavigationEntry( array( "id" => "crate",
									"order" => 250,
									"href" => OCP\Util::linkTo( "crate_it", "index.php" ),
									"icon" => OCP\Util::imagePath( "crate_it", "milk-crate-grey.png" ),
									"name" => "Cr8It" ));
