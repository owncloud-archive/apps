<div id="event" title="<?php p($l->t("View an event"));?>">
<ul>
	<li><a href="#tabs-1"><?php p($l->t('Eventinfo')); ?></a></li>
	<li><a href="#tabs-2"><?php p($l->t('Repeating')); ?></a></li>
	<!--<li><a href="#tabs-3"><?php p($l->t('Alarm')); ?></a></li>
	<li><a href="#tabs-4"><?php p($l->t('Attendees')); ?></a></li>-->
</ul>
<div id="tabs-1">
	<table width="100%">
		<tr>
			<th width="75px"><?php p($l->t("Title"));?>:</th>
			<td>
				<?php p(isset($_['title']) ? $_['title'] : '') ?>
			</td>
		</tr>
	</table>
	<table width="100%">
		<tr>
			<th width="75px"><?php p($l->t("Category"));?>:</th>
			<td>
				<?php
				if(count($_['categories']) == 0 || $_['categories'] == '') {
					p($l->t('No categories selected'));
				}else{
					print_unescaped('<ul>');
					if(is_array($_['categories'])){
						foreach($_['categories'] as $categorie) {
							print_unescaped('<li>' . OC_Util::sanitizeHTML($categorie) . '</li>');
						}
					}else{
						print_unescaped('<li>' . OC_Util::sanitizeHTML($_['categories']) . '</li>');
					}
					print_unescaped('</ul>');
				}
				?>
			</td>
			<th width="75px">&nbsp;&nbsp;&nbsp;<?php p($l->t("Calendar"));?>:</th>
			<td>
			<?php
			$calendar = OC_Calendar_App::getCalendar($_['calendar'], false, false);
			p($calendar['displayname']) . ' ' . $l->t('of') . ' ' . $calendar['userid'];
			?>
			</td>
			<th width="75px">&nbsp;</th>
			<td>
				<input type="hidden" name="calendar" value="<?php p($_['calendar_options'][0]['id']) ?>">
			</td>
		</tr>
		<tr>
			<th width="75px"><?php p($l->t("Access Class"));?>:</th>
			<td>
			<select id="accessclass" name="accessclass">
				<?php
				print_unescaped(OCP\html_select_options(array($_['access_class_options'][$_['accessclass']]), $_['accessclass']));
				?>
			</select>
			</td>
		</tr>
	</table>
	<hr>
	<table width="100%">
		<tr>
			<th width="75px"></th>
			<td>
				<input type="checkbox"<?php if($_['allday']) {print_unescaped('checked="checked"');} ?> id="allday_checkbox" name="allday" disabled="disabled">
				<?php p($l->t("All Day Event"));?>
			</td>
		</tr>
		<tr>
			<th width="75px"><?php p($l->t("From"));?>:</th>
			<td>
				<?php p($_['startdate']);?>
				&nbsp;&nbsp; <?php p((!$_['allday'])?$l->t('at'):''); ?> &nbsp;&nbsp;
				<?php p($_['starttime']);?>
			</td>
		</tr>
		<tr>
			<th width="75px"><?php p($l->t("To"));?>:</th>
			<td>
				<?php p($_['enddate']);?>
				&nbsp;&nbsp; <?php p((!$_['allday'])?$l->t('at'):''); ?> &nbsp;&nbsp;
				<?php p($_['endtime']);?>
			</td>
		</tr>
	</table>
	<input type="button" class="submit" value="<?php p($l->t("Advanced options")); ?>" id="advanced_options_button">
	<div id="advanced_options" style="display: none;">
		<hr>
		<table>
			<tr>
				<th width="85px"><?php p($l->t("Location"));?>:</th>
				<td>
					<?php p(isset($_['location']) ? $_['location'] : '') ?>
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<th width="85px" style="vertical-align: top;"><?php p($l->t("Description"));?>:</th>
				<td>
					<?php p(isset($_['description']) ? $_['description'] : '') ?></textarea>
			</tr>
		</table>
	</div>
	</div>
<div id="tabs-2">
	<table style="width:100%">
			<tr>
				<th width="75px"><?php p($l->t("Repeat"));?>:</th>
				<td>
				<select id="repeat" name="repeat">
					<?php
					print_unescaped(OCP\html_select_options(array($_['repeat_options'][$_['repeat']]), $_['repeat']));
					?>
				</select></td>
				<td><input type="button" style="float:right;" class="submit" value="<?php p($l->t("Advanced")); ?>" id="advanced_options_button_repeat"></td>
			</tr>
		</table>
		<div id="advanced_options_repeating" style="display:none;">
			<table style="width:100%">
				<tr id="advanced_month" style="display:none;">
					<th width="75px"></th>
					<td>
						<select id="advanced_month_select" name="advanced_month_select">
							<?php
							print_unescaped(OCP\html_select_options(array($_['repeat_month_options'][$_['repeat_month']]), $_['repeat_month']));
							?>
						</select>
					</td>
				</tr>
			</table>
			<table style="width:100%">
				<tr id="advanced_year" style="display:none;">
					<th width="75px"></th>
					<td>
						<select id="advanced_year_select" name="advanced_year_select">
							<?php
							print_unescaped(OCP\html_select_options(array($_['repeat_year_options'][$_['repeat_year']]), $_['repeat_year']));
							?>
						</select>
					</td>
				</tr>
			</table>
			<table style="width:100%">
				<tr id="advanced_weekofmonth" style="display:none;">
					<th width="75px"></th>
					<td id="weekofmonthcheckbox">
						<select id="weekofmonthoptions" name="weekofmonthoptions">
							<?php
							print_unescaped(OCP\html_select_options(array($_['repeat_weekofmonth_options'][$_['repeat_weekofmonth']]), $_['repeat_weekofmonth']));
							?>
						</select>
					</td>
				</tr>
			</table>
			<table style="width:100%">
				<tr id="advanced_weekday" style="display:none;">
					<th width="75px"></th>
					<td id="weeklycheckbox">
						<select id="weeklyoptions" name="weeklyoptions[]" multiple="multiple" style="width: 150px;" title="<?php p($l->t("Select weekdays")) ?>">
							<?php
							if (!isset($_['weekdays'])) {$_['weekdays'] = array();}
							print_unescaped(OCP\html_select_options(array($_['repeat_weekly_options'][$_['repeat_weekdays']]), $_['repeat_weekdays'], array('combine'=>true)));
							?>
						</select>
					</td>
				</tr>
			</table>
			<table style="width:100%">
				<tr id="advanced_byyearday" style="display:none;">
					<th width="75px"></th>
					<td id="byyeardaycheckbox">
						<select id="byyearday" name="byyearday[]" multiple="multiple" title="<?php p($l->t("Select days")) ?>">
							<?php
							if (!isset($_['repeat_byyearday'])) {$_['repeat_byyearday'] = array();}
							print_unescaped(OCP\html_select_options(array($_['repeat_byyearday_options'][$_['repeat_byyearday']]), $_['repeat_byyearday'], array('combine'=>true)));
							?>
						</select><?php p($l->t('and the events day of year.')); ?>
					</td>
				</tr>
			</table>
			<table style="width:100%">
				<tr id="advanced_bymonthday" style="display:none;">
					<th width="75px"></th>
					<td id="bymonthdaycheckbox">
						<select id="bymonthday" name="bymonthday[]" multiple="multiple" title="<?php p($l->t("Select days")) ?>">
							<?php
							if (!isset($_['repeat_bymonthday'])) {$_['repeat_bymonthday'] = array();}
							print_unescaped(OCP\html_select_options(array($_['repeat_bymonthday_options'][$_['repeat_bymonthday']]), $_['repeat_bymonthday'], array('combine'=>true)));
							?>
						</select><?php p($l->t('and the events day of month.')); ?>
					</td>
				</tr>
			</table>
			<table style="width:100%">
				<tr id="advanced_bymonth" style="display:none;">
					<th width="75px"></th>
					<td id="bymonthcheckbox">
						<select id="bymonth" name="bymonth[]" multiple="multiple" title="<?php p($l->t("Select months")) ?>">
							<?php
							if (!isset($_['repeat_bymonth'])) {$_['repeat_bymonth'] = array();}
							print_unescaped(OCP\html_select_options(array($_['repeat_bymonth_options'][$_['repeat_bymonth']]), $_['repeat_bymonth'], array('combine'=>true)));
							?>
						</select>
					</td>
				</tr>
			</table>
			<table style="width:100%">
				<tr id="advanced_byweekno" style="display:none;">
					<th width="75px"></th>
					<td id="bymonthcheckbox">
						<select id="byweekno" name="byweekno[]" multiple="multiple" title="<?php p($l->t("Select weeks")) ?>">
							<?php
							if (!isset($_['repeat_byweekno'])) {$_['repeat_byweekno'] = array();}
							print_unescaped(OCP\html_select_options(array($_['repeat_byweekno_options'][$_['repeat_byweekno']]), $_['repeat_byweekno'], array('combine'=>true)));
							?>
						</select><?php p($l->t('and the events week of year.')); ?>
					</td>
				</tr>
			</table>
			<table style="width:100%">
				<tr>
					<th width="75px"><?php p($l->t('Interval')); ?>:</th>
					<td>
						<?php p(isset($_['repeat_interval']) ? $_['repeat_interval'] : '1'); ?>
					</td>
				</tr>
				<tr>
					<th width="75px"><?php p($l->t('End')); ?>:</th>
					<td>
						<select id="end" name="end">
							<?php
							if($_['repeat_end'] == '') $_['repeat_end'] = 'never';
							print_unescaped(OCP\html_select_options(array($_['repeat_end_options'][$_['repeat_end']]), $_['repeat_end']));
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th></th>
					<td id="byoccurrences" style="display:none;">
						<?php p($_['repeat_count'] . ' ' . $l->t('occurrences')); ?>
					</td>
				</tr>
				<tr>
					<th></th>
					<td id="bydate" style="display:none;">
						<?php p($_['repeat_date']); ?>
					</td>
				</tr>
			</table>
		</div>
</div>
<!--<div id="tabs-3">//Alarm</div>
<div id="tabs-4">//Attendees</div>-->

</div>