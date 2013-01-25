<div id="event" title="<?php echo $l->t("Create a new event");?>">
	<form id="event_form">
<?php echo $this->inc("part.eventform"); ?>
	<div style="width: 100%;text-align: center;color: #FF1D1D;" id="errorbox"></div>
	<span id="actions">
		<input type="button" id="submitNewEvent" data-link="<?php echo OCP\Util::linkTo('calendar', 'ajax/event/new.php'); ?>" class="submit" style="float: left;" value="<?php echo $l->t("Submit");?>">
	</span>
	</form>
</div>
