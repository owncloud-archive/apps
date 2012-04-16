<form id="tattoo" method="post">
	<fieldset class="personalblock">
		<strong><?php echo $l->t('Tattoo Wallpaper'); ?></strong><br />
		<div class="tattooTile">
			<div class="tattooTilePicture"></div>
			<input type="radio" name="tattooWallpaper" value="none"<?php if ($_['tattooSelectedWallpaper']=='none') echo ' checked="checked"'; ?>/>
		</div>
		<div class="tattooTile">
			<div class="tattooTilePicture"><img src="../apps/tattoo/img/tattoo-tattoo.png" width="71" height="71"></div>
			<input type="radio" name="tattooWallpaper" value="tattoo-tattoo.png"<?php if ($_['tattooSelectedWallpaper']=='tattoo-tattoo.png') echo ' checked="checked"'; ?>/>
		</div>
		<div class="tattooTile">
			<div class="tattooTilePicture"><img src="../apps/tattoo/img/tattoo-cat.png" width="71" height="71"></div>
			<input type="radio" name="tattooWallpaper" value="tattoo-cat.png"<?php if ($_['tattooSelectedWallpaper']=='tattoo-cat.png') echo ' checked="checked"'; ?>/>
		</div>
		<div class="tattooTile">
			<div class="tattooTilePicture"><img src="../apps/tattoo/img/tattoo-clouds.png" width="71" height="71"></div>
			<input type="radio" name="tattooWallpaper" value="tattoo-clouds.png"<?php if ($_['tattooSelectedWallpaper']=='tattoo-clouds.png') echo ' checked="checked"'; ?>/>
		</div>
		<div class="tattooTile">
			<div class="tattooTilePicture"><img src="../apps/tattoo/img/tattoo-sun.png" width="71" height="71"></div>
			<input type="radio" name="tattooWallpaper" value="tattoo-sun.png"<?php if ($_['tattooSelectedWallpaper']=='tattoo-sun.png') echo ' checked="checked"'; ?>/>
		</div>
		<br/>
		<input type="submit" name="tattooSetWallpaper" id="tattooSetWallpaper" value="Save"/>
	</fieldset>
</form>
