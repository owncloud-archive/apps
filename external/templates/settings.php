<form class="section" id="external">
	<h2><?php p($l->t('External Sites'));?></h2>
	<p>
		<em><?php p($l->t('Please note that some browsers will block displaying of sites via http if you are running https.')); ?></em>
		<br>
		<em><?php p($l->t('Furthermore please note that many sites these days disallow iframing due to security reasons.')); ?></em>
		<br>
		<em><?php p($l->t('We highly recommend to test the configured sites below properly.')); ?></em>
	</p>
		<ul class="external_sites">

		<?php
		$sites = \OCA\External\External::getSites();
		for($i = 0; $i < sizeof($sites); $i++) {
			print_unescaped('<li>
			<input type="text" class="site_name" name="site_name[]" value="'.OCP\Util::sanitizeHTML($sites[$i][0]).'" placeholder="'.$l->t('Name').'" />
			<input type="text" class="site_url"  name="site_url[]"  value="'.OCP\Util::sanitizeHTML($sites[$i][1]).'" placeholder="'.$l->t('URL').'" />
			<select class="site_icon" name="site_icon[]">');
			$nf = true;
			foreach($_['images'] as $image) {
				if (basename($image) == $sites[$i][2]) {
					print_unescaped('<option value="'.basename($image).'" selected>'.basename($image).'</option>');
					$nf = false;
				} else {
					print_unescaped('<option value="'.basename($image).'">'.basename($image).'</option>');
				}
			}
			if($nf) {
				print_unescaped('<option value="" selected>'.$l->t('Select an icon').'</option>');
			} else {
				print_unescaped('<option value="">'.$l->t('Select an icon').'</option>');
			}
			print_unescaped('</select>
			<img class="svg action delete_button" src="'.OCP\image_path("", "actions/delete.svg") .'" title="'.$l->t("Remove site").'" />
			</li>');
		}
		if(sizeof($sites) === 0) {
			print_unescaped('<li>
			<input type="text" class="site_name" name="site_name[]" value="" placeholder="'.$l->t('Name').'" />
			<input type="text" class="site_url"  name="site_url[]"  value="" placeholder="'.$l->t('URL').'" />
			<select class="site_icon" name="site_icon[]">');
			foreach($_['images'] as $image) {
				print_unescaped('<option value="'.basename($image).'">'.basename($image).'</option>');
			}
			print_unescaped('<option value="" selected>'.$l->t('Select an icon').'</option>
			</select>
			<img class="svg action delete_button" src="'.OCP\image_path("", "actions/delete.svg") .'" title="'.$l->t("Remove site").'" />
			</li>');
		}

		?>

		</ul>

	<input type="button" id="add_external_site" value="<?php p($l->t("Add")); ?>" />
	<span class="msg"></span>
</form>
