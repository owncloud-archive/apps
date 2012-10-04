<?php

OC::$CLASSPATH['OCA_Mail\App'] = 'apps/mail/lib/mail.php';
OC::$CLASSPATH['OCA_Mail\Message'] = 'apps/mail/lib/message.php';

OCP\App::addNavigationEntry( array(
  'id' => 'mail_index',
  'order' => 1,
  'href' => OCP\Util::linkTo( 'mail', 'index.php' ),
  'icon' => OCP\Util::imagePath( 'mail', 'icon.png' ),
  'name' => 'Mail'));

OCP\App::registerPersonal('mail','settings');
