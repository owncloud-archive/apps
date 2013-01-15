<?php
OCP\App::checkAppEnabled('xmpp');

OC::$CLASSPATH['OC_User_xmpp_Hooks'] = 'apps/xmpp/lib/hooks.php';
OC::$CLASSPATH['OC_xmpp_login'] = 'apps/xmpp/lib/xmpplogin.php';
# Crear sessio xmpp
OCP\Util::connectHook('OC_User', 'post_login', "OC_User_xmpp_Hooks", "createXmppSession");
OCP\Util::connectHook('OC_User', 'logout', "OC_User_xmpp_Hooks", "deleteXmppSession");
# Crear/modificar usuari
OCP\Util::connectHook('OC_User', 'post_createUser', "OC_User_xmpp_Hooks", "createXmppUser");
OCP\Util::connectHook('OC_User', 'post_setPassword', "OC_User_xmpp_Hooks", "updateXmppUserPassword");
# Auto add roster
OCP\Util::connectHook('OC_Contacts_VCard', 'post_updateVCard', "OC_User_xmpp_Hooks", "post_updateVCard");

# Configuracions admin/user
OCP\App::registerAdmin('xmpp', 'adminSettings');
OCP\App::registerPersonal('xmpp', 'userSettings');


OCP\App::register(Array(
	'order' => 10,
	'id' => 'xmpp',
	'name' => 'xmpp'
));

# Scripts i stils xat
OCP\Util::addScript('xmpp', 'mini');
OCP\Util::addScript('xmpp', 'strophe');
OCP\Util::addStyle('xmpp', 'mini');

OCP\App::addNavigationEntry(
        array(
                'id' => 'xmpp_roster',
                'order' => 10,
                'href' => OCP\Util::linkTo('xmpp', 'manageRoster.php'),
                'icon' => OCP\Util::imagePath('xmpp', 'icon-jabber.png'),
                'name' => 'XMPP'
        )
);
