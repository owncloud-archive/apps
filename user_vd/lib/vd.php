<?php

class OC_USER_VD extends OC_User_Backend{
	public $domain;
	public function __construct(){
	}

	public function createUser($uid,$password){
		$uid=$this->checkUid($uid);
		if($this->userExists($uid)){
			return false;
		}else{
			$query=OC_DB::prepare('INSERT INTO *PREFIX*users_vd (uid,password) VALUES (?,?,?,?)');
			$result=$query->execute(array($uid,$password));
			return $result ? true : false;
		}
	}

	public function userExists($uid){
		$uid=$this->checkUid($uid);
		$query=OC_DB::prepare('SELECT * FROM *PREFIX*users_vd WHERE LOWER(uid) = LOWER(?)');
		$result=$query->execute(array($uid));
		return $result->numRows()>0;
	}

	public function checkPassword($uid,$password){
		$uid=$this->checkUid($uid);
		$query = OC_DB::prepare( 'SELECT `uid`, `password` FROM `*PREFIX*users_vd` WHERE LOWER(`uid`) = LOWER(?)' );
		$result = $query->execute( array( $uid));
		$row=$result->fetchRow();
		if($row){
			if($row['password']==$password){
				return $row['uid'];
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function getHome($uid){
		$uid=$this->checkUid($uid);
		if($this->userExists($uid)){
			list($user,$domain)=explode('@',$uid);
			return OC_Config::getValue( "datadirectory", OC::$SERVERROOT."/data" ) . '/' . $domain . '/' . $user;
		}else{
			return false;
		}
	}
	
	public function getUsers($search = '', $limit = null, $offset = null) {
		$query = OC_DB::prepare('SELECT `uid` FROM `*PREFIX*users_vd` WHERE LOWER(`uid`) LIKE LOWER(?)',$limit,$offset);
		$result = $query->execute(array($search.'%'));
		$users = array();
		while ($row = $result->fetchRow()) {
			$users[] = $row['uid'];
		}
		return $users;
	}

	public function implementsActions($actions){
		return true;
	}

	public function checkUid($uid){
		if(strpos($uid,'@')){
			$uide=explode('@',$uid);
			$nuid=$uide[0];
			$fqdn=$uide[1];
		}else{
			$nuid=$uid;
			$fqdn=$_SERVER['SERVER_NAME'];
		}

		$query=OC_DB::prepare('SELECT domain FROM *PREFIX*users_vd_domains WHERE LOWER(domain)=LOWER(?) OR LOWER(fqdn)=LOWER(?)');
		$result=$query->execute(array($fqdn,$fqdn));
		$row=$result->fetchRow();
		if($row){
			return $nuid.'@'.$row['domain'];
		}else{
			return $uid;
		}
	}

	public static function deleteBackends(){
                OC_User::clearBackends();
                OC_User::useBackend('VD');
        }
}
?>
