<form id="external">
	<div class="section">
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
			print_unescaped('<li><input type="text" name="site_name[]" class="site_name" value="'.OCP\Util::sanitizeHTML($sites[$i][0]).'" placeholder="'.$l->t('Name').'" />
			<input type="text" class="site_url" name="site_url[]"  value="'.OCP\Util::sanitizeHTML($sites[$i][1]).'"  placeholder="'.$l->t('URL').'" />
			<img class="svg action delete_button" src="'.OCP\image_path("", "actions/delete.svg") .'" title="'.$l->t("Remove site").'" />
			</li>');
		}
		?>

		</ul>

        <input type="button" id="add_external_site" value="<?php p($l->t("Add")); ?>" />
		<span class="msg"></span>
	</div>
</form>
