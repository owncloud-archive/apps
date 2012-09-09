<script type="text/javascript">
    Gallery.images = <?php echo json_encode($_['images']);?>
</script>
<div id="controls">
    <div id='breadcrumbs'></div>
    <input type='button' id='slideshow-start' value='<?php echo $l->t('Slideshow');?>'></input>
</div>
<div id='gallery'></div>
<!-- start supersized block -->
<div id="slideshow-content" style="display:none;">

    <!--Thumbnail Navigation-->
    <div id="prevthumb"></div>
    <div id="nextthumb"></div>

    <!--Arrow Navigation-->
    <a id="prevslide" class="load-item"></a>
    <a id="nextslide" class="load-item"></a>

    <div id="thumb-tray" class="load-item">
        <div id="thumb-back"></div>
        <div id="thumb-forward"></div>
    </div>

    <!--Time Bar-->
    <div id="progress-back" class="load-item">
        <div id="progress-bar"></div>
    </div>

    <!--Control Bar-->
    <div id="slideshow-controls-wrapper" class="load-item">
        <div id="slideshow-controls">

            <a id="play-button"><img id="pauseplay"
                                     src="<?php echo OCP\image_path('gallery', 'supersized/pause.png'); ?>"/></a>

            <!--Slide counter-->
            <div id="slidecounter">
                <span class="slidenumber"></span> / <span class="totalslides"></span>
            </div>

            <!--Slide captions displayed here-->
            <div id="slidecaption"></div>

            <!--Thumb Tray button-->
            <a id="tray-button"><img id="tray-arrow"
                                     src="<?php echo OCP\image_path('gallery', 'supersized/button-tray-up.png'); ?>"/></a>

            <!--Navigation-->
            <!--
			   <ul id="slide-list"></ul>
			   -->
        </div>
    </div>

</div>
<!-- end supersized block -->
