<html>
<body>
<div>
<form action="<?php print_unescaped(OCP\Util::linkTo('file_previewer', 'ajax/upload_file.php')); ?>" method="post"
enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" />
<br />
<input type="submit" name="submit" value="Submit" />
</form>
</div>
</body>
</html>