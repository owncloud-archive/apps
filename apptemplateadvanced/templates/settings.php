<form id="apptemplate">
	<fieldset class="personalblock">
		<strong>Advanced App Template</strong><br />
		<input type="text" name="somesetting" id="somesetting" value="<?php p($_['url']); ?>" placeholder="<?php p($l->t('Some Setting'));?>" />
		<br />
		<span class="msg"></span>
	</fieldset>
</form>
