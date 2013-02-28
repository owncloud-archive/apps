<p><?php echo str_replace('{user}', $_['user'], $l->t('Greetings {user},')); ?> </p>
<p style='margin-left:20px'><?php p($l->t('Sorry, but a malware was detected in a file you tried to upload and it had to be deleted.')); ?> <br />
   <?php echo str_replace('{host}', $_['host'], $l->t('This email is a notification from {host}. Please, do not reply.')); ?> </p>
<p style='margin-left:20px'><?php echo str_replace('{file}', $_['file'], $l->t('File uploaded: {file}')); ?> </p>
