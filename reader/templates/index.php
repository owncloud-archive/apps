<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<script type="text/javascript">
    // Specify the main script used to create a new PDF.JS web worker.
    PDFJS.workerSrc = 'apps/reader/js/pdf.js';
</script>

<div id = "controls">
<?
	include('reader/lib/dir.php');
	include('reader/lib/thumbnail.php');
	include('reader/lib/library_display.php');
	include('reader/lib/tag_utils.php');
	
	// Get the current directory.
	$current_dir = empty($_['dir'])?'/':$_['dir'];
	$base_url = OCP\Util::linkTo('reader', 'index.php').'&dir=';
	$curr_path = '';
	$path = explode( '/', trim($current_dir,'/')); 
	
	// Navaigation Tab.
	if( $path != '' ){
		for($i=0; $i<count($path); $i++){ 
			$curr_path .= '/'.str_replace('+','%20', urlencode($path[$i]));?>
			<div class="crumb <?php if($i == count($path)-1) p('last');?> svg" data-dir='<?php p($curr_path);?>' style='background-image:url("<?php print_unescaped(OCP\image_path('core','breadcrumb.png'));?>")'>
				<a href="<?php print_unescaped($base_url.$curr_path.'/'); ?>"><?php p($path[$i]); ?></a>
			</div>
<? 		}
	}	
?>

	<div id="file_action_panel"></div>
	<!-- Set dir value to be passed to integrate.js -->
	<input type="hidden" name="dir" value="<?php p(empty($_['dir'])?'':rtrim($_['dir'],'/')) ?>" id="dir">

</div>

<div class="actions"></div>

<?php
	
	// Search for pdf files in current directory.
	$view = new \OC\Files\View('/'.\OCP\USER::getUser().'/files'.$current_dir);
	$pdfs = $view->searchByMime('application/pdf');
	sort($pdfs);

	// Cleans the eBooks table of files that have been deleted from the files app.
	if ($current_dir == '/')
		check_consistency_with_database($current_dir,$pdfs);
	
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

<table id = "readerContent">
	<tbody id = "fileList">
<?php		
		// Array to store directory entries, which contain pdfs.
			$sub_dirs = array();
			$ebooks = array();
			
			foreach ($files as $file) {
				if ($file['dirname'] == '.') { 
					$ebooks[] = $file['filename'];
				}
				else {
					// Trim the extra slash that we don't need.
					$dir_name = ltrim($current_dir, '/');
			
					// Explode the variable to check if the pdf file is contained in a directory.
					$dir_array = explode('/', $file['dirname']);
					
					// Get the directory name in which the pdf resides.
					$sub_dir = $dir_array[0];
					if (!in_array($sub_dir, $sub_dirs)) {
						$sub_dirs[] = $sub_dir;
					}	
				}
			}	
			display_sub_dirs($current_dir,$sub_dirs);
			
			foreach ($ebooks as $ebook) {
				display_ebooks($ebook,$current_dir);
			}
?>
	</tbody>
</table>	
 
