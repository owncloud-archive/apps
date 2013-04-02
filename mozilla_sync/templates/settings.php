<fieldset class="personalblock">
	<legend><?php p($l->t('Mozilla Sync Service')); ?></legend>
    <table class="nostyle">

      <tr>
        <td><label class="bold"><?php p($l->t('Email'));?></label></td>
        <td><?php p($_['email']);?></td>
      </tr>
      <tr>
        <td><label class="bold"><?php p($l->t('Password and Confirmation'));?></label></td>
        <td>Use your owncloud account password</td>
      </tr>
      <tr>
        <td><label class="bold"><?php p($l->t('Server address'));?></label></td>
        <td><?php p($_['syncaddress']);?></td>
      </tr>
    </table>
    Video tutorial on Mozilla Sync Service configuration can be found at
    <a href="http://www.mozilla.org/en-US/firefox/video/?video=fx4-sync-instructions">
    http://www.mozilla.org/en-US/firefox/video/?video=fx4-sync-instructions</a>
</fieldset>