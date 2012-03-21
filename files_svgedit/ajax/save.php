<?php
/**
 * based on files_texteditor/ajax/savefile.php by
 * Tom Needham <contact@tomneedham.com> 2011
 */
// Init owncloud
require_once('../../../lib/base.php');


// Check if we are a user
OC_JSON::checkLoggedIn();

// Get paramteres
$filecontents = htmlspecialchars_decode($_POST['filecontents']);
$path = isset($_POST['path']) ? $_POST['path'] : '';
$mtime = isset($_POST['mtime']) ? $_POST['mtime'] : '';


if($path != '' && $mtime != '')
{
	// Get file mtime
	$filemtime = OC_Filesystem::filemtime($path);
	if($mtime != $filemtime)
	{
		// Then the file has changed since opening
		OC_JSON::error();
		OC_Log::write('files_svgedit',"File: ".$path." modified since opening.",OC_Log::ERROR);	
	}
	else
	{
		// File same as when opened
		// Save file
		if(OC_Filesystem::is_writeable($path))	
		{
			OC_Filesystem::file_put_contents($path, $filecontents);
			// Clear statcache
			clearstatcache();
			// Get new mtime
			$newmtime = OC_Filesystem::filemtime($path);
			OC_JSON::success(array('data' => array('mtime' => $newmtime)));
		}
		else
		{
			// Not writeable!
			OC_JSON::error(array('data' => array( 'message' => 'Insufficient permissions')));	
			OC_Log::write('files_svgedit',"User does not have permission to write to file: ".$path,OC_Log::ERROR);
		}
	}
} else {
	OC_JSON::error(array('data' => array( 'message' => 'File path or mtime not supplied')));
	OC_Log::write('files_svgedit',"Invalid path supplied:".$path,OC_Log::ERROR);	
}
?>
