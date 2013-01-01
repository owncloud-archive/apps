<form id="xmppchat" action="#" method="post">
	<fieldset class="personalblock">
		<legend><strong><?php echo $l->t('XMPP Chat Settings');?></strong></legend>
		<p>
			<input type="checkbox" name="autoroster" id="autoroster" <?php if ($_['autoroster']) echo ' checked'; ?>>
			<label for="autoroster"><?php echo $l->t('Auto Add contacts to roster on insert/update');?></label>
		</p>
        <input type="submit" value="Save" />
	</fieldset>
</form>
