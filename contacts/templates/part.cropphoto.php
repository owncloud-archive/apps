<?php
$id = $_['id'];
$tmpkey = $_['tmpkey'];
$requesttoken = $_['requesttoken'];
?>
<?php if(OC_Cache::hasKey($tmpkey)) { ?>
<img id="cropbox" src="<?php echo OCP\Util::linkToAbsolute('contacts', 'tmpphoto.php'); ?>?tmpkey=<?php echo $tmpkey; ?>" />
<form id="cropform"
	class="coords"
	method="post"
	enctype="multipart/form-data"
	target="crop_target"
	action="<?php echo OCP\Util::linkToAbsolute('contacts', 'ajax/savecrop.php'); ?>">

	<input type="hidden" id="id" name="id" value="<?php echo $id; ?>" />
	<input type="hidden" name="requesttoken" value="<?php echo $requesttoken; ?>">
	<input type="hidden" id="tmpkey" name="tmpkey" value="<?php echo $tmpkey; ?>" />
	<fieldset id="coords">
	<input type="hidden" id="x1" name="x1" value="" />
	<input type="hidden" id="y1" name="y1" value="" />
	<input type="hidden" id="x2" name="x2" value="" />
	<input type="hidden" id="y2" name="y2" value="" />
	<input type="hidden" id="w" name="w" value="" />
	<input type="hidden" id="h" name="h" value="" />
	</fieldset>
	<iframe name="crop_target" id='crop_target' src=""></iframe>
</form>
<script type="text/javascript" src="<?php echo OC_Helper::linkTo('contacts/js', 'jcrop.js');?>"></script>
<?php
} else {
	echo $l->t('The temporary image has been removed from cache.');
}
