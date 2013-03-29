<?php

OC::$CLASSPATH['OC_INT_MESSAGES'] = 'internal_messages/lib/internalmessages.php';

OCP\Util::addscript('internal_messages','messages');
OCP\Util::addStyle ('internal_messages','style');

$unread = OC_INT_MESSAGES::unreadMessages( OCP\USER::getUser() );
if ($unread) { $name = "Messages<strong id=unread_count>(".$unread.")</strong>" ; } else { $name = "Messages" ; }

OCP\App::addNavigationEntry(
    array( 'id' => 'internal_messages_index',
           'order' => 74,
           'href' => OCP\Util::linkTo( 'internal_messages' , 'index.php' ),
           'icon' => OCP\Util::imagePath( 'internal_messages', 'message.png' ),
           'name' => $name  )
   );
