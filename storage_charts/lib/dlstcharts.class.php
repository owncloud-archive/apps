<?php

/**
* ownCloud - DjazzLab Storage Charts plugin
*
* @author Xavier Beurois
* @copyright 2012 Xavier Beurois www.djazz-lab.net
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

/**
 * This class manages storage_graph. 
 */
class OC_DLStCharts {
	
	/**
	 * UPDATE day use for a user
	 * @param $used user used space
	 * @param $total total users used space
	 */
	public static function update($used, $total){
		$query = OC_DB::prepare("SELECT stc_id FROM *PREFIX*dlstcharts WHERE oc_uid = ? AND stc_dayts = ?");
		$result = $query->execute(Array(OC_User::getUser(), mktime(0,0,0)))->fetchAll();
		if(count($result) > 0){
			$query = OC_DB::prepare("UPDATE *PREFIX*dlstcharts SET stc_used = ?, stc_total = ? WHERE stc_id = ?");
			$query->execute(Array($used, $total, $result[0]['stc_id']));
		}else{
			$query = OC_DB::prepare("INSERT INTO *PREFIX*dlstcharts (oc_uid,stc_dayts,stc_used,stc_total) VALUES (?,?,?,?)");
			$query->execute(Array(OC_User::getUser(), mktime(0,0,0), $used, $total));
		}
	}
	
	/**
	 * Get the size of the data folder
	 * @param $path path to the folder you want to calculate the total size
	 */
	public static function getTotalDataSize($path){
		if(is_file($path)){
			$path = dirname($path);
		}
		$path = str_replace('//', '/', $path);
		if(is_dir($path) and strcmp(substr($path, -1), '/') != 0){
			$path .= '/';
		}
		$size = 0;
		if($dh = opendir($path)){
			while(($filename = readdir($dh)) !== false) {
				if(strcmp($filename, '.') != 0 and strcmp($filename, '..') != 0){
					$subFile = $path . '/' . $filename;
					if(is_file($subFile)){
						$size += filesize($subFile);
					}else{
						$size += self::getTotalDataSize($subFile);
					}
				}
			}
		}
		return $size;
	}
	
	/**
	 * Get data to build the pie about the Free-Used space ratio
	 */
	public static function getPieFreeUsedSpaceRatio(){
		if(OC_Group::inGroup(OC_User::getUser(), 'admin')){
			$query = OC_DB::prepare("SELECT stc_id, MAX(stc_dayts) as stc_dayts FROM *PREFIX*dlstcharts GROUP BY oc_uid");
			$results = $query->execute()->fetchAll();
		}else{
			$query = OC_DB::prepare("SELECT stc_id, MAX(stc_dayts) as stc_dayts FROM *PREFIX*dlstcharts WHERE oc_uid = ?");
			$results = $query->execute(Array(OC_User::getUser()))->fetchAll();
		}
		
		$return = Array();
		foreach($results as $result){
			$query = OC_DB::prepare("SELECT oc_uid, stc_used, stc_total FROM *PREFIX*dlstcharts WHERE stc_id = ?");
			$return[] = $query->execute(Array($result['stc_id']))->fetchAll();
		}
		
		return $return;
	}
	
	/**
	 * Get data to build the line chart about last 7 days used space evolution
	 */
	public static function getLinesLastSevenDaysUsedSpace(){
		$return = Array();
		if(OC_Group::inGroup(OC_User::getUser(), 'admin')){
			foreach (OC_User::getUsers() as $user) {
				$return[$user] = self::getDataByUserToLineChart($user);
			}
		}else{
			$return[OC_User::getUser()] = self::getDataByUserToLineChart(OC_User::getUser());
		}
		return $return;
	}
	
	/**
	 * Parse an array and return data in the highCharts format
	 * @param $operation operation to do 
	 * @param $elements elements to parse
	 */
	public static function arrayParser($operation, $elements){
		$return = "";
		switch($operation){
			case 'pie':
				$free = $total = 0;
				foreach($elements as $element){
					$element = $element[0];
					
					$total = $element['stc_total'];
					$free += $element['stc_used'];
					
					$return .= "['" . $element['oc_uid'] . "', " . $element['stc_used'] . "],";
				}
				$return .= "['Free space', " . ($total - $free) . "]";
			break;
			case 'line':
				foreach($elements as $user => $data){
					$return_tmp = "{name:'" . $user . "',data:[";
					foreach($data as $number){
						$return_tmp .= round(($number/1024)/1024, 2) . ",";
					}
					$return_tmp = substr($return_tmp, 0, -1) . "]}";
					
					$return .= $return_tmp . ",";
				}
				$return = substr($return, 0, -1);
			break;
		}
		return $return;
	}
	
	/**
	 * Get data by user
	 * @param $user the user
	 */
	private static function getDataByUserToLineChart($user){
		$dates = Array(
			mktime(0,0,0,date('m'),date('d')-6),
			mktime(0,0,0,date('m'),date('d')-5),
			mktime(0,0,0,date('m'),date('d')-4),
			mktime(0,0,0,date('m'),date('d')-3),
			mktime(0,0,0,date('m'),date('d')-2),
			mktime(0,0,0,date('m'),date('d')-1),
			mktime(0,0,0,date('m'),date('d'))
		);
		
		$return = Array();
		foreach($dates as $kd => $date){
			$query = OC_DB::prepare("SELECT stc_used FROM *PREFIX*dlstcharts WHERE oc_uid = ? AND stc_dayts = ?");
			$result = $query->execute(Array($user, $date))->fetchAll();
			
			if(count($result) > 0){
				$return[] = $result[0]['stc_used'];
			}else{
				if($kd == 0){
					$query = OC_DB::prepare("SELECT stc_used FROM *PREFIX*dlstcharts WHERE oc_uid = ? AND stc_dayts < ? ORDER BY stc_dayts DESC");
					$result = $query->execute(Array($user, $date))->fetchAll();
					
					if(count($result) > 0){
						$return[] = $result[0]['stc_used'];
					}else{
						$return[] = 0;
					}
				}else{
					$return[] = 0;
				}
			}
		}
		
		$last = 0;
		foreach ($return as $key => $value) {
			if($value == 0){
				$return[$key] = $last;
			}
			$last = $return[$key];
		}
		return $return;
	}
	
}