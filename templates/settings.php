<form id="antivirus" action="#" method="post">
	<fieldset class="personalblock">
		<legend><strong><?php echo $l->t('Antivirus Configuration');?></strong></legend>
		<p class='av_mode'><label for="av_mode"><?php echo $l->t('Mode');?></label>
			<select id="av_mode" name="av_mode"><option value="executable"<?php if (isset($_['av_mode']) && ($_['av_mode'] == 'executable')) echo ' selected'; ?>><?php echo $l->t('Executable');?></option><option value="daemon"<?php if (isset($_['av_mode']) && ($_['av_mode'] == 'daemon')) echo ' selected'; ?>><?php echo $l->t('Daemon');?></option></select>
		</p>
		<p class='av_host'><label for="av_host"><?php echo $l->t('Host');?></label><input type="text" id="av_host" name="av_host" value="<?php echo $_['av_host']; ?>" title="<?php echo $l->t('Address of Antivirus Host. Not required in Executable Mode.');?>"></p>
		<p class='av_port'><label for="av_port"><?php echo $l->t('Port');?></label><input type="text" id="av_port" name="av_port" value="<?php echo $_['av_port']; ?>" title="<?php echo $l->t('Port number of Antivirus Host. Not required in Executable Mode.');?>"></p>
		<p class='av_path'><label for="av_path"><?php echo $l->t('Path to clamscan');?></label><input type="text" id="av_path" name="av_path" value="<?php echo $_['av_path']; ?>" title="<?php echo $l->t('Path to clamscan executable. Not required in Daemon Mode.');?>"></p>
		<input type="submit" value="<?php echo $l->t('Save');?>" />
	</fieldset>
</form>
