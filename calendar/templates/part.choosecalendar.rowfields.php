<td width="20px">
  <?php if($_['calendar']['userid'] == OCP\USER::getUser()) { ?>
  <input type="checkbox" id="active_<?php echo $_['calendar']['id'] ?>" class="activeCalendar" data-id="<?php echo $_['calendar']['id'] ?>" <?php echo $_['calendar']['active'] ? ' checked="checked"' : '' ?>>
  <?php } ?>
</td>
<td id="<?php echo OCP\USER::getUser() ?>_<?php echo $_['calendar']['id'] ?>">
  <label for="active_<?php echo $_['calendar']['id'] ?>"><?php echo $_['calendar']['displayname'] ?></label>
</td>
<td width="20px">
  <?php if($_['calendar']['permissions'] & OCP\PERMISSION_SHARE) { ?>
  <a href="#" class="share" data-item-type="calendar" data-item="<?php echo $_['calendar']['id']; ?>"
	data-possible-permissions="<?php echo $_['calendar']['permissions'] ?>"
	title="<?php echo $l->t('Share Calendar') ?>" class="action"><img class="svg action" src="<?php echo (!$_['shared']) ? OCP\Util::imagePath('core', 'actions/share.svg') : OCP\Util::imagePath('core', 'actions/shared.svg') ?>"></a>
  <?php } ?>
</td>
<td width="20px">
<?php
if($_['calendar']['userid'] == OCP\USER::getUser()){
	$caldav = rawurlencode(html_entity_decode($_['calendar']['uri'], ENT_QUOTES, 'UTF-8'));
}else{
	$caldav = rawurlencode(html_entity_decode($_['calendar']['uri'], ENT_QUOTES, 'UTF-8')) . '_shared_by_' . $_['calendar']['userid'];
}
?>
  <a href="#" id="chooseCalendar-showCalDAVURL" data-user="<?php echo OCP\USER::getUser() ?>" data-caldav="<?php echo $caldav ?>" title="<?php echo $l->t('CalDav Link') ?>" class="action"><img class="svg action" src="<?php echo OCP\Util::imagePath('core', 'actions/public.svg') ?>"></a>
</td>
<td width="20px">
  <a href="<?php echo OCP\Util::linkTo('calendar', 'export.php') . '?calid=' . $_['calendar']['id'] ?>" title="<?php echo $l->t('Download') ?>" class="action"><img class="svg action" src="<?php echo OCP\Util::imagePath('core', 'actions/download.svg') ?>"></a>
</td>
<td width="20px">
  <?php if($_['calendar']['permissions'] & OCP\PERMISSION_UPDATE) { ?>
  <a href="#" id="chooseCalendar-edit" data-id="<?php echo $_['calendar']['id'] ?>" title="<?php echo $l->t('Edit') ?>" class="action"><img class="svg action" src="<?php echo OCP\Util::imagePath('core', 'actions/rename.svg') ?>"></a>
  <?php } ?>
</td>
<td width="20px">
  <?php if($_['calendar']['permissions'] & OCP\PERMISSION_DELETE) { ?>
  <a href="#"  id="chooseCalendar-delete" data-id="<?php echo $_['calendar']['id'] ?>" title="<?php echo $l->t('Delete') ?>" class="action"><img class="svg action" src="<?php echo OCP\Util::imagePath('core', 'actions/delete.svg') ?>"></a>
  <?php } ?>
</td>
