<?php
    // Get the data
   $imageData=$_POST['canv_data'];	
   $title = rtrim($_POST['title'],'pdf');
   $location = urldecode(dirname($_POST['location']));
  
   if($location != '/')
	$location = $location.'/';
    $filteredData=substr($imageData, strpos($imageData, ",")+1);
    $owner = OCP\USER::getUser();
	$save_dir = OCP\Config::getSystemValue("datadirectory").'/'. $owner .'/reader';	
    $save_dir .= $location;
	$thumb_file = $save_dir . $title;
	if (!is_dir($save_dir)) {		
		mkdir($save_dir, 0777, true);
	}
	$image = new OC_Image($filteredData);
	if ($image->valid()) {
		$image->centerCrop(100);
		$image->fixOrientation();
		$image->save($thumb_file.'png');
	}
?>
