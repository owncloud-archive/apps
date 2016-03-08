<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<script type="text/javascript">
    PDFJS.workerSrc = 'apps/reader/js/pdf.js';
</script>

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
			<div class="crumb <?php if($i == count($path)-1) p('last');?> svg" data-dir='<?php p($curr_path);?>' style='background-image:url("<?php print_unescaped(OCP\image_path('core','breadcrumb.png'));?>")'>
				<a href="<?php print_unescaped($base_url.$curr_path.'/'); ?>"><?php p($path[$i]) ?></a>
			</div>
	<?php }
	}	
	?>
	<div id="file_action_panel"></div>
	<input type="hidden" name="dir" value="<?php p(empty($_['path'])?'':rtrim($_['path'],'/')) ?>" id="dir">
</div>

<div class="actions"></div>

<table id = "readerContent">
	<tbody id = "fileList">
		<?php
		
			include('reader/lib/thumbnail.php');
			include('reader/lib/library_display.php');
			include('reader/lib/tag_utils.php');
			$file = $_['file'];
			$path = $_['path'];
			$filename = $_['filename']; 
			display_ebooks($filename,$path.'/');
		?>
	</tbody>
</table>

