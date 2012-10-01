<div id = "controls">
	<!-- TODO: add controls -->
	<div id="file_action_panel"></div>
	<!-- Set dir value to be passed to integrate.js -->
	<input type="hidden" name="dir" value="<?php echo empty($_['dir'])?'':rtrim($_['dir'],'/') ?>" id="dir">
</div>
<div class = "actions"></div>
<?php
	// Get the current directory.
	$dir = empty($_['dir'])?'/':$_['dir'];

	// Search for pdf files in current directory.
	$pdfs = \OC_FileCache::searchByMime('application', 'pdf', '/'.\OCP\USER::getUser().'/files'.$dir);
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
			$dirs = array();
			foreach ($files as $file) {
				// Encode the file and directory names so that they can be used in querying a url.
				$name = str_replace('+','%20',urlencode($file['filename']));
				$name = str_replace('%2F','/', $name);
				$directory = str_replace('+','%20',urlencode($dir));
				$directory = str_replace('%2F','/', $directory);
				if ($file['dirname'] == '.') { 
		?>			
					<!-- Each tr displays a file -->	
					<tr data-file="<?php echo $name;?>" data-type="<?php echo 'file'?>" data-mime="<?php echo 'application/pdf'?>" data-size="3462755" data-write="true" >
						<td class="filename svg" style="background-image:url(<?php echo OCP\mimetype_icon('application/pdf'); ?>)">
							<a class="name" href="http://localhost<?php echo \OCP\Util::linkTo('files', 'download.php').'?file='.$directory.$name; ?>" title="">
								<span class = "nametext">
									<?php echo htmlspecialchars($file['basename']);?><span class='extension'><?php echo $file['extension'];?></span>
								</span>
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
					// Each tr entry here contains name of a directory which has a link to reader/index.php.
					// TODO: correct mime type to be rendered explicitly.
					$d = '<tr data-file="'.$dir_array[0].'" data-type="dir" data-mime="httpd/unix-directory">
							<td class="filename svg" style="background-image:url('.OCP\mimetype_icon('dir').')">
								<a class = "name" href = "'.OCP\Util::linkTo('reader', 'index.php').'&dir='.$dir.$dir_array[0].'/">
									<span class = "nametext">'.
										htmlspecialchars($dir_array[0]).
									'</span>
								</a>
							</td>
						</tr>';
					/* Store the directory entries in an array so that incase 2 pdf files are conatined in a directory
					 * we don't end up printing the directory name twice. */
					if (!in_array($d, $dirs)) {
						$dirs[] = $d;
						echo $d;
						echo '<br>';
					}
				}
			}
		?>
	</tbody>
</table>	


