<form id="openidform">
	<fieldset class="personalblock">
		<legend><strong><?php p($l->t('OpenID'));?></strong></legend>
		<?php p((OCP\Util::getServerProtocol()).'://'.OCP\Util::getServerHost().OC::$WEBROOT.'/?'); p(OCP\USER::getUser()); ?><br /><em><?php p($l->t('you can authenticate to other sites with this address'));?></em><br />
		<label for="identity"><?php p($l->t('Authorized OpenID provider'));?></label>
		<input type="text" name="identity" id="identity" value="<?php p($_['identity']); ?>" placeholder="<?php p($l->t('Your address at Wordpress, Identi.ca, &hellip;'));?>" /><span class="msg"></span>
	</fieldset>
</form>
