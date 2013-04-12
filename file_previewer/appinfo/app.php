<?php
OCP\App::addNavigationEntry( array(
  'id' => 'file_previewer',
  'order' => 10,
  'href' => OCP\Util::linkTo( 'file_previewer', 'index.php' ),
  'icon' => OCP\Util::imagePath( 'file_previewer', 'active_star.svg' ),
  'name' => OC_L10N::get('file_previewer')->t('File Viewer') ));
