<?php
	include ('reader/lib/tag_utils.php');
	$filepath = $_POST['filepath'];
	$tag_toBeRemv = $_POST['tag'];
	$tags = find_tags_for_ebook($filepath);
	
	$arr = explode(',',$tags);
	$arr2 = array();
	foreach($arr as $a) {
		if (strcmp($a,$tag_toBeRemv) != 0)
			$arr2[] = $a; 
	}
	$new_tags = implode(",",$arr2);
	update_tag_for_ebook($new_tags,$filepath);

?>
