	<fieldset class="personalblock">
		<img src="<?php p(image_path('remoteStorage', 'remoteStorage.png')) ?>" style="width:16px">
		<strong><?php p($l->t('remoteStorage')) ?></strong> user address: <?php p(OCP\USER::getUser().'@'.$_SERVER['SERVER_NAME']) ?> (<a href="http://unhosted.org/">more info</a>)
		<p><em>Apps that currently have access to your ownCloud:</em></p>
		<script>
			function revokeToken(token) {
				var xhr = new XMLHttpRequest();
				xhr.open('POST', '/apps/remoteStorage/ajax/revokeToken.php', true);
				xhr.send(token);
			}
		</script>
		<ul>
		<?php foreach(OC_remoteStorage::getAllTokens() as $token => $details) { ?>
			<li onmouseover="$('#revoke_<?php p($token) ?>').show();" onmouseout="$('#revoke_<?php p($token) ?>').hide();">
				<strong><?php p($details['appUrl']) ?></strong>: <?php p($details['categories']) ?>
				<a href="#" title="Revoke" class="action" style="display:none" id="revoke_<?php p($token) ?>" onclick="revokeToken('<?php p($token) ?>');$(this).hide();">
					<img src="<?php p(OCP\Util::imagePath('core', 'actions/delete.svg')) ?>">
				</a>
			</li>
		<?php } ?>
		</ul>
	</fieldset>
