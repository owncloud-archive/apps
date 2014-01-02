<form id="xmppchat" action="#" method="post">
	<fieldset class="personalblock">
		<h2><?php p($l->t('XMPP Chat Settings'));?></h2>
		<p>
			<input type="checkbox" name="autoroster" id="autoroster" <?php if ($_['autoroster']) p(' checked'); ?>>
			<label for="autoroster"><?php p($l->t('Auto Add contacts to roster on insert/update'));?></label>
		</p>
        <input type="submit" value="Save" />
	</fieldset>
</form>
