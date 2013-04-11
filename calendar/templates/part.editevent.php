<div id="event" title="<?php p($l->t("Edit an event"));?>">
	<form id="event_form">
		<input type="hidden" name="id" value="<?php p($_['eventid']) ?>">
		<input type="hidden" name="lastmodified" value="<?php p($_['lastmodified']) ?>">
<?php print_unescaped($this->inc("part.eventform")); ?>
	<div style="width: 100%;text-align: center;color: #FF1D1D;" id="errorbox"></div>
	<span id="actions">
		<input type="button" class="submit" style="float: left;" id="editEvent-submit" value="<?php p($l->t("Submit"));?>" data-link="<?php print_unescaped(OCP\Util::linkTo('calendar', 'ajax/event/edit.php')) ?>">
		<input type="button" class="submit" style="float: left;" id="editEvent-delete"  name="delete" value="<?php p($l->t("Delete"));?>" data-link="<?php print_unescaped(OCP\Util::linkTo('calendar', 'ajax/event/delete.php')) ?>">
		<input type="button" class="submit" style="float: right;" id="editEvent-export"  name="export" value="<?php p($l->t("Export"));?>" data-link="<?php print_unescaped(OCP\Util::linkTo('calendar', 'export.php')) ?>?eventid=<?php p($_['eventid']) ?>">
	</span>
	</form>
</div>
