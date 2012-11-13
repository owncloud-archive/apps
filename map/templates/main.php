<!--[if lte IE 8]>
	<link rel="stylesheet" href="<?php echo OC_Helper::linkTo('map', '3rdparty/leaflet/css/leaflet.ie.css'); ?>" />
<![endif]-->

<div id="map"></div>

<div id="sidebar">
	<ul class="cat_titles">
		<li><i class="star important"></i> Favorite</li>
		<li>My Contacts</li>
		<li>Events</li>
		<li>My places
			<ul id="pts_myplaces"></ul>
		</li>
	</ul>
</div>

<div id="search_field">
	<form>
		<input id="" class="svg" name="query" value="" placeholder="<?php echo $l->t('Search a place');?>" autocomplete="off" type="search">
		<button id="search_launch"><?php echo $l->t('Search');?></button>
	</form>
</div>



<script type="text/html" id="new_point">
	<form id="new_pnt">
		<label for="pt_name"><?php echo $l->t('Name');?></label> <input name="pt_name" placeholder="<?php echo $l->t('Name');?>" /><br />
		<label for="pt_type"><?php echo $l->t('Type');?></label> <select name="pt_type">
			<option value="places"><?php echo $l->t('Places');?></option>
			<option value="home"><?php echo $l->t('Home');?></option>
		</select><br />
		<input type="submit" value="<?php echo $l->t('Save');?>"/>
	</form>
</script>