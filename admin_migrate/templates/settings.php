<form id="export" action="#" method="post">
    <fieldset class="personalblock">
        <legend><strong><?php echo $l->t('Export this ownCloud instance');?></strong></legend>
        <p>
            <?php echo $l->t('This will create a compressed file that contains the data of this owncloud instance.');?>
            <br />
            <?php echo $l->t('Please choose the export type:');?>
        </p>
        <p>
        <input type="radio" name="export_type" value="instance" style="width:20px;" /><?php echo $l->t('ownCloud instance (suitable for import)');?><br />
	<input type="radio" name="export_type" value="system" style="width:20px;" /><?php echo $l->t('ownCloud system files');?><br />
	<input type="radio" name="export_type" value="userfiles" style="width:20px;" /><?php echo $l->t('Just user files');?><br />
        <input type="submit" name="admin_export" value="<?php echo $l->t('Export'); ?>" />
    </fieldset>
</form>
<?php 
/* 
 * EXPERIMENTAL
?>
<form id="import" action="#" method="post" enctype="multipart/form-data">
    <fieldset class="personalblock">
        <legend><strong><?php echo $l->t('Import an ownCloud instance. THIS WILL DELETE ALL CURRENT OWNCLOUD DATA');?></strong></legend>
        <p><?php echo $l->t('All current ownCloud data will be replaced by the ownCloud instance that is uploaded.');?>
        </p>
        <p><input type="file" id="owncloud_import" name="owncloud_import"><label for="owncloud_import"><?php echo $l->t('ownCloud Export Zip File');?></label>
        </p>
        <input type="submit" name="admin_import" value="<?php echo $l->t('Import'); ?>" />
    </fieldset>
</form>
<?php 
*/ 
