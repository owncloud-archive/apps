<?php
$stmt = OCP\DB::prepare('SELECT * FROM *PREFIX*xmpp WHERE ocUser = ?');
$result = $stmt->execute(array(OCP\User::getUser()));
$val=$result->fetchRow();

echo json_encode($val);
?>
