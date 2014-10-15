
<link rel="stylesheet" type="text/css" href="../apps/user_saml/css/saml.css" />
<form class="section" id="regenerate" action="#" method="post">
<h2> <?php p($l->t('Reset desktop client password'));?></h2>
	<input type="hidden" name="requesttoken" value="<?php echo $_['requesttoken'] ?>" id="requesttoken">
	<input type="button" id="regenerate_password_button" name="regenerate_password" class="inlineblock" value="<?php p($l->t('Reset'));?>" />
        <div class="inlineblock">And your new password is... <strong><span id="newpassword"></span></strong><span id="newpassword_notification"></span></div>
        <div><?php p($l->t("Be careful. We will show your new password only once. If you reset it you have to change all syncronizing devices' password."));?></div>
</form>
