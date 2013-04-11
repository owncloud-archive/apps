<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
?>
<h2 id="title_general"><?php p($l->t('General')); ?></h2>
<div id="general">
	<table class="nostyle">
		<tr>
			<td>
				<label for="timezone" class="bold"><?php p($l->t('Timezone'));?></label>
				&nbsp;&nbsp;
			</td>
			<td>
				<select style="display: none;" id="timezone" name="timezone">
				<?php
				$continent = '';
				foreach($_['timezones'] as $timezone):
					$ex=explode('/', $timezone, 2);//obtain continent,city
					if (!isset($ex[1])) {
						$ex[1] = $ex[0];
						$ex[0] = "Other";
					}
					if ($continent!=$ex[0]):
						if ($continent!="") print_unescaped('</optgroup>');
						print_unescaped('<optgroup label="'.OC_Util::sanitizeHTML($ex[0]).'">');
					endif;
					$city=strtr($ex[1], '_', ' ');
					$continent=$ex[0];
					print_unescaped('<option value="'.OC_Util::sanitizeHTML($timezone).'"'.($_['timezone'] == $timezone?' selected="selected"':'').'>'.OC_Util::sanitizeHTML($city).'</option>');
					var_dump($_['timezone']);
				endforeach;?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;&nbsp;
			</td>
			<td>
				<input type="checkbox" name="timezonedetection" id="timezonedetection">
				&nbsp;
				<label for="timezonedetection"><?php p($l->t('Update timezone automatically')); ?></label>
			</td>
		</tr>
		<tr>
			<td>
				<label for="timeformat" class="bold"><?php p($l->t('Time format'));?></label>
				&nbsp;&nbsp;
			</td>
			<td>
				<select style="display: none; width: 60px;" id="timeformat" title="<?php p("timeformat"); ?>" name="timeformat">
					<option value="24" id="24h"><?php p($l->t("24h")); ?></option>
					<option value="ampm" id="ampm"><?php p($l->t("12h")); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="firstday" class="bold"><?php p($l->t('Start week on'));?></label>
				&nbsp;&nbsp;
			</td>
			<td>
				<select style="display: none;" id="firstday" title="<?php p("First day"); ?>" name="firstday">
					<option value="mo" id="mo"><?php p($l->t("Monday")); ?></option>
					<option value="su" id="su"><?php p($l->t("Sunday")); ?></option>
				</select>
			</td>
		</tr>
		<tr class="advancedsettings">
			<td>
				<label for="" class="bold"><?php p($l->t('Cache'));?></label>
				&nbsp;&nbsp;
			</td>
			<td>
				<input id="cleancalendarcache" type="button" class="button" value="<?php p($l->t('Clear cache for repeating events'));?>">
			</td>
		</tr>
	</table>
</div>
<h2 id="title_urls"><?php p($l->t('URLs')); ?></h2>
<div id="urls">
		<?php p($l->t('Calendar CalDAV syncing addresses')); ?> (<a href="http://owncloud.org/synchronisation/" target="_blank"><?php p($l->t('more info')); ?></a>)
		<dl>
		<dt><?php p($l->t('Primary address (Kontact et al)')); ?></dt>
		<dd><code><?php print_unescaped(OCP\Util::linkToRemote('caldav')); ?></code></dd>
		<dt><?php p($l->t('iOS/OS X')); ?></dt>
		<dd><code><?php print_unescaped(OCP\Util::linkToRemote('caldav')); ?>principals/<?php p(OCP\USER::getUser()); ?></code>/</dd>
		<dt><?php p($l->t('Read only iCalendar link(s)')); ?></dt>
		<dd>
			<?php foreach($_['calendars'] as $calendar) {
			if($calendar['userid'] == OCP\USER::getUser()){
				$uri = rawurlencode(html_entity_decode($calendar['uri'], ENT_QUOTES, 'UTF-8'));
			}else{
				$uri = rawurlencode(html_entity_decode($calendar['uri'], ENT_QUOTES, 'UTF-8')) . '_shared_by_' . $calendar['userid'];
			}
			?>
			<a href="<?php p(OCP\Util::linkToRemote('caldav').'calendars/'.OCP\USER::getUser().'/'.$uri) ?>?export"><?php p(OCP\Util::sanitizeHTML($calendar['displayname'])) ?></a><br />
			<?php } ?>
		</dd>
		</dl>
	</div>
</div>