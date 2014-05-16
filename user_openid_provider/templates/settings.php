<form id="user_openid_provider">
	<div class="section">
		<h2><?php p($l->t('OpenID Provider remembered sites', 'user_openid_provider')) ?></h2>
		<table>
		<?php foreach($_['trusted_sites'] as $url => $trust): ?>
			<tr>
				<td><?php p($url) ?></td>
				<td class="<?php p($trust ? 'trusted' : 'denied') ?>"><?php p($trust ? $l->t('Trusted', 'user_openid_provider') : $l->t('Denied', 'user_openid_provider') )?></td>
				<td><button class="delete"><?php p($l->t('Remove', 'user_openid_provider')) ?></button></td>
			</tr>
		<?php endforeach ?>
		</table>
	</div>
</form>
