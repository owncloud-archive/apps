<script type="text/javascript">
<!--
var ocsvgFile = {
    path: <?php echo $_['filePath']; ?>,
    mtime: <?php echo $_['filemTime']; ?>,
    contents: <?php echo $_['fileContents']; ?>
};
//-->
</script>
<div id="controls">
	<input type="button" id="ocsvgBtnSave" value="<?php echo $l->t('Save'); ?>" />
	<input type="button" id="ocsvgBtnExport" value="<?php echo $l->t('Export PNG'); ?>" />
	<input type="button" id="ocsvgBtnPrefs" value="<?php echo $l->t('Preferences'); ?>" />
	<input type="button" id="ocsvgBtnClose" value="<?php echo $l->t('Close'); ?>" />
</div>
<div id="svgEditor">
    <iframe src="<?php echo OC_Helper::linkTo('files_svgedit', 'svg-edit/svg-editor.html'); ?>" id="svgedit"></iframe>
</div>
