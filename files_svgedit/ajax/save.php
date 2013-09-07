<?php
/**
 * based on files_texteditor/ajax/savefile.php by
 * Tom Needham <contact@tomneedham.com> 2011
 */
// Init owncloud
require_once '../../../lib/base.php';


// Check if we are a user
OCP\JSON::checkLoggedIn();

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
    if(\OC\Files\Filesystem::file_exists($path)) {
        // Get file mtime
        $filemtime = \OC\Files\Filesystem::filemtime($path);
		if(!$force && $mtime != $filemtime) {
			if($mtime == 0) {
				$msg = "File already exists!";
			} else {
				$msg = "File has been modified since opening!";
			}
            OCP\JSON::error(array("data" => array("message" => $msg)));
            //OCP\Util::writeLog('files_svgedit',"File: ".$path." modified since opening.",OC_Log::ERROR);
            exit();
        }
    } else {
        // file doesn't exist yet, so let's create it!
        if($file == '') {
            OCP\JSON::error(array("data" => array( "message" => "Empty Filename") ));
            exit();
        }
		\OC\Files\Filesystem::mkdir($dir);
		if(!\OC\Files\Filesystem::touch($dir . '/' . $file)) {
            OCP\JSON::error(array("data" => array("message" => "Error when creating new file!")));
            OCP\Util::writeLog('files_svgedit', "Failed to create file: " . $path, OC_Log::ERROR);
            exit();
        }
    }
	// file should be existing now
	$writable = \OC\Files\Filesystem::isUpdatable($path);

	if($writable) {
		if($b64encoded) {
			$b64prefix = 'data:' . $b64type . ';base64,';
			if(strpos($filecontents, $b64prefix) === 0) {
				$filecontents = base64_decode(substr($filecontents, strlen($b64prefix)));
			}
		}
		\OC\Files\Filesystem::file_put_contents($path, $filecontents);
        // Clear statcache
        clearstatcache();
        // Get new mtime
        $newmtime = \OC\Files\Filesystem::filemtime($path);
        OCP\JSON::success(array('data' => array('mtime' => $newmtime)));
    } else {
        // Not writable!
        OCP\JSON::error(array('data' => array( 'message' => 'Insufficient permissions')));
        OCP\Util::writeLog('files_svgedit',"User does not have permission to write to file: ".$path,OC_Log::ERROR);
    }
} else {
	OCP\JSON::error(array('data' => array( 'message' => 'File path or mtime not supplied')));
	OCP\Util::writeLog('files_svgedit',"Invalid path supplied:".$path,OC_Log::ERROR);	
}
