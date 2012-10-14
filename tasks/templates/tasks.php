<div id="controls">
	<input type="text" id="tasks_newtask">
	<input type="button" id="tasks_addtask" value="<?php echo $l->t('Add Task'); ?>">
	<input type="button" id="tasks_order_due" value="<?php echo $l->t('Order Due'); ?>">
	<input type="button" id="tasks_order_category" value="<?php echo $l->t('Order List'); ?>">
	<input type="button" id="tasks_order_complete" value="<?php echo $l->t('Order Complete'); ?>">
	<input type="button" id="tasks_order_location" value="<?php echo $l->t('Order Location'); ?>">
	<input type="button" id="tasks_order_prio" value="<?php echo $l->t('Order Priority'); ?>">
	<input type="button" id="tasks_order_label" value="<?php echo $l->t('Order Label'); ?>">
</div>
<div id="tasks_lists" class="leftcontent">
	<div class="all">All</div>
	<div class="done">Done</div>
</div>
<div id="tasks_list" class="rightcontent">
<p class="loading"><?php echo $l->t('Loading tasks...') ?></p>
</div>
<div id="task_template" class="task">
	<input type="checkbox" />
	<span class="summary"></span>
	<span class="description"></span>
	<div class="categories"></div>
	<span class="due"></span>
	<div class="task_actions">
		<span class="task_edit">
			<img class="svg action" title="<?php echo $l->t('Edit');?>" src="<?php echo OCP\image_path("", "actions/rename.svg");?>" />
		</span>
		<span class="task_delete">
			<img class="svg action" title="<?php echo $l->t('Delete') ?>" src="<?php echo OCP\image_path('core', 'actions/delete.svg') ?>" />
		</span>
	</div>
	
<script type='text/javascript'>
var categories = <?php echo json_encode($_['categories']); ?>;
</script>
