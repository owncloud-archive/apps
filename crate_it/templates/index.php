<div>
	<span style="font-weight:bold;font-size:large; padding:20px;">Items</span>
</div>

<div style="padding-left:20px;">
<!-- don't think about hiearchi now, just create a list
and let user drag and drop -->

<ul id="crateList">
<?php foreach($_['bagged_files'] as $file):?>
	<li><?php print_unescaped($file);?>
<?php endforeach;?>
</ul>
</div>
<!-- <div id='toc'></div>  -->

<div style="float:left; padding:20px;">
	<input id="clear" type="button" value="Clear Crate"/>
	<input id="download" type="button" value="Download Crate as zip"/>
</div><br>
<div>
<?php //print_r(get_loaded_extensions())?>
</div>
