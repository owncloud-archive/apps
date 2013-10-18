<?php
OCP\Util::addScript('files_odfviewer', 'viewer' );

if (\OCP\App::isEnabled('documents')){
	OCP\Util::addScript('files_odfviewer', 'viewer-documents' );
}
?>
