<form id="xmppadmin" action="#" method="post">
	<fieldset class="personalblock">
		<legend><strong><?php echo $l->t('XMPP Admin Account');?></strong></legend>
        <p>
        	<label for="usermail"><?php echo $l->t('Username');?>
        		<input type="text" id="xmppAdminUser" name="xmppAdminUser" value="<?php echo $_['xmppAdminUser'];?>">
        	</label>
        	<label for="usermail"><?php echo $l->t('Password');?>
        		<input type="password" id="xmppAdminPasswd" name="xmppAdminPasswd" value="<?php echo $_['xmppAdminPasswd'];?>">
        	</label></p>
        	<label for="usermail"><?php echo $l->t('BOSH URL');?>
        		<input type="text" id="xmppBOSHURL" name="xmppBOSHURL" value="<?php echo $_['xmppBOSHURL'];?>">
        	</label></p>
        <input type="submit" value="Save" />
	</fieldset>
</form>
