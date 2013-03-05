<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('calendar/js', 'l10n.php'));?>"></script>

<div id="notification" style="display:none;"></div>
<div id="controls">
	<form id="view">
		<input type="button" value="<?php p($l->t('Week'));?>" id="oneweekview_radio"/>
		<input type="button" value="<?php p($l->t('Month'));?>" id="onemonthview_radio"/>
		<input type="button" value="<?php p($l->t('List'));?>" id="listview_radio"/>&nbsp;&nbsp;
		<img id="loading" src="<?php print_unescaped(OCP\Util::imagePath('core', 'loading.gif')); ?>" />
	</form>
	<form id="choosecalendar">
		<!--<input type="button" id="today_input" value="<?php p($l->t("Today"));?>"/>-->
		<button class="settings generalsettings" title="<?php p($l->t('Settings')); ?>"><img class="svg" src="<?php print_unescaped(OCP\Util::imagePath('core', 'actions/settings.svg')); ?>" alt="<?php p($l->t('Settings')); ?>" /></button>
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