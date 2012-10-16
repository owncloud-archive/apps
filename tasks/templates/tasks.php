<div id="controls">
	<input type="text" id="tasks_newtask" placeholder="<?php echo $l->t('Task'); ?>">
	<input type="button" id="tasks_addtask" value="<?php echo $l->t('Add Task'); ?>">
	<span id="sortby">
		<?php echo $l->t('Sort by'); ?>
		<select id="tasks_sort">
			<option value="dueDate"><?php echo $l->t('Due date'); ?></option>
			<option value="dueDate"><?php echo $l->t('Priority'); ?></option>
			<option value="dueDate"><?php echo $l->t('Name'); ?></option>
		</select>
	</span>
</div>
<div id="tasks_lists" class="leftcontent">
	<div class="all">All</div>
	<div class="done">Done</div>
</div>

<div class="rightcontent" id="rightcontent">
	<p class="loading"><?php echo $l->t('Loading tasks...') ?></p>
	<table id="tasks_list">
		<tbody>
			<tr id="task_template" class="task">
				<td class="completed"><input type="checkbox" /></td>
				<td><div class="priority priority-n tag"></div></td>
				<td class="overview">
					<span class="summary"></span>
					<span class="description"></span>
				</td>
				<td class="task_actions task_edit">
					<button title="<?php echo $l->t('Edit');?>" class="svg action"></button>
				</td>
				<td class="categories"></td>
				<td class="due">
					<?php echo $l->t('No due date'); ?>
				</td>
				<td class="task_actions task_edit_due">
					<button title="<?php echo $l->t('Edit due date');?>" class="svg action"></button>
				</td>
				<!--
				<td class="task_delete task_actions">
					<img class="svg action" title="<?php echo $l->t('Delete') ?>" src="<?php echo OCP\image_path('core', 'actions/delete.svg') ?>" />
				</td>
				-->
			</tr>
		</tbody>
	</table>
</div>
<script type='text/javascript'>
var categories = <?php echo json_encode($_['categories']); ?>;
</script>
