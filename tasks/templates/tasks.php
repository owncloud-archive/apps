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
				<td class="summary"></td>
				<td class="description"></td>
				<td class="categories"></td>
				<td class="due"></td>
				<td class="task_actions">
					<span class="task_edit">
						<img class="svg action" title="<?php echo $l->t('Edit');?>" src="<?php echo OCP\image_path("", "actions/rename.svg");?>" />
					</span>
					<span class="task_delete">
						<img class="svg action" title="<?php echo $l->t('Delete') ?>" src="<?php echo OCP\image_path('core', 'actions/delete.svg') ?>" />
					</span>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<script type='text/javascript'>
var categories = <?php echo json_encode($_['categories']); ?>;
</script>
