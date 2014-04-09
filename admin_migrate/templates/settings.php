<form id="export" action="#" method="post">
    <div class="section">
        <h2><?php p($l->t('Export data'));?></h2>
        <p>
        <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>" id="requesttoken">
        <input type="radio" id="export_instance" name="export_type" value="instance" checked /><label for="export_instance"><?php p($l->t('All system files and user data'));?></label><br />
        <input type="radio" id="export_system" name="export_type" value="system" /><label for="export_system"><?php p($l->t('Only system files'));?></label><br />
        <input type="radio" id="export_value" name="export_type" value="userfiles" /><label for="export_value"><?php p($l->t('Only user data'));?></label><br />
        <input type="submit" name="admin_export" value="<?php p($l->t('Export')); ?>" />
    </div>
</form>
<?php
/*
 * EXPERIMENTAL
?>
<form id="import" action="#" method="post" enctype="multipart/form-data">
    <div class="section">
        <h2><?php p($l->t('Import an ownCloud instance. THIS WILL DELETE ALL CURRENT OWNCLOUD DATA'));?></h2>
        <p><?php p($l->t('All current ownCloud data will be replaced by the ownCloud instance that is uploaded.'));?>
        </p>
        <p><input type="file" id="owncloud_import" name="owncloud_import"><label for="owncloud_import"><?php p($l->t('ownCloud Export Zip File'));?></label>
        </p>
        <input type="submit" name="admin_import" value="<?php p($l->t('Import')); ?>" />
    </div>
</form>
<?php
*/
