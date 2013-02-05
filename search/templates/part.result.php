<?php // note: strange formatting fixes output HTML  ?>
<tr data-id="<?php echo $_['result']->id; ?>">
    <td><input type="checkbox"/></td>
    <?php foreach ($_['columns'] as $property): ?>
        <td class="<?php echo 'search_property_' . $property; ?>"><?php echo @$_['result']->$property; ?></td>
    <?php endforeach; ?>
</tr>