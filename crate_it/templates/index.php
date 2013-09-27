<div style="padding-top:20px">
	<span id="crateName" title="Double click to edit..." style="font-weight:bold;font-size:large; padding-left:20px;"><?php echo $_['selected_crate'] ?></span>
</div>

<div style="padding-left:20px;padding-top:5px">
<!-- don't think about hiearchi now, just create a list
and let user drag and drop -->

<ul id="crateList" style="min-width:35%;display:inline-block;">
<?php foreach($_['bagged_files'] as $entry):?>
	<li id="<?php echo $entry['id'];?>"><span id="<?php echo $entry['id'];?>" style="padding-right: 10px;"><?php print_unescaped($entry['title']);?></span>
	<a id="<?php echo $entry['id'];?>" data-action="delete" title="Delete" style="float:right;">
	   <img class="svg" src="/owncloud/core/img/actions/delete.svg"></a>
	<a id="<?php echo $entry['id'];?>" style="float:right;">View</a>
	</li>
<?php endforeach;?>
</ul>
</div>

<div style="float:left; padding:20px;">
	<form id="crate_input" method="get">
		Create new crate: <input type="text" id="create">
		<input id="subbutton" type="submit" value="Submit">
	</form>
	<select id="crates">
		<option id="choose" value="choose">Choose a crate</option>
		<?php foreach($_['crates'] as $crate):?>
		<option id="<?php echo $crate;?>" value="<?php echo $crate;?>" <?php if($_['selected_crate'] == $crate){echo 'selected';}?>><?php echo $crate;?></option>
		<?php endforeach;?>
	</select>
	<input id="clear" type="button" value="Clear Crate"/>
	<input id="epub" type="button" value="EPUB"/>
	<input id="download" type="button" value="Download Crate as zip"/>
</div><br>
<div>
<?php //print_r(get_loaded_extensions())?>
</div>
