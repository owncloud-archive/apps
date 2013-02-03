<div id="controls">
	<div id='breadcrumbs'></div>
	<span class="right">
		<button class="share"><?php echo $l->t("Share"); ?></button>
		<a class="share" data-item-type="gallery" data-item="" title="<?php echo $l->t("Share"); ?>"
		   data-possible-permissions="31"></a>
		<input type='button' id='slideshow-start' value='<?php echo $l->t('Slideshow');?>'> </input>
	</span>
</div>
<div id='gallery'></div>
<div id='slideshow'>
	<input type='button' class='next'/>
	<input type='button' class='previous'/>
	<input type='button' class='exit'/>
</div>
