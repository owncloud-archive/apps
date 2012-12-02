<div id = "controls">
	<?php
	$current_dir = empty($_['path'])?'/':$_['path'];
	$base_url = OCP\Util::linkTo('reader', 'index.php').'?dir=';
	
	$curr_path = '';
	$path = explode( '/', trim($current_dir,'/')); 
	// Navaigation Tab.
	if( $path != '' ){
		for($i=0; $i<count($path); $i++){ 
			$curr_path .= '/'.str_replace('+','%20', urlencode($path[$i]));?>
			<div class="crumb <?php if($i == count($path)-1) echo 'last';?> svg" data-dir='<?php echo $curr_path;?>' style='background-image:url("<?php echo OCP\image_path('core','breadcrumb.png');?>")'>
				<a href="<?php echo $base_url.$curr_path.'/'; ?>"><?php echo htmlentities($path[$i],ENT_COMPAT,'utf-8'); ?></a>
			</div>
	<?php }
	}	
	?>
	<div id="file_action_panel"></div>
	<input type="hidden" name="dir" value="<?php echo empty($_['path'])?'':rtrim($_['path'],'/') ?>" id="dir">
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
					$dirname = $dirname.'/'; }
				display_ebooks(basename($r['filepath']),$dirname);
			}
		?>
</tbody>
</table>
