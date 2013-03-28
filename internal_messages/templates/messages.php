
<div id="controls">
    <form id="view" href="javascript:void('')">
        <input type="button" value="<?php echo $l->t(' Back ');?>" id="back_btn"/>
        <input type="button" value="<?php echo $l->t(' Write Message ');?>" id="create_message"/>
        <div class="separator"></div>
        <label for="search_messages">Search:</label><input type="text" id="search_messages">
        <img id="loading" src="<?php echo OCP\Util::imagePath('core', 'loading.gif'); ?>" />
    </form>
</div>

<div id="messages_wall">

<?php
    print $_['data'];
?>

</div>

<div id="dialog_holder"></div>
