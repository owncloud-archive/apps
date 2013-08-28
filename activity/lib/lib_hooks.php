<?php

/**
 * ownCloud - Activities App
 *
 * @author Frank Karlitschek
 * @copyright 2013 Frank Karlitschek frank@owncloud.org
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace OCA\Activity;

/**
 * @brief The class to handle the filesystem hooks
 */
class Hook {

	/**
	* @brief Registers the filesystem hooks for basic filesystem operations. All other events has to be triggered by the apps.
	*/
	public static function register() {

		//Listen to create file signal
		\OCP\Util::connectHook('OC_Filesystem', 'post_create', "OCA\Activity\Hook", "file_create");

		//Listen to delete file signal
		\OCP\Util::connectHook('OC_Filesystem', 'delete', "OCA\Activity\Hook", "file_delete");

		//Listen to write file signal
		\OCP\Util::connectHook('OC_Filesystem', 'post_write', "OCA\Activity\Hook", "file_write");

	}

	/**
	 * @brief Store the write hook events
	 * @param $params The hook params
	 */
	public static function file_write($params) {
		error_log('write hook'.$params['path']);
		\OCA\Activity\Data::send('files', $params['path'].' changed', '', $params['path'], $params['path']);
	}

	/**
	 * @brief Store the delete hook events
	 * @param $params The hook params
	 */
	public static function file_delete($params) {
		error_log('delete hook');
		\OCA\Activity\Data::send('files', $params['path'].' deleted', '', $params['path'], $params['path']);
	}


	/**
	 * @brief Store the create hook events
	 * @param @param $params The hook params
	 */
	public static function file_create($params) {
		error_log('create hook');
		\OCA\Activity\Data::send('files', $params['path'].' created', '', $params['path'], $params['path']);
	}
	



}
