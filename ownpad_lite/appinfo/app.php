<?php

OCP\App::addNavigationEntry( array(
  'id' => 'ownpad_lite_index',
  'order' => 90,
  'href' => OCP\Util::linkTo( 'ownpad_lite', 'index.php' ),
  'icon' => OCP\Util::imagePath( 'settings', 'users.svg' ),
  'name' => OC_L10N::get('ownpad_lite')->t('My pad') ));
