<!--[if lte IE 8]>
	<link rel="stylesheet" href="<?php echo OC_Helper::linkTo('map', '3rdparty/leaflet/css/leaflet.ie.css'); ?>" />
<![endif]-->

<div id="map"></div>

<div id="sidebar">
	<div class="main_panel">
	<div class="cat_panel">
		<ul class="cat_titles">
			<?php foreach($_['category'] as $cat):?>
				<li data-id="<?php echo $cat['id'];?>">
					<?php if(isset($cat['icon'])):?>
						<i class="<?php echo $cat['icon'];?>"></i>
					<? endif;?>
					<a href="#" class="panel_change" data-toid="2"><?php echo $cat['name'];?> <?php if($cat['depth'] != 1) echo "&gt;";?></a>
				</li>
			<?php endforeach;?>
		</ul>
	</div>
	<div class="cat_panel">
		<ul class="cat_points">
		</ul>
			<a href="#" class="panel_change" data-toid="1">back</a>

	</div>
	</div>
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
			<option value="place"><?php echo $l->t('Places');?></option>
			<option value="home"><?php echo $l->t('Home');?></option>
		</select><br />
		<input type="submit" value="<?php echo $l->t('Save');?>"/>
	</form>
</script>