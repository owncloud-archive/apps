<form id="antivirus" action="#" method="post">
	<fieldset class="personalblock">
		<legend><strong><?php p($l->t('Antivirus Configuration'));?></strong></legend>
		<p class='av_mode'><label for="av_mode"><?php p($l->t('Mode'));?></label>
			<select id="av_mode" name="av_mode"><?php print_unescaped(html_select_options(array('executable' => $l->t('Executable'), 'daemon' => $l->t('Daemon')), $_['av_mode'])) ?></select>
		</p>
		<p class='av_host'><label for="av_host"><?php p($l->t('Host'));?></label><input type="text" id="av_host" name="av_host" value="<?php p($_['av_host']); ?>" title="<?php p($l->t('Address of Antivirus Host.')). ' ' .$l->t('Not required in Executable Mode.');?>"></p>
		<p class='av_port'><label for="av_port"><?php p($l->t('Port'));?></label><input type="text" id="av_port" name="av_port" value="<?php p($_['av_port']); ?>" title="<?php p($l->t('Port number of Antivirus Host.')). ' ' .$l->t('Not required in Executable Mode.');?>"></p>
		<p class='av_chunk_size'><label for="av_chunk_size"><?php p($l->t('Stream Length'));?></label><input type="text" id="av_chunk_size" name="av_chunk_size" value="<?php p($_['av_chunk_size']); ?>" title="<?php p($l->t('ClamAV StreamMaxLength value in bytes.')). ' ' .$l->t('Not required in Executable Mode.');?>"> bytes</p>
		<p class='av_path'><label for="av_path"><?php p($l->t('Path to clamscan'));?></label><input type="text" id="av_path" name="av_path" value="<?php p($_['av_path']); ?>" title="<?php p($l->t('Path to clamscan executable.')). ' ' .$l->t('Not required in Daemon Mode.');?>"></p>
		<p class='infected_action'><label for="infected_action"><?php p($l->t('Action for infected files found while scanning'));?></label>
			<select id="infected_action" name="infected_action"><?php print_unescaped(html_select_options(array('only_log' => $l->t('Only log'), 'delete' => $l->t('Delete file')), $_['infected_action'])) ?></select>
		</p>
		<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']);?>" />
		<input type="submit" value="<?php p($l->t('Save'));?>" />
	</fieldset>
</form>
