<?php

OC::$CLASSPATH['App_Mail'] = 'apps/mail/lib/mail.php';
OC::$CLASSPATH['App_Mail_Message'] = 'apps/mail/lib/message.php';

OC_App::register( array(
  'order' => 1,
  'id' => 'mail',
  'name' => 'Mail' ));

OC_App::addNavigationEntry( array(
  'id' => 'mail_index',
  'order' => 1,
  'href' => OC_Helper::linkTo( 'mail', 'index.php' ),
  'icon' => OC_Helper::imagePath( 'mail', 'icon.png' ),
  'name' => 'Mail'));

OC_APP::registerPersonal('mail','settings');
