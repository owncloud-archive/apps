<script type="text/javascript">
    // Specify the main script used to create a new PDF.JS web worker.
    // In production, change this to point to the combined `pdf.js` file.
    PDFJS.workerSrc = 'apps/reader/js/pdf.js';
</script>


<div id = "controls">
	<?php
	include('apps/reader/lib/dir.php');
	// Get the current directory.
	$current_dir = empty($_['dir'])?'/':$_['dir'];
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
	<!-- Set dir value to be passed to integrate.js -->
	<input type="hidden" name="dir" value="<?php echo empty($_['dir'])?'':rtrim($_['dir'],'/') ?>" id="dir">
</div>
<div class="actions"></div>
<?php
	
	// Search for pdf files in current directory.
	$pdfs = \OC_FileCache::searchByMime('application', 'pdf', '/'.\OCP\USER::getUser().'/files'.$current_dir);
	sort($pdfs);

	// Construct an array, to store pdf files and directory names, in which pdf files reside.
	$files = array();
	// Store file info in the file array.
	foreach ($pdfs as $pdf) {
		$file_info = pathinfo($pdf);
		$file = array();
		$file['dirname'] = $file_info['dirname'];
		$file['basename'] = $file_info['filename'];
		$file['filename'] = $file_info['basename'];
		$file['extension'] = '.'.$file_info['extension'];	
		$files[] = $file;
	}
?>

<table>
	<tbody id = "fileList">
		<?php
		// Array to store directory entries, which contain pdfs.
			$sub_dirs = array();
			foreach ($files as $file) {
				// Encode the file and directory names so that they can be used in querying a url.
				$name = str_replace('+','%20',urlencode($file['filename']));
				$name = str_replace('%2F','/', $name);
				$directory = str_replace('+','%20',urlencode($current_dir));
				$directory = str_replace('%2F','/', $directory);
				if ($file['dirname'] == '.') { 
		?>			
					<!-- Each tr displays a file -->	
					<tr id = "row" data-file="<?php echo $name;?>" data-type="<?php echo 'file'?>" data-mime="<?php echo 'application/pdf'?>" data-size="3462755" data-write="true" >
						<td class="filename svg">
							<a class="name" id = "http://localhost<?php echo \OCP\Util::linkTo('files', 'download.php').'?file='.$directory.$name; ?>" href="http://localhost<?php echo \OCP\Util::linkTo('files', 'download.php').'?file='.$directory.$name; ?>" title="">
								<center>
									<span class = "nametext">
										<?php echo htmlspecialchars($file['basename']);?>
									</span>
								</center>
							</a>
						</td>
					</tr>
		<?php
					echo '<br>';
				}
				else {
					// Trim the extra slash that we don't need.
					$dir_name = ltrim($file['dirname'], '/');
					// Explode the variable to check if the pdf file is contained in a directory.
					$dir_array = explode('/', $dir_name);
					// Get the directory name in which the pdf resides.
					$sub_dir = $dir_array[0];
					if (!in_array($sub_dir, $sub_dirs)) {
						$sub_dirs[] = $sub_dir;
					}
				}
			}
			/* Send the the directory names, inside the current directory, and current 
			 * directory name to fetch any 3 pdf urls inside those directories.*/
			$results = explore($current_dir,$sub_dirs);
			
			foreach ($results as $r) {?>
			<!-- Display folder name--> 
				<tr id = "row" data-file="<?php echo $r[0];?>" data-type="dir">
					<td class = "filename svg">
						<a class = "dirs" id = "<?php echo $r[0];?>" href = "<?php echo OCP\Util::linkTo('reader', 'index.php').'?dir='.$current_dir.$r[0].'/';?>">
							<center>
								<span class = "nametext">
									<?php echo htmlspecialchars($r[0]);?>
								</span>
							</center>
							<?php
								$margin = 15;
								// Display thumbnails of 3 pdf pages to show a folder. 
								foreach ($r[1] as $thumbs) {
									// Use directory name, pdf url, margin value to be sent to javascript to generate thumbnail. 
									echo '<input type = "hidden" value = "'.$r[0].'" id = "http://localhost'.\OCP\Util::linkTo('files', 'download.php').'?file='.$directory.$r[0].'/'.$thumbs.'" name = "'.$margin.'">';
									// Left Margin of each page should increase from the left to right.
									$margin = $margin + 15;
								}
							?>	
							
						</a>
					</td>
				</tr><?php
			}
		?>
	</tbody>
</table>	


