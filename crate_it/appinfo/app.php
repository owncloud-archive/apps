<?php
//load the required files
OCP\Util::addscript( 'crate_it', 'loader');

OCP\App::addNavigationEntry( array( "id" => "crate",
									"order" => 250,
									"href" => OCP\Util::linkTo( "crate_it", "index.php" ),
									"icon" => OCP\Util::imagePath( "crate_it", "milk-crate-grey.png" ),
									"name" => "Cr8It" ));
