<?php
$this->create('previewer', '{fname}')
	->requirements(array('fname' => '.*'))
	->actionInclude('file_previewer/docViewer.php');

$this->create('package_downloader', '{fname}')
	->requirements(array('fname' => '.*'))
	->actionInclude('file_previewer/download.php');
