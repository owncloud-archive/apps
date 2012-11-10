<?php
class OC_Map {

	public static function findAll($limit = 100) {
		$sql = "Select * from `*PREFIX*map_items` WHERE uid_owner = ?";
		$query = OCP\DB::prepare($sql, $limit);
		$params = array(OCP\USER::getUser());
		return self::hydrate($query->execute($params)->fetchAll());
	}

	public static function hydrate($elems) {
		$e = array();
		foreach($elems as $k=>$v) {
			$i = new OC_MapItem();
			$i->fromArray($v);
			$e[$k] = $i;
		}
		return $e;
	}
}