<fieldset class="personalblock" id="migration-settings-block">
	<legend><strong><?php echo $l->t('User Account Migration');?></strong></legend>
	<p><?php echo $l->t('Export and import ownCloud user accounts.');?>
	</p>
	<?php if(isset($_['error'])) { ?>
		<h3><?php echo $_['error']['error']; ?></h3>
		<p><?php echo $_['error']['hint']; ?></p>
		<?php } ?>
	<button id="exportbtn">Export<img class="loadingexport" src="<?php echo OCP\Util::imagePath('core', 'loading.gif'); ?>" /></button>
	<form id="import" action="#" method="post" enctype="multipart/form-data">
		<input type="hidden" name="requesttoken" value="<?php echo $_['requesttoken'] ?>" id="requesttoken">
		<input type="file" id="import_input" name="owncloud_import">
	</form>
	<button id="importbtn">Import<img class="loadingimport" src="<?php echo OCP\Util::imagePath('core', 'loading.gif'); ?>" /></button>
</fieldset>

