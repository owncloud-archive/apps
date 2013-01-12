<div id = "controls">
</div>

<div class="actions"></div>

<?php
	include('apps/reader/lib/dir.php');
	include('apps/reader/lib/thumbnail.php');
	include('apps/reader/lib/library_display.php');
	include('apps/reader/lib/tag_utils.php');
	$tag = "%".$_['tag']."%";
	$res = find_results_with_tag_like($tag);
?>
<table id = "readerContent">
	<tbody id = "fileList">
		<?php
			while($r = $res->fetchRow()) {
				$dirname = dirname($r['filepath']);
				if ($dirname != '/') {
					$dirname = $dirname.'/'; 
				}
				display_ebooks(basename($r['filepath']),$dirname);
			}
		?>
</tbody>
</table>
