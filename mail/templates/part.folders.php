<?php if (count($_['accounts']) > 0) { ?>
<form>
    <input type="button" value="<?php echo $l->t('New Message'); ?>">
</form>

<?php foreach ($_['accounts'] as $account): ?>
    <h2 class="mail_account"><?php echo $account['name']; ?></h2>
    <ul class="mail_folders" data-account_id="<?php echo $account['id']; ?>">
        <!--	<li>--><?php //echo $account['error']; ?><!--</li>-->
		<?php foreach ($account['folders'] as $folder): ?>
		<?php $unseen = $folder['unseen'] ?>
		<?php $total = $folder['total'] ?>
        <li data-folder_id="<?php echo $folder['id']; ?>">
			<?php echo $folder['name']; ?>
			<?php if ($total > 0) {
			echo " ($unseen/$total)";
		}?>
        </li>
		<?php endforeach; ?>
    </ul>
	<?php endforeach; ?>

<?php } ?>
