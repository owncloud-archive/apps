<?php
OCP\JSON::callCheck();
$uid=OCP\User::getUser();
$dirs_stat = OCP\Config::getUserValue($uid,'files_tree','dirs_stat','');
if($dirs_stat=='') $dirs_stat=array();
else $dirs_stat=unserialize($dirs_stat);
$dirs_stat[$_REQUEST['d']]=$_REQUEST['s'];
OCP\Config::setUserValue($uid,'files_tree', 'dirs_stat', serialize($dirs_stat));
?>