<td width="20px">
  <?php if($_['calendar']['userid'] == OCP\USER::getUser()) { ?>
  <input type="checkbox" id="active_<?php p($_['calendar']['id']) ?>" class="activeCalendar" data-id="<?php p($_['calendar']['id']) ?>" <?php print_unescaped($_['calendar']['active'] ? ' checked="checked"' : '') ?>>
  <?php } ?>
</td>
<td id="<?php p(OCP\USER::getUser()) ?>_<?php p($_['calendar']['id']) ?>">
  <label for="active_<?php p($_['calendar']['id']) ?>"><?php p($_['calendar']['displayname']) ?></label>
</td>
<td width="20px">
  <?php if($_['calendar']['permissions'] & OCP\PERMISSION_SHARE) { ?>
  <a href="#" class="share" data-item-type="calendar" data-item="<?php p($_['calendar']['id']); ?>"
	data-possible-permissions="<?php p($_['calendar']['permissions']) ?>"
	title="<?php p($l->t('Share Calendar')) ?>" class="action permanent"><img class="svg" src="<?php print_unescaped((!$_['shared']) ? OCP\Util::imagePath('core', 'actions/share.svg') : OCP\Util::imagePath('core', 'actions/shared.svg')) ?>"></a>
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
  <a href="#" id="chooseCalendar-showCalDAVURL" data-user="<?php p(OCP\USER::getUser()) ?>" data-caldav="<?php p($caldav) ?>" title="<?php p($l->t('CalDav Link')) ?>" class="action permanent"><img class="svg" src="<?php p(OCP\Util::imagePath('core', 'actions/public.svg')) ?>"></a>
</td>
<td width="20px">
  <a href="<?php print_unescaped(OCP\Util::linkTo('calendar', 'export.php') . '?calid=' . $_['calendar']['id']) ?>" title="<?php p($l->t('Download')) ?>" class="action"><img class="svg action" src="<?php p(OCP\Util::imagePath('core', 'actions/download.svg')) ?>"></a>
</td>
<td width="20px">
  <?php if($_['calendar']['permissions'] & OCP\PERMISSION_UPDATE) { ?>
  <a href="#" id="chooseCalendar-edit" data-id="<?php p($_['calendar']['id']) ?>" title="<?php p($l->t('Edit')) ?>" class="action"><img class="svg action" src="<?php p(OCP\Util::imagePath('core', 'actions/rename.svg')) ?>"></a>
  <?php } ?>
</td>
<td width="20px">
  <?php if($_['calendar']['permissions'] & OCP\PERMISSION_DELETE) { ?>
  <a href="#"  id="chooseCalendar-delete" data-id="<?php p($_['calendar']['id']) ?>" title="<?php p($l->t('Delete')) ?>" class="action"><img class="svg action" src="<?php p(OCP\Util::imagePath('core', 'actions/delete.svg')) ?>"></a>
  <?php } ?>
</td>
