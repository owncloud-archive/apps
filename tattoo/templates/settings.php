<form id="tattoo" method="post">
	<fieldset class="personalblock">
		<strong><?php p($l->t('Tattoo Wallpaper')); ?></strong><br />
		<div class="tattooTile">
			<div class="tattooTilePicture"></div>
			<input type="radio" name="tattooWallpaper" value="none"<?php if ($_['tattooSelectedWallpaper']=='none') print_unescaped(' checked="checked"'); ?>/>
		</div>
		<div class="tattooTile">
			<div class="tattooTilePicture"><img src="<?php print_unescaped(OCP\image_path('tattoo', 'tattoo-tattoo.png')); ?>" width="71" height="71"></div>
			<input type="radio" name="tattooWallpaper" value="tattoo-tattoo.png"<?php if ($_['tattooSelectedWallpaper']=='tattoo-tattoo.png') print_unescaped(' checked="checked"'); ?>/>
		</div>
		<div class="tattooTile">
			<div class="tattooTilePicture"><img src="<?php print_unescaped(OCP\image_path('tattoo', 'tattoo-cat.png')); ?>" width="71" height="71"></div>
			<input type="radio" name="tattooWallpaper" value="tattoo-cat.png"<?php if ($_['tattooSelectedWallpaper']=='tattoo-cat.png') print_unescaped(' checked="checked"'); ?>/>
		</div>
		<div class="tattooTile">
			<div class="tattooTilePicture"><img src="<?php print_unescaped(OCP\image_path('tattoo', 'tattoo-clouds.png')); ?>" width="71" height="71"></div>
			<input type="radio" name="tattooWallpaper" value="tattoo-clouds.png"<?php if ($_['tattooSelectedWallpaper']=='tattoo-clouds.png') print_unescaped(' checked="checked"'); ?>/>
		</div>
		<div class="tattooTile">
			<div class="tattooTilePicture"><img src="<?php print_unescaped(OCP\image_path('tattoo', 'tattoo-sun.png')); ?>" width="71" height="71"></div>
			<input type="radio" name="tattooWallpaper" value="tattoo-sun.png"<?php if ($_['tattooSelectedWallpaper']=='tattoo-sun.png') print_unescaped(' checked="checked"'); ?>/>
		</div>
		<br/>
		<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>">
		<input type="submit" name="tattooSetWallpaper" id="tattooSetWallpaper" value="Save"/>
	</fieldset>
</form>
