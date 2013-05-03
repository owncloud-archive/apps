<ul>
<?php foreach($_['cart_files'] as $file):?>
	<li><?php print_unescaped($file);?>
	<input type="checkbox"></input></li>
<?php endforeach;?>
</ul>
