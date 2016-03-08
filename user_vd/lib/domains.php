<?php

class OC_USER_VD_DOMAIN{
	public static function getDomains(){
		$query=OC_DB::prepare('SELECT domain,GROUP_CONCAT(fqdn) as fqdn FROM *PREFIX*users_vd_domains GROUP BY domain');
		$result=$query->execute();
		$row=$result->fetchAll();
		if($row){
			return $row;
		}else{
			return false;
		}
	}

	public static function saveDomains($dom){
		if(is_array($dom)){
			$query=OC_DB::prepare('DELETE FROM *PREFIX*users_vd_domains');
			$query->execute();
		}else{
			return false;
		}
		$query=OC_DB::prepare('INSERT INTO *PREFIX*users_vd_domains (domain,fqdn) VALUES (?,?)');
		foreach($dom as $domain => $fqdn){
			$fqdn=explode(',',$fqdn);
			foreach($fqdn as $f){
				$result=$query->execute(array($domain,$f));
			}
		}
	}
}
?>
