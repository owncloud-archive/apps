<div id="container">
<div>
<div style="padding-top:20px">
	<span id="crateName" style="font-weight:bold;font-size:large; padding-left:10px;"><?php echo $_['selected_crate'] ?></span>
</div>

<div style="float:left; padding-left:20px; padding-top:5px;">
<!-- don't think about hiearchi now, just create a list
and let user drag and drop -->

<table id="cratesTable">
<tbody id="crateList">
<?php foreach($_['bagged_files'] as $entry):?>
	<tr id="<?php echo $entry['id'];?>">
	<td><span class="title" style="padding-right: 150px;"><?php print_unescaped($entry['title']);?></span></td>
	<!-- <td><div style="padding-right: 22px;"><a data-action="view">View</a></div></td> -->
	<td><div><a data-action="delete" title="Delete">
	   <img src="/owncloud/core/img/actions/delete.svg"></a></div></td>
	</tr>
<?php endforeach;?>
</tbody>
</table>
</div>

<div id="anzsrc_for" style="float:right;">
<div>
<select id="for_top_level" class="for_codes">
	<option id="select_top" value="for_top_choose">Choose a code</option> 
	<?php 
		foreach($_['top_for'] as $item):
		$vars = get_object_vars($item);
		//$prefLabel = $vars['skos:prefLabel'];
	?>
	<option id="<?php echo $vars['rdf:about'];?>" value="<?php echo $vars['rdf:about'];?>"><?php echo $vars['skos:prefLabel']?></option>
	<?php endforeach;?>
</select>
</div>
<div>
<select id="for_second_level" class="for_codes">
	<option id="select_second" value="for_second_choose">Choose a code</option>
</select>
</div>
<div>
<select id="for_third_level" class="for_codes">
	<option id="select_third" value="for_third_choose">Choose a code</option>
</select>
</div>
</div>
</div></div>

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
	<!-- <input id="epub" type="button" value="EPUB"/> -->
	<input id="download" type="button" value="Download Crate as zip"/>
</div>
<div>
<?php //print_r(get_loaded_extensions())?>
</div>
