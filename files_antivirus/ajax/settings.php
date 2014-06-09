<?php
/**
 * Copyright (c) 2014 Victor Dubiniuk <victor.dubiniuk@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

\OCP\JSON::checkAdminUser();
\OCP\JSON::callCheck();

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action){
	case 'list':
		$query = \OCP\DB::prepare('SELECT * FROM `*PREFIX*files_antivirus_status`');
		$result = $query->execute(array());
		$statuses = $result->fetchAll();
		\OCP\JSON::success(array('statuses'=>$statuses));
		break;
	case 'clear':
		$query = \OCP\DB::prepare('DELETE FROM `*PREFIX*files_antivirus_status`');
		$query->execute(array());
		\OCP\JSON::success();
		break;
	case 'reset':
		$query = \OCP\DB::prepare('DELETE FROM `*PREFIX*files_antivirus_status`');
		$query->execute(array());
		\OCA\Files_Antivirus\Status::init();
		\OCP\JSON::success();
		break;
	case 'save':
		$ruleId = isset($_POST['id']) ? intval($_POST['id']) : false;
		$statusType = isset($_POST['status_type']) ? intval($_POST['status_type']) : 0;
		
		if ($statusType === \OCA\Files_Antivirus\Status::STATUS_TYPE_CODE){
			$field = 'result';
		} else {
			$field = 'match';
		}
		$data = array();
		$data[] = $statusType;
		$data[] = isset($_POST['match']) ? $_POST['match'] : '';
		$data[] = isset($_POST['description']) ? $_POST['description'] : '';
		$data[] = isset($_POST['status']) ? intval($_POST['status']) : 0;
		if ($ruleId){
			$data[] = $ruleId;
			$query = \OCP\DB::prepare('UPDATE `*PREFIX*files_antivirus_status` SET `status_type`=(?),'
				.' `'. $field .'`=(?), `description`=(?), `status`=(?) WHERE `id`=?');
		} else {
			$query = \OCP\DB::prepare('INSERT INTO `*PREFIX*files_antivirus_status` (`status_type`,'
				.' `'. $field .'`, `description`, `status`) VALUES (?, ?, ?, ?)');
		}
		
		$query->execute($data);
		$result = array();
		if (!$ruleId){
			$result['id'] = \OCP\DB::insertid('`*PREFIX*files_antivirus_status`');
		}
		
		\OCP\JSON::success($result);
		break;
	case 'delete':
		$ruleId = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$query = \OCP\DB::prepare('DELETE FROM `*PREFIX*files_antivirus_status` WHERE `id`=?');
		$query->execute(array($ruleId));
		\OCP\JSON::success();
		break;
	default:
		break;
}
exit();