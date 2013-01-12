<?php
/* creates a compressed zip file */
$filename = $_GET["filename"];
function create_zip($files = array(),$destination = '',$overwrite = false) {
	//if the zip file already exists and overwrite is false, return false
	if(file_exists($destination) && !$overwrite) { return false; }
	//vars
	$valid_files = array();
	//if files were passed in...
	if(is_array($files)) {
		//cycle through each file
		foreach($files as $file => $local) {
			//make sure the file exists
			if(file_exists($file)) {
				$valid_files[$file] = $local;
			}
		}
	}
	//if we have good files...
	if(count($valid_files)) {
		//create the archive
		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			return false;
		}

		//add the files
		foreach($valid_files as $file => $local) {
			$zip->addFile($file, $local);
		}

		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

		//close the zip -- done!
		$zip->close();

		//check to make sure the file exists
		return file_exists($destination);
	}
	else
	{
		return false;
	}
}

$files_to_zip = array(
	\OCP\Util::linkToAbsolute('impressionist', 'css/mappingstyle.css') => '/css/mappingstyle.css',
	\OCP\Util::linkToAbsolute('impressionist', 'css/player.css') => '/css/style.css',
	\OCP\Util::linkToAbsolute('', 'js/jquery-1.7.2.min.js') => '/js/jquery.js',
	\OCP\Util::linkToAbsolute('impressionist', "output/'.$filename.'.html")=> $filename.'.html'
);
//if true, good; if false, zip creation failed
$result = create_zip($files_to_zip, $filename.'.zip');
?>
<html lang="en">
<head>
    <meta charset="utf-8" />
     <title>Impressionist for ownCloud</title>
     <link rel="stylesheet" type="text/css" src="<?php echo \OCP\Util::linkToAbsolute('impressionist', 'css/bootstrap.css'); ?>"></script>
     <link rel="stylesheet" type="text/css" href="<?php echo \OCP\Util::linkToAbsolute('impressionist', 'css/mainstyle.css'); ?>" />
     <script type="text/javascript" src="<?php echo \OCP\Util::linkToAbsolute('', 'js/jquery-1.7.2.min.js'); ?>"></script>
     <script type="text/javascript" src="<?php echo \OCP\Util::linkToAbsolute('impressionist', 'js/bootstrap.js'); ?>"></script>
     <script>
         function closewindow() {
              close();
         }
     </script>
 </head>
 <body>
    <div id="hero">
       <div class="hero-unit" style="position:absolute; width:800px; text-align:center; left: 25%;top:30%; font-family:'Open Sans', serif; border: 1px dotted #0ca4eb;">
           <h1>Congrats! You are all set.</h1>
           <p>Filename: <?php echo $filename.".zip"?> </p>
           <p>
             <button class="btn btn-info btn-large" onclick="closewindow()">Close this Window</button>
           </p>
       </div>
   </div>

</body>

