<?php 
function mail_recursive_folder_tree( $account, $folders, $depth ){
	foreach( $folders as $folder ){
		echo '<li data-folder_id="'.$folder['id'].'">';
		echo $folder['name'];
		echo '</li>';
		if( isset( $folder['children'] ) && count( $folder['children'] )){
			mail_recursive_folder_tree( $account, $folder['children'], $depth + 1 );
		}
	}
}
?>
<form >
	<input type="button" value="<?php echo $l->t('New Message'); ?>">
</form>

<h2 class="mail_account"><?php echo $account['name']; ?></h2>

<?php foreach( $_['accounts'] as $account ): ?>
	<ul class="mail_folders" data-account_id="<?php echo $account['id']; ?>">
		<?php mail_recursive_folder_tree( $account['id'], $account['folders'], 0 ); ?>
	</ul>
<?php endforeach; ?>
