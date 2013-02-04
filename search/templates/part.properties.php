<?php // note: strange formatting fixes output HTML ?>
<th><input type="checkbox" id="search-select-all"/></th>
<?php foreach($_['properties'] as $property): ?>
	    <th><?php echo ucfirst($l->t(str_replace('_', ' ', $property))); ?></th>
<?php endforeach; ?>
