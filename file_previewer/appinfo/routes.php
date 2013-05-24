<?php
$this->create('previewer', '{link}')
	->requirements(array('link' => '.*'))
	->actionInclude('file_previewer/docViewer.php');
