<?php
$stmt = OCP\DB::prepare('UPDATE *PREFIX*xmpp SET rid = ? WHERE ocUser = ?');
$result = $stmt->execute(array($_POST['rid'],OCP\User::getUser()));
?>
