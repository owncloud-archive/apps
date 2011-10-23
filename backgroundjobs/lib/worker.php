<?php

/**
* ownCloud - Background Job
*
* @author Jakob Sack
* @copyright 2011 Jakob Sack owncloud@jakobsack.de
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
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

/**
 * This class does the dirty work.
 */
class OC_Backgroundjobs_Worker{
	public static function executeScheduledJobs(){
		// get the run id
		$run = OC_Appconfig::getValue('backgroundjobs','run',1) + 1;
		OC_Appconfig::setValue('backgroundjobs','run',$run);

		// Search for backgroundjob.php
		$apps = OC_App::get();

		foreach($apps as $app){
			$path = $app['name'];
			// App exists?
			if( !file_exists( $path )){
				$path = 'apps/'.$path;
				if( !file_exists( $path )){
					continue;
				}
			}

			if( !file_exists( $path.'/appinfo' ) || !file_exists( $path.'/appinfo/backgroundjobs.php' )){
				continue;
			}

			$RUN = $run;
			require $path;
		}
	}

	public static function executeQueuedJobs(){
		$jobs = OC_Backgroundjobs_Queue::all();

		foreach( $jobs as $job ){
			call_user_func( array( $job['class'], $job['method'] ), $job['parameters'] );
			OC_Backgroundjobs_Queue::delete($job['id']);
		}
	}
}
