<p><?php $l->t("Sorry, but we found a malware in the file you tried to upload and it had to be deleted."); ?> </p>
<p><?php echo str_replace('{file}', $_['file'], $l->t('File uploaded: {file}')); ?> </p>
