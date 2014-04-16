<?php

$installedVersion = OCP\Config::getAppValue('activity', 'installed_version');

if (version_compare($installedVersion, '1.1.6', '<')) {
	$type_map = array(
		1 => \OCA\Activity\Data::TYPE_SHARE_CHANGED,
		2 => \OCA\Activity\Data::TYPE_SHARE_DELETED,
		3 => \OCA\Activity\Data::TYPE_SHARE_CREATED,
		4 => \OCA\Activity\Data::TYPE_SHARED,
		5 => \OCA\Activity\Data::TYPE_SHARED,
		6 => \OCA\Activity\Data::TYPE_SHARE_CHANGED,
		7 => \OCA\Activity\Data::TYPE_SHARE_DELETED,
		8 => \OCA\Activity\Data::TYPE_SHARE_CREATED,
		9 => \OCA\Activity\Data::TYPE_SHARE_EXPIRED,
		10 => \OCA\Activity\Data::TYPE_SHARE_RESHARED,
		11 => \OCA\Activity\Data::TYPE_SHARE_RESHARED,
		12 => \OCA\Activity\Data::TYPE_SHARE_DOWNLOADED,
		13 => \OCA\Activity\Data::TYPE_SHARE_UPLOADED,
		14 => \OCA\Activity\Data::TYPE_STORAGE_QUOTA_90,
		15 => \OCA\Activity\Data::TYPE_STORAGE_FAILURE,
		16 => \OCA\Activity\Data::TYPE_SHARE_UNSHARED,
	);

	foreach ($type_map as $old_type => $new_type) {
		$query = \OC_DB::prepare('UPDATE `*PREFIX*activity` SET `type` = ? WHERE `type` = ?');
		$query->execute(array($new_type, $old_type));
	}

	// fetch from DB
	$query = \OC_DB::prepare(
		'SELECT `userid`, `configvalue` '
		. ' FROM `*PREFIX*preferences` '
		. ' WHERE `appid` = ? AND `configkey` = ? '
	);
	$result = $query->execute(array('activity', 'notify_stream'));

	$preference_map = array(
		1 => \OCA\Activity\Data::TYPE_SHARE_CHANGED,
		2 => \OCA\Activity\Data::TYPE_SHARE_DELETED,
		3 => \OCA\Activity\Data::TYPE_SHARE_CREATED,
		4 => \OCA\Activity\Data::TYPE_SHARED,
		9 => \OCA\Activity\Data::TYPE_SHARE_EXPIRED,
		10 => \OCA\Activity\Data::TYPE_SHARE_RESHARED,
		12 => \OCA\Activity\Data::TYPE_SHARE_DOWNLOADED,
		13 => \OCA\Activity\Data::TYPE_SHARE_UPLOADED,
		14 => \OCA\Activity\Data::TYPE_STORAGE_QUOTA_90,
		15 => \OCA\Activity\Data::TYPE_STORAGE_FAILURE,
		16 => \OCA\Activity\Data::TYPE_SHARE_UNSHARED,
	);

	$query = \OC_DB::prepare('INSERT INTO `*PREFIX*preferences` (`userid`, `appid`, `configkey`, `configvalue`)' . ' VALUES ( ?, ?, ?, ? )');
	while ($row = $result->fetchRow()) {
		foreach ($preference_map as $old_type => $new_type) {
			$query->execute(array(
				$row['userid'],
				'activity',
				'notify_stream_' . $new_type,
				in_array($old_type, unserialize($row['configvalue'])) ? '1' : '0',
			));
		}
	}

	$query = \OC_DB::prepare('DELETE FROM `*PREFIX*preferences` WHERE `appid` = ? AND (`configkey` = ? OR `configkey` = ?)');
	$query->execute(array('activity', 'notify_stream', 'notify_email'));
}
