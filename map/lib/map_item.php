<?php
class OC_MapItem {

  public $id;
  public $type;
  public $name;
  public $lat;
  public $lon;
  public $uid_owner;

  public function __construct() {
		/* Force user */
    $this->uid_owner = OCP\USER::getUser();
		return $this;
  }

	public function save() {
		
	}

	public function fromArray($f_array) {
		foreach($f_array as $k=>$v) {
		//if(isset($this->{$k}))
			$this->{$k} = $v;
		}
		return $this;
	}
	public function toArray() {
		return array('id' => $this->id, 'type' => $this->type,'name' => $this->name,
			'lat' => $this->lat, 'lon' => $this->lon, 'uid_owner' => $this->uid_owner,);
	}


	public static function add(MapItem $map_item)
	{
		$sql = "INSERT INTO `*PREFIX*map_items (type, name, lat, lon, uid_owner)
			VALUES (?, ?, ?, ?, ?)";
		$query = OCP\DB::prepare($sql);
		$query->execute();
		$id = OCP\DB::insertid('*PREFIX*map_item');
		return self::find($id);
	}

	public static function find($id)
	{
		$sql = "select * from `*PREFIX*map_items where id = ?";
		$query = OCP\DB::prepare($sql);
		$result = $query->execute(array($id))->fetchOne();
		if(! $result) return null;
		$el = new OC_MapItem();
		$el->fromArray($result);
		return $el;
	}
}