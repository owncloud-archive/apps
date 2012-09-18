<?php
$calid = isset($_['calendar']) ? $_['calendar'] : null;
$eventid = isset($_['eventid']) ? $_['eventid'] : null;

$calsharees = array();
$eventsharees = array();

$sharedwithByCalendar = OCP\Share::getItemShared('calendar', $calid);
$sharedwithByEvent = OCP\Share::getItemShared('event', $eventid);

if(is_array($sharedwithByCalendar)) {
	foreach($sharedwithByCalendar as $share) {
		if($share['share_type'] == OCP\Share::SHARE_TYPE_USER || $share['share_type'] == OCP\Share::SHARE_TYPE_GROUP) {
			$calsharees[] = $share;
		}
	}
}
if(is_array($sharedwithByEvent)) {
	foreach($sharedwithByEvent as $share) {
		if($share['share_type'] == OCP\Share::SHARE_TYPE_USER || $share['share_type'] == OCP\Share::SHARE_TYPE_GROUP) {
			$eventsharees[] = $share;
		}
	}
}
?>

<label for="sharewith"><?php echo $l->t('Share with:'); ?></label>
<input type="text" id="sharewith" data-item-source="<?php echo $eventid; ?>" /><br />

<strong><?php echo $l->t('Shared with'); ?></strong>
<ul class="sharedby eventlist">
<?php foreach($eventsharees as $sharee): ?>
	<li data-share-with="<?php echo $sharee['share_with']; ?>"
		data-item="<?php echo $eventid; ?>"
		data-item-type="event"
		data-permissions="<?php echo $sharee['permissions']; ?>"
		data-share-type="<?php echo $sharee['share_type']; ?>">
		<?php echo $sharee['share_with'] . ' (' . ($sharee['share_type'] == OCP\Share::SHARE_TYPE_USER ? 'user' : 'group'). ')'; ?>
		<span class="shareactions">
			<input class="update" type="checkbox" <?php echo ($sharee['permissions'] & OCP\Share::PERMISSION_UPDATE?'checked="checked"':'')?>
				title="<?php echo $l->t('Editable'); ?>">
			<input class="share" type="checkbox" <?php echo ($sharee['permissions'] & OCP\Share::PERMISSION_SHARE?'checked="checked"':'')?>
				title="<?php echo $l->t('Shareable'); ?>">
			<input class="delete" type="checkbox" <?php echo ($sharee['permissions'] & OCP\Share::PERMISSION_DELETE?'checked="checked"':'')?>
				title="<?php echo $l->t('Deletable'); ?>">
			<img src="<?php echo OCP\Util::imagePath('core', 'actions/delete.svg'); ?>" class="svg action delete"
				title="<?php echo $l->t('Unshare'); ?>">
		</span>
	</li>
<?php endforeach; ?>
</ul>
<?php if(!$eventsharees) {
	echo '<div id="sharedWithNobody">' . $l->t('Nobody') . '</div>';
} ?>
<br />
<strong><?php echo $l->t('Shared via calendar'); ?></strong>
<ul class="sharedby calendarlist">
<?php foreach($calsharees as $sharee): ?>
	<li data-share-with="<?php echo $sharee['share_with']; ?>"
		data-item="<?php echo $calid; ?>"
		data-item-type="calendar"
		data-permissions="<?php echo $sharee['permissions']; ?>"
		data-share-type="<?php echo $sharee['share_type']; ?>">
		<?php echo $sharee['share_with'] . ' (' . ($sharee['share_type'] == OCP\Share::SHARE_TYPE_USER ? 'user' : 'group'). ')'; ?>
		<span class="shareactions">
			<input class="update" type="checkbox" <?php echo ($sharee['permissions'] & OCP\Share::PERMISSION_UPDATE?'checked="checked"':'')?>
				title="<?php echo $l->t('Editable'); ?>">
			<input class="share" type="checkbox" <?php echo ($sharee['permissions'] & OCP\Share::PERMISSION_SHARE?'checked="checked"':'')?>
				title="<?php echo $l->t('Shareable'); ?>">
			<input class="delete" type="checkbox" <?php echo ($sharee['permissions'] & OCP\Share::PERMISSION_DELETE?'checked="checked"':'')?>
				title="<?php echo $l->t('Deletable'); ?>">
			<img src="<?php echo OCP\Util::imagePath('core', 'actions/delete.svg'); ?>" class="svg action delete"
				title="<?php echo $l->t('Unshare'); ?>">
		</span>
	</li>
<?php endforeach; ?>
</ul>
<br />
<?php echo $l->t('NOTE: Actions on events shared via calendar will affect the entire calendar sharing.'); ?>