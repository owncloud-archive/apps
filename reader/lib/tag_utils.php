<?php
function find_tags_for_ebook($path_of_ebook) {
	$sql = 'SELECT `tags` FROM `*PREFIX*eBook_library` WHERE `filepath` = ?';
	$stmt = OCP\DB::prepare($sql);
	$res =  $stmt->execute(array($path_of_ebook));
	$tags = NULL;
	while($r = $res->fetchRow()) {
		$tags = $r['tags'];
	}
	return $tags;
}

function update_tag_for_ebook($new_tag,$path_of_ebook) {
	$tags = find_tags_for_ebook($path_of_ebook);
	$each_tag = explode(",",$tags);
	if (count($each_tag) < 5) {
		$stmt = OCP\DB::prepare("UPDATE `*PREFIX*eBook_library` SET `tags` = ? WHERE `filepath` = ?");
		$stmt->execute(array($new_tag,$path_of_ebook));
	}
	else
		return;
}

function insert_new_tag($new_tag,$path_of_ebook) {
	$stmt = OCP\DB::prepare('INSERT INTO `*PREFIX*eBook_library` (`filepath`,`tags`) VALUES (?, ?)');
	$stmt->execute(array($path_of_ebook,$new_tag));
}

function find_results_with_tag_like($tag) {
	$sql = 'SELECT * FROM `*PREFIX*eBook_library` WHERE `tags` LIKE ?';
	$stmt = OCP\DB::prepare($sql);
	$res =  $stmt->execute(array($tag));
	return $res;
}

function check_consistency_with_database($root,$pdfs) {
	$new_array_pdfs = array();
	foreach ($pdfs as $pdf) {
		$new_array_pdfs[] = $root.$pdf;
	}
	$sql = 'SELECT `filepath` from `*PREFIX*ebook_library`';
	$stmt = OCP\DB::prepare($sql);
	$res =  $stmt->execute();
	while ($r = $res->fetchRow()) {
		if (!in_array($r['filepath'],$new_array_pdfs))
			delete_entry($r['filepath']);
	}
}

function delete_entry($filepath) {
	$sql = 'DELETE FROM `*PREFIX*eBook_library` WHERE filepath = ?';
	$stmt = OCP\DB::prepare($sql);
	$res =  $stmt->execute(array($filepath));
}

?>
