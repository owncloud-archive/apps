<?php

class OC_User_xmpp_Hooks {
	static public function createXmppSession($params){
		$xmpplogin=new OC_xmpp_login($params['uid'],OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain',''),$params['password'],OCP\Config::getAppValue('xmpp', 'xmppBOSHURL',''));
		$xmpplogin->doLogin();
                
		$stmt = OCP\DB::prepare('SELECT ocUser FROM *PREFIX*xmpp WHERE ocUser = "'.$params['uid'].'"');
                $result = $stmt->execute();
                if($result->numRows()!=0){
			OC_User_xmpp_Hooks::deleteXmppSession();
                }
                $stmt = OCP\DB::prepare('INSERT INTO *PREFIX*xmpp (ocUser,jid,rid,sid) VALUES ("'.$params['uid'].'","'.$xmpplogin->jid.'","'.$xmpplogin->rid.'","'.$xmpplogin->sid.'")');
                $result=$stmt->execute();

	}

	static public function deleteXmppSession(){
		$stmt = OCP\DB::prepare('DELETE FROM *PREFIX*xmpp WHERE ocUser = "'.OCP\User::getUser().'"');
		$stmt->execute();
	}

	static public function createXmppUser($info){
		system('sudo /usr/sbin/ejabberdctl register '.$info['uid'].' '.OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain','').' '.$info['password']);
	}

	static public function updateXmppUserPassword($info){
		system('sudo /usr/sbin/ejabberdctl change_password '.$info['uid'].' '.OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain','').' '.$info['password']);
	}

	static public function post_updateVCard($id){
		if(OC_Preferences::getValue(OC_USER::getUser(),'xmpp','autoroster')!=true){ return false; }
		$email='';
		$vcardq=OC_Contacts_Vcard::find($id);
		if($vcardq==false)return false;
		$name=$vcardq['fullname'];
		$data=$vcardq['carddata'];
		$vcard = OC_VObject::parse($data);
		foreach($vcard->children as &$property) {
			if($property->name == 'EMAIL'){
				$email = $property->value;
			}
		}
		if($email!=''){
			$xmpplogin=new OC_xmpp_login(OCP\Config::getAppValue('xmpp', 'xmppAdminUser',''),OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain',''),OCP\Config::getAppValue('xmpp', 'xmppAdminPasswd',''),OCP\Config::getAppValue('xmpp', 'xmppBOSHURL',''));	
			$xuser=$xmpplogin->doLogin(OC_USER::getUser().'@'.OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain',''));

			$xuser->addRoster($email,$name);
			$xmpplogin->logout();
			$xuser->logout();

		}
	}
}

?>
