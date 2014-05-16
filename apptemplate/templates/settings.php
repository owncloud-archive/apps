<form id="apptemplate">
	<div class="section">
		<h2><?php p($l->t('App Template'));?></h2>
		<input type="text" name="somesetting" id="somesetting" value="<?php p($_['url']); ?>" placeholder="<?php p($l->t('Some Setting'));?>" />
		<br />
		<span class="msg"></span>
	</div>
</form>
