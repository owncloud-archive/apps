<form >
	<input type="button" value="<?php echo $l->t('New Message'); ?>">
</form>


<?php foreach( $_['accounts'] as $account ): ?>
	<h2 class="mail_account"><?php echo $account['name']; ?></h2>
	<ul class="mail_folders" data-account_id="<?php echo $account['id']; ?>">
		<?php foreach( $account['folders'] as $folder ): ?>
			<li data-folder_id="<?php echo $folder['id']; ?>">
				<?php echo $folder['name']; ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endforeach; ?>
