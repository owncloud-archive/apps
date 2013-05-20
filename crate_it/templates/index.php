<div>
	<span style="font-weight:bold;font-size:large; padding:20px;">Items</span>
</div>

<div style="padding-left:20px;">
<ul id="crateList">
<?php foreach($_['bagged_files'] as $file):?>
	<li><?php print_unescaped($file);?>
<?php endforeach;?>
</ul>
</div>

<div style="float:left; padding:20px;">
	<input id="clear" type="button" value="Clear Crate"/>
	<input id="download" type="button" value="Download Crate as zip"/>
</div><br>
<div>
<?php //print_r(get_loaded_extensions())?>
</div>

<script type="text/javascript">

</script>
