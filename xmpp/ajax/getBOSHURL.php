<?php
$val=OCP\Config::getAppValue('xmpp', 'xmppBOSHURL','');
echo json_encode($val);
?>
