<div id="firstrun">
    <h1><?php echo $l->t("You don't have any email account configured yet.") ?></h1>
    <div id="selections">
	    <fieldset id="addaccount_dialog_firstrun">
	        <legend style="margin-left:10px;"><img src="<?php echo OCP\Util::imagePath('mail','icon.png'); ?>"> <?php echo $l->t('Add email account') ?></legend>
	        <input type="text" id="email_address" placeholder="<?php echo $l->t('E-Mail Address'); ?>" />
	        <input type="submit" value="<?php echo $l->t('Auto Detect'); ?>" id="account_add_submit" />
	    </fieldset>
    </div>
    <div>
        <small><?php echo($l->t('You can manage your email accounts here:')); ?></small>
	    <a class="button"><?php echo $l->t('Settings'); ?></a>
	</div>
</div>
