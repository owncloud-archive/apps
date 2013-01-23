<?php
/**
 * Copyright (c) 2013 Lukas Reschke <lukas@statuscode.ch>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

// Set the content type to Javascript
header("Content-type: text/javascript");

// Disallow caching
header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 

$array = array(
	"totalurl" => "\"".OCP\Util::linkToRemote('carddav')."\"",
	"categories" => json_encode(OCA\Contacts\App::getCategories()),
	"id" => "\"".$_GET['id']."\"",
	"lang" => "\"".OCP\Config::getUserValue(OCP\USER::getUser(), 'core', 'lang', 'en')."\"",
	);

// Echo it
foreach ($array as  $setting => $value) {
	echo("var ". $setting ."=".$value.";\n");
}