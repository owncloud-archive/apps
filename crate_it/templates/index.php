<div 
	<span style="font-weight:bold;font-size:large; padding:20px;">Items</span>
</div>

<div style="padding-left:20px;">
<ul>
<?php foreach($_['cart_files'] as $file):?>
	<li><?php print_unescaped($file);?>
	<input type="checkbox"></input></li>
<?php endforeach;?>
</ul>
</div>

<div style="float:left; padding:20px;">
	<input onClick="createZipPackage();" type="button" value="Package Zip"/>
	<input type="button" value="Bag It"/>
</div>

<script type="text/javascript">

function createZipPackage(){
	$.ajax(OC.linkTo('crate_it', 'ajax/zippackage.php')+'?action=zip');
	//alert("clicked packageZip...");
}

</script>
