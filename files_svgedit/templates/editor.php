<?php
// load required style sheets:
OC_Util::addStyle('files_svgedit', 'ocsvg');
// load required javascripts:
OC_Util::addScript('files_svgedit', 'svg-edit/embedapi');
OC_Util::addScript('files_svgedit', 'ocsvgEditor');
?>

<script type="text/javascript">
<!--
var ocsvgFile = {
    path: <?php echo $_['filePath']; ?>,
    mtime: <?php echo $_['filemTime']; ?>,
    contents: <?php echo $_['fileContents']; ?>
};
//-->
</script>
<!--
<div id="controls">
    <input type="button" id="ocsvgBtnSave" value="<?php echo $l->t('Save'); ?>" />
    <input type="button" id="ocsvgBtnPrefs" value="<?php echo $l->t('Preferences'); ?>" />
</div>
-->
<div id="editor">
    <iframe src="<?php echo OC_Helper::linkTo('files_svgedit', 'svg-edit/svg-editor.html'); ?>" id="svgedit"></iframe>
</div>
