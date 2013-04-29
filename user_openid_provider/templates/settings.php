<form id="user_openid_provider">
	<fieldset class="personalblock">
		<strong><?php p($l->t('OpenID Provider remembered sites', 'user_openid_provider')) ?></strong>
		<table>
		<?php foreach($_['trusted_sites'] as $url => $trust): ?>
			<tr>
				<td><?php p($url) ?></td>
				<td class="<?php p($trust ? 'trusted' : 'denied') ?>"><?php p($trust ? $l->t('Trusted', 'user_openid_provider') : $l->t('Denied', 'user_openid_provider') )?></td>
				<td><button class="delete"><?php p($l->t('Remove', 'user_openid_provider')) ?></button></td>
			</tr>
		<?php endforeach ?>
		</table>
	</fieldset>
</form>
