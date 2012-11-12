<!--[if lte IE 8]>
	<link rel="stylesheet" href="<?php echo OC_Helper::linkTo('map', '3rdparty/leaflet/css/leaflet.ie.css'); ?>" />
<![endif]-->

<div id="map"></div>

<div id="sidebar">
	<ul class="cat_titles">
		<li><i class="star important"></i> Favorite</li>
		<li>My Contacts</li>
		<li>Events</li>
		<li>My Contacts</li>
	</ul>
</div>

<div id="search_field">
	<form>
		<input id="" class="svg" name="query" value="" placeholder="<?php echo $l->t('Search a place');?>" autocomplete="off" type="search">
		<button id="search_launch"><?php echo $l->t('Search');?></button>
	</form>
</div>