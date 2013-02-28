<form id="xmppadmin" action="#" method="post">
	<fieldset class="personalblock">
		<legend><strong><?php p($l->t('XMPP Admin Account'));?></strong></legend>
        <p>
        	<label for="usermail"><?php p($l->t('Username'));?>
        		<input type="text" id="xmppAdminUser" name="xmppAdminUser" value="<?php p($_['xmppAdminUser']);?>">
        	</label>
        	<label for="usermail"><?php p($l->t('Password'));?>
        		<input type="password" id="xmppAdminPasswd" name="xmppAdminPasswd" value="<?php p($_['xmppAdminPasswd']);?>">
        	</label>
	</p>
	<p>
        	<label for="usermail"><?php p($l->t('BOSH URL'));?>
        		<input type="text" id="xmppBOSHURL" name="xmppBOSHURL" value="<?php p($_['xmppBOSHURL']);?>">
        	</label>
	</p>
	<p>
		<label for="usermail"><?php p($l->t('Default XMPP Domain'));?>
			<input type="text" id="xmppDefaultDomain" name="xmppDefaultDomain" value="<?php p($_['xmppDefaultDomain']);?>">
		</label>
	</p>
        <input type="submit" value="Save" />
	</fieldset>
</form>
