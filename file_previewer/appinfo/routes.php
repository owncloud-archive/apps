<?php
/*$this->create('previewer', '{fname}')
	->requirements(array('fname' => '.*'))
	->actionInclude('file_previewer/docViewer.php');*/

$this->create('preview_handler', '{fname}')
	->requirements(array('fname' => '.*'))
	->actionInclude('file_previewer/preview_handler.php');
