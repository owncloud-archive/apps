<?php

$tmpl = null;

$email = OCP\Config::getUserValue(OCP\User::getUser(), 'settings', 'email');
//no email address set
if($email === null) {
  $tmpl = new OCP\Template( 'mozilla_sync', 'noemail');

}
else{
  $tmpl = new OCP\Template( 'mozilla_sync', 'settings');
  $tmpl->assign('email', $email);
  $tmpl->assign('syncaddress', OCA_mozilla_sync\Utils::getServerAddress());
}

return $tmpl->fetchPage();
