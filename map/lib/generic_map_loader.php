<?php
	abstract class OC_Generic_Map_Loader {

	public static function findByType($type = '', $path = '', $limit = 100) {
		$sql = "Select * from `*PREFIX*map_items` WHERE uid_owner = ?";
		$params = array(OCP\USER::getUser());

		if($type != '') {
			$sql .= ' and type = ? ';
			$params[] = $type;
		}
		$query = OCP\DB::prepare($sql, $limit);
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