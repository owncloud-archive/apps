<?php

/**
* ownCloud - ocDownloader plugin
*
* @author Xavier Beurois
* @copyright 2012 Xavier Beurois www.djazz-lab.net
* 
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either 
* version 3 of the License, or any later version.
* 
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*  
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

?>

<form id="ocdownloader" method="POST" action="#">
	<fieldset class="personalblock">
		<strong>ocDownloader</strong>
		<input type="hidden" id="ocdownloader" name="ocdownloader" value="1" />
		<?php foreach($_['pr_list'] as $p){ ?>
		<div>
			<div style="float:left;width:100px;margin-top:8px;">
				<label><?php print($p['pr_name']); ?></label>
			</div>
			<input type="text" name="ocdownloader_pr_un_<?php print($p['pr_id']); ?>" id="ocdownloader_pr_un_<?php print($p['pr_id']); ?>" value="<?php print(!is_null($p['us_id'])?$p['us_username']:''); ?>" placeholder="Username" /><input type="password" name="ocdownloader_pr_pw_<?php print($p['pr_id']); ?>" id="ocdownloader_pr_pw_<?php print($p['pr_id']); ?>" value="<?php print(!is_null($p['us_id'])?$p['us_password']:''); ?>" placeholder="Password" /><?php print(!is_null($p['us_id'])?'<img class="ocdownloader-delete" src="' . OC_Helper::imagePath('ocdownloader', 'delete.png') . '" rel="' . $p['pr_id'] . '" style="margin-left:10px" />':''); ?>
		</div>
		<?php } ?>
		<input type="submit" value="Save" />
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$('.ocdownloader-delete').bind('click', function(){
			$('#ocdownloader_pr_un_' + $(this).attr('rel')).val('');
			$('#ocdownloader_pr_pw_' + $(this).attr('rel')).val('');
		});
	});
</script>
