<?php
function display_each_ebook($directory,$name) {
	$check_thumb = check_thumb_exists(urldecode($directory.$name));
	echo '<td id = "thumbnail_container" width = "14%">
			<img rel ="images" id = "'.$directory.$name.'" src = "'.\OCP\Util::linkTo('reader', 'ajax/thumbnail.php').'&filepath='.$directory.rtrim($name,'pdf').'png'.'" value = "'.$check_thumb.'">	
		</td>';
	echo '<td class = "filename svg" width = "86%">
			<a class="name" href="http://localhost'.\OCP\Util::linkTo('files', 'download.php').'?file='.$directory.$name.'" title="'.urldecode($name).'" dir = "'.urldecode($directory.$name).'">
				<span class = "nametext">'.
					htmlspecialchars(urldecode($name)).
				'</span>
			</a>';
			$each_row = find_tags_for_ebook(urldecode($directory).urldecode($name));
			$tags = explode(",",$each_row);
			if (count($tags) < 5) {
				echo '<form action = "apps/reader/ajax/tags.php" id="TagForm">
				<input type="text" name="tag" placeholder="Add Tag..." />
				<input type="submit" value="Add Tag" />
				</form>';
			}

			if (count($tags) >= 1) {
				echo '<div id="result">';
				foreach ($tags as $tag) {	
					if ($tag != '' ) {
						echo '<div class = "each_result">';
						echo '<a id = "each_tag" href = "'.\OCP\Util::linkTo('reader', 'fetch_tags.php').'?tag='.$tag.'">'
						.ucwords($tag).
						'</a>';
						echo '<a id = "close" value = "'.$tag.'">x</a></div>';
					}
				} 			
				echo '</div>';
			}
			echo '</td>';	
}

function display_sub_dirs($current_dir,$sub_dirs) {
	$results = explore($current_dir,$sub_dirs,1);	
	foreach ($results as $r) {
		echo '<tr id = "row" data-file="'.$r[0].'" data-type="dir">
				<td id = "thumbnail_container" width = "14%"><div id = "thumbs">';
					$is_dir = check_dir_exists($current_dir,$r[0]);
					$margin = 10;
					$img_id = 1;
					foreach ($r[1] as $thumbs) {
						$thumb_exists = false;
						$thumb_exists = check_thumb_exists($current_dir.$r[0].'/'.$thumbs);
			
						$img_path = \OCP\Util::linkTo('reader', 'ajax/thumbnail.php').'&filepath='.urlencode($current_dir.$r[0].'/'.rtrim($thumbs,'pdf').'png');
						$counter = 3;						
						for ($i = 1; $i <= $counter; $i++) {
							echo '<img id = "'.$current_dir.$r[0].'/'.$thumbs.'" src = "'.$img_path.'" value = "'.$thumb_exists.'" style = "position:absolute;top:-55px;left:10px;margin-left:'.$margin.'px; z-index:'.(50-$margin).';"/>';
							$margin = $margin + 5;
							$img_id = $img_id + 1;
						}
					}
				echo '</div></td>';
				echo '<td class = "filename svg" width = "86%">
					<a class = "dirs" id = "'.$r[0].'" href = "'.OCP\Util::linkTo('reader', 'index.php').'&dir='.$current_dir.$r[0].'/'.'">
						<span class = "nametext">'
							.htmlspecialchars($r[0])
						.'</span>
					</a>
					<div id = "more_info" style = "color:#666;margin-left:15px;margin-top:35px; vertical-align:bottom">';
						echo "Browse in for";
						echo '<br>';
						$dir_browse_results = explore($current_dir,array($r[0]),5);
						foreach($dir_browse_results as $browse_result) {
							foreach($browse_result[1] as $each) {
								$each_sub_dir = explode("/",$each);
								if (count($each_sub_dir) > 1) {
									foreach($each_sub_dir as $element) { echo '<span style = "color:#DDD;">>></span>'.$element; }
								}
								else
									echo '<span style = "color:#DDD;"> >></span>'.$each;echo '<br>';
							}
						}
				echo '</div> 
			</td>
		</tr>';
	}
}

function display_ebooks($filename,$current_dir) {
	$name = str_replace('+','%20',urlencode($filename));
	$name = str_replace('%2F','/', $name);
	$directory = str_replace('+','%20',urlencode($current_dir));
	$directory = str_replace('%2F','/', $directory);
	
	echo '<tr id = "row" data-file="'.$name.'" data-type="file" data-mime="application/pdf" data-size="3462755" data-write="true">';
		display_each_ebook($directory,$name);
	echo '</tr>';
	echo '<br>';
}
