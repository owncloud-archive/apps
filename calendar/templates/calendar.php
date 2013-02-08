<script type="text/javascript" src="<?php echo OC_Helper::linkTo('calendar/js', 'l10n.php');?>"></script>

<div id="notification" style="display:none;"></div>
<div id="controls">
	<form id="view">
		<input type="button" value="<?php echo $l->t('Week');?>" id="oneweekview_radio"/>
		<input type="button" value="<?php echo $l->t('Month');?>" id="onemonthview_radio"/>
		<input type="button" value="<?php echo $l->t('List');?>" id="listview_radio"/>&nbsp;&nbsp;
		<img id="loading" src="<?php echo OCP\Util::imagePath('core', 'loading.gif'); ?>" />
	</form>
	<form id="choosecalendar">
		<!--<input type="button" id="today_input" value="<?php echo $l->t("Today");?>"/>-->
		<button class="settings generalsettings" title="<?php echo $l->t('Settings'); ?>"><img class="svg" src="<?php echo OCP\Util::imagePath('core', 'actions/settings.svg'); ?>" alt="<?php echo $l->t('Settings'); ?>" /></button>
	</form>
	<form id="datecontrol">
		<input type="button" value="&nbsp;&lt;&nbsp;" id="datecontrol_left"/>
		<input type="button" value="" id="datecontrol_date"/>
		<input type="button" value="&nbsp;&gt;&nbsp;" id="datecontrol_right"/>
	</form>
</div>
<div id="fullcalendar"></div>
<div id="dialog_holder"></div>
<div id="appsettings" class="popup topright hidden"></div>