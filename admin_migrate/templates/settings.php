<form id="export" action="#" method="post">
    <fieldset class="personalblock">
        <h2><?php p($l->t('Admin Export'));?></h2>
        <p>
           <input type="submit" name="admin_export" value="<?php p($l->t('Export config file')); ?>" />
        </p>
    </fieldset>
</form>
<?php
/*
 * EXPERIMENTAL
?>
<form id="import" action="#" method="post" enctype="multipart/form-data">
    <fieldset class="personalblock">
        <h2><?php p($l->t('Import an ownCloud instance. THIS WILL DELETE ALL CURRENT OWNCLOUD DATA'));?></h2>
        <p><?php p($l->t('All current ownCloud data will be replaced by the ownCloud instance that is uploaded.'));?>
        </p>
        <p><input type="file" id="owncloud_import" name="owncloud_import"><label for="owncloud_import"><?php p($l->t('ownCloud Export Zip File'));?></label>
        </p>
        <input type="submit" name="admin_import" value="<?php p($l->t('Import')); ?>" />
    </fieldset>
</form>
<?php
*/
