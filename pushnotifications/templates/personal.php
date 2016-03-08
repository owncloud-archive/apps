<form id="pushnotificationform" class="section">
        <h2>
                <label for="pushnotificationid"><?php echo $l->t('Push Notification');?></label>
        </h2>
        <input type="text" id="pushnotificationid" name="pushnotificationid" size="50"
                value="<?php p($_['pushId'])?>"
                placeholder="Your Pushover.net id"
                autocomplete="on" autocapitalize="off" autocorrect="off" />
        <span class="msg"></span>
        <?php echo '<br />'.$l->t('Please install the <a href="https://pushover.net">pushover.net</a> app on your iOS or Android device or Mac. Then get the Pushover user key from the settings and enter it here. You will now get push notification for your activities.<br />') ;?>
        <?php if(empty($_['appId'])) echo '<br />'.$l->t('Please register and app on Pushover.net and set the app ID in the ownCloud config.php file. The config variable is called: "pushnotifications_pushover_app"'); ?>
</form>
