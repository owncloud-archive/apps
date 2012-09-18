<fieldset class="personalblock">
	<legend><?php echo $l->t('Mozilla Sync Service'); ?></legend>
    <table class="nostyle">
      <tr>
        <td><label class="bold"><?php echo $l->t('Email');?></label></td>
        <td><?php echo $_['email'];?></td>
      </tr>
      <tr>
        <td><label class="bold"><?php echo $l->t('Password and Confirmation');?></label></td>
        <td>Use your owncloud account password</td>
      </tr>
      <tr>
        <td><label class="bold"><?php echo $l->t('Server address');?></label></td>
        <td><?php echo $_['syncaddress'];?></td>
      </tr>
    </table>
    Video tutorial on Mozilla Sync Service configuration can be found at
    <a href="http://www.mozilla.org/en-US/firefox/video/?video=fx4-sync-instructions">
    http://www.mozilla.org/en-US/firefox/video/?video=fx4-sync-instructions</a>
</fieldset>