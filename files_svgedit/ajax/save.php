<?php
/**
 * based on files_texteditor/ajax/savefile.php by
 * Tom Needham <contact@tomneedham.com> 2011
 */
// Init owncloud
require_once '../../../lib/base.php';


// Check if we are a user
OC_JSON::checkLoggedIn();

// Get paramteres
$filecontents = $_POST['file']['filecontents'];
$path = isset($_POST['file']['path']) ? $_POST['file']['path'] : '';
$mtime = isset($_POST['file']['mtime']) ? $_POST['file']['mtime'] : '';
$force = isset($_POST['force']) ? ($_POST['force'] == 'true') : false;
$b64encoded = isset($_POST['base64encoded']) ? ($_POST['base64encoded'] == 'true') : false;
if($b64encoded) {
	$b64type = isset($_POST['base64type']) ? $_POST['base64type'] : 'image/png';
} else {
    if(get_magic_quotes_gpc()) {
        $filecontents = stripslashes($filecontents);
    }
}

$pathParts = pathinfo($path);
$dir = $pathParts['dirname'];
$file = $pathParts['basename'];

if($path != '' && $mtime != '') {
    if(OC_Filesystem::file_exists($path)) {
        // Get file mtime
        $filemtime = OC_Filesystem::filemtime($path);
		if(!$force && $mtime != $filemtime) {
			if($mtime == 0) {
				$msg = "File already exists!";
			} else {
				$msg = "File has been modified since opening!";
			}
            OC_JSON::error(array("data" => array("message" => $msg)));
            //OC_Log::write('files_svgedit',"File: ".$path." modified since opening.",OC_Log::ERROR);
            exit();
        }
    } else {
        // file doesn't exist yet, so let's create it!
        if($file == '') {
            OC_JSON::error(array("data" => array( "message" => "Empty Filename") ));
            exit();
        }
		OC_Files::newFile($dir, '', 'dir');
		if(!OC_Files::newFile($dir, $file, 'file')) {
            OC_JSON::error(array("data" => array("message" => "Error when creating new file!")));
            OC_Log::write('files_svgedit', "Failed to create file: " . $path, OC_Log::ERROR);
            exit();
        }
    }
	// file should be existing now
	if(method_exists('OC_Filesystem', 'is_writable')) {
		$writable = OC_Filesystem::is_writable($path);
	} else {
		$writable = OC_Filesystem::is_writeable($path);
	}
	if($writable) {
		if($b64encoded) {
			$b64prefix = 'data:' . $b64type . ';base64,';
			if(strpos($filecontents, $b64prefix) === 0) {
				$filecontents = base64_decode(substr($filecontents, strlen($b64prefix)));
			}
		}
        OC_Filesystem::file_put_contents($path, $filecontents);
        // Clear statcache
        clearstatcache();
        // Get new mtime
        $newmtime = OC_Filesystem::filemtime($path);
        OC_JSON::success(array('data' => array('mtime' => $newmtime)));
    } else {
        // Not writable!
        OC_JSON::error(array('data' => array( 'message' => 'Insufficient permissions')));
        OC_Log::write('files_svgedit',"User does not have permission to write to file: ".$path,OC_Log::ERROR);
    }
} else {
	OC_JSON::error(array('data' => array( 'message' => 'File path or mtime not supplied')));
	OC_Log::write('files_svgedit',"Invalid path supplied:".$path,OC_Log::ERROR);	
}
