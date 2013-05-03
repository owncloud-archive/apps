<?php
//load the required files
OCP\Util::addscript( 'file_cart', 'loader');

OCP\App::addNavigationEntry( array( "id" => "cart",
									"order" => 250,
									"href" => OCP\Util::linkTo( "file_cart", "index.php" ),
									"icon" => OCP\Util::imagePath( "file_cart", "cart.jpg" ),
									"name" => "FileCart" ));
