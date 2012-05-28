<?php

OC::$CLASSPATH['OCA_Mail\App'] = 'apps/mail/lib/mail.php';
OC::$CLASSPATH['OCA_Mail\Message'] = 'apps/mail/lib/message.php';

OCP\App::register( array(
  'order' => 1,
  'id' => 'mail',
  'name' => 'Mail' ));

OCP\App::addNavigationEntry( array(
  'id' => 'mail_index',
  'order' => 1,
  'href' => OCP\Helper::linkTo( 'mail', 'index.php' ),
  'icon' => OCP\Helper::imagePath( 'mail', 'icon.png' ),
  'name' => 'Mail'));

OCP\App::registerPersonal('mail','settings');
