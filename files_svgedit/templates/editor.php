<script type="text/javascript">
<!--
var ocsvgFile = {
    path: <?php p($_['filePath']); ?>,
    mtime: <?php p($_['filemTime']); ?>,
    contents: <?php p($_['fileContents']); ?>
};
//-->
</script>
<div id="controls">
	<input type="button" id="ocsvgBtnSave" value="<?php p($l->t('Save')); ?>" />
	<input type="button" id="ocsvgBtnProps" value="<?php p($l->t('Properties')); ?>" />
	<input type="button" id="ocsvgBtnPrefs" value="<?php p($l->t('Preferences')); ?>" />
	<input type="button" id="ocsvgBtnPrint" value="<?php p($l->t('Print')); ?>" />
	<div class="separator"></div>
	<input type="button" id="ocsvgBtnClose" value="<?php p($l->t('Close')); ?>" />
</div>
<div id="svgEditor">
    <iframe src="<?php print_unescaped(OCP\Util::linkTo('files_svgedit', 'svg-edit/svg-editor.html')); ?>" id="svgedit"></iframe>
</div>
<div id="svgEditorSave" title="<?php p($l->t('Save')); ?>">
	<ul>
		<li><a href="#svgSave">
            <img src="<?php print_unescaped(OC_Helper::mimetypeIcon("image/svg+xml")); ?>" class="mimetypeTab" />
            <?php p($l->t('Save SVG')); ?>
        </a></li>
		<li><a href="#pngExport">
            <img src="<?php print_unescaped(OC_Helper::mimetypeIcon("image/png")); ?>" class="mimetypeTab" />
            <?php p($l->t('Export PNG')); ?>
        </a></li>
		<li><a href="#pdfExport">
            <img src="<?php print_unescaped(OC_Helper::mimetypeIcon("application/pdf")); ?>" class="mimetypeTab" />
            <?php p($l->t('Export PDF')); ?>
        </a></li>
	</ul>
	<div id="svgSave">
		<input type="text" id="svgSavePath" value="" />
		<input type="button" id="svgSaveBtn" value="<?php p($l->t('Save')); ?>" />
        <div class="separator"></div>
		<input type="button" id="svgDownloadBtn" value="<?php p($l->t('Download')); ?>" />
	</div>
	<div id="pngExport">
		<input type="button" id="canvasRenderBtn" value="<?php p($l->t('Render')); ?>">
        <div class="separator"></div>
		<strong><?php p($l->t('Preview:')); ?></strong>
		<div id="canvasPreview">
			<canvas id="exportCanvas"></canvas>
		</div>
		<input type="text" id="pngSavePath" value="" />
		<input type="button" id="pngSaveBtn" value="<?php p($l->t('Save')); ?>" />
        <div class="separator"></div>
		<input type="button" id="pngDownloadBtn" value="<?php p($l->t('Download')); ?>" />
	</div>
	<div id="pdfExport">
		<input type="button" id="pdfRenderBtn" value="<?php p($l->t('Render')); ?>" />
        <div class="separator"></div>
		<strong><?php p($l->t('Preview:')); ?></strong>
        <div id="svgtopdfPreview">
            <svg></svg>
        </div>
		<input type="text" id="pdfSavePath" value="" />
        <input type="button" id="pdfSaveBtn" value="<?php p($l->t('Save')); ?>" />
        <div class="separator"></div>
		<input type="button" id="pdfDownloadBtn" value="<?php p($l->t('Download')); ?>" />
	</div>
</div>
