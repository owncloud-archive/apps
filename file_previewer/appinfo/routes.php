<?php
$this->create('previewer', '{link}')
	->requirements(array('link' => '.*'))
	->actionInclude('file_previewer/docViewer.php');

$this->create('package_downloader', '{fname}')
	->requirements(array('fname' => '.*'))
	->actionInclude('file_previewer/download.php');
