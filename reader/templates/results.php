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

<table id = "readerContent">
	<tbody id = "fileList">
		<?php
			include('apps/reader/lib/thumbnail.php');
			$file = $_['file'];
			$path = $_['path'];
			$filename = $_['filename']; 
				// Encode the file and directory names so that they can be used in querying a url.
			$name = str_replace('+','%20',urlencode($filename));
			$name = str_replace('%2F','/', $name);
			$directory = str_replace('+','%20',urlencode($path));
			$directory = str_replace('%2F','/', $path);
		?>			
			<!-- Each tr displays a file -->	
			<tr id = "row" data-file="<?php echo $name;?>" data-type="<?php echo 'file'?>" data-mime="<?php echo 'application/pdf'?>" data-size="3462755" data-write="true">
				<td class="filename svg">
				<?php $check_thumb = check_thumb_exists($directory,$name);?>
					<a class="name" href="http://localhost<?php echo \OCP\Util::linkTo('files', 'download.php').'?file='.$directory.$name; ?>" title="<?php echo urldecode($name);?>" dir ="<?php echo $directory.$name?>" value  = "<?php echo $check_thumb;?>">
						<center>
							<span class = "nametext">
								<?php echo htmlspecialchars($filename);?>
							</span>
						</center>
						<img rel ="images" src = "<?php echo \OCP\Util::linkTo('reader', 'ajax/thumbnail.php').'&filepath='.urlencode($path.rtrim($filename,'pdf').'png');?>">	
					</a>
				</td>
			</tr> 
	</tbody>
</table>

