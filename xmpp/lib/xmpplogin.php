<?php
class OC_xmpp_login{
	public $rid=0;
	public $bosh_url;
	public $sid;
	public $username;
	public $domain;
	public $password;
	public $password64;
	public $resource;
	public $jid;
	public $debug=false;
	private $xmlns='xmlns=\'http://jabber.org/protocol/httpbind\'';
	public function __construct($username,$domain,$password,$bosh_url){
		$this->username=$username;
		$this->domain=$domain;
		$this->password=$password;
		$this->bosh_url=$bosh_url;
		$this->resource='ownCloud'.time();

		$this->jid=$this->username.'@'.$this->domain.'/'.$this->resource;
		$this->password64=base64_encode($this->jid.chr(0).$this->username.chr(0).$this->password);
	}

	public function doLogin($jidas=''){
		if($this->get_sid()){$this->login();}
		if($jidas!=''){
			$jide=explode('@',$jidas);
			$passwd=$this->getUserPasswd($jidas);
			$newx=new OC_xmpp_login($jide[0],$jide[1],$passwd,$this->bosh_url);
			$newx->doLogin();
			return $newx;
		}
	}

	public function logout(){
		$xml=$this->newBody();
		$xml->addAttribute('type','terminate');
		$this->send_xml($xml->asXML());
	}

	public function nrid(){
		if($this->rid==0){ $this->rid=rand() * 10000;}
		else{ $this->rid=$this->rid+1;}
		return $this->rid;
	}

	public function newBody(){
		$body=new SimpleXMLElement('<body/>');
		$body->addAttribute('xmlns','http://jabber.org/protocol/httpbind');
		$body->addAttribute('rid',$this->nrid());
		if($this->sid!=''){
			$body->addAttribute('sid',$this->sid);
		}
		return $body;
	}

	public function iq($type,$id=null,$to=null,$from=null){
		$body=$this->newBody();
		$body->addChild('iq','','jabber:client');
		$body->iq->addAttribute('type',$type);
		if($id!=null){
			$body->iq->addAttribute('id',$id);
		}
		if($to!=null){
			$body->iq->addAttribute('to',$to);
		}
		if($from!=null){
			$body->iq->addAttribute('from',$from);
		}
		return $body;
	}

	public function msg($to){
		$body=$this->newBody();
		$body->addChild('message');
		return $body;
	}

	public function send_xml($xml){
		$ch=curl_init($this->bosh_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		$header = array('Content-Type: text/xml; charset=utf-8');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		if($this->debug){ echo 'SEND: ';var_dump($xml); }
		$response=curl_exec($ch);
		$ret=simplexml_load_string($response);
		if($this->debug){ echo 'RECV: ';var_dump($ret); }
		curl_close($ch);
		return $ret;
	}

	public function get_sid(){
		$xml='<body rid="'.$this->nrid().'" '.$this->xmlns.' to="'.$this->domain.'" xml:lang="en" wait="60" hold="1" window="5" content="text/xml; charset=utf-8" ver="1.6" xmpp:version="1.0" xmlns:xmpp="urn:xmpp:xbosh" from="'.$this->jid.'"/>';
		$res=$this->send_xml($xml);
		$this->sid=$res->attributes()->sid;
		if($this->sid!=''){return true;}
		else{return false;}
	}
	public function login(){
		// envia el password
		$auth=$this->newBody();
		$auth->addChild('auth',$this->password64,'urn:ietf:params:xml:ns:xmpp-sasl');
		$auth->auth->addAttribute('mechanism','PLAIN');
		$res=$this->send_xml($auth->asXML());

		$restart=$this->newBody();
		$restart->addAttribute('to',$this->domain);
		$restart->addAttribute('xml:lang','en','xml');
		$restart->addAttribute('xmpp:restart','true','xmpp');
		$restart->addAttribute('xmlns:xmpp','urn:xmpp:xbosh');
		$res=$this->send_xml($restart->asXML());

		$bind2=$this->iq('set','_bind_auth_2');
		$bind2->iq->addChild('bind','','urn:ietf:params:xml:ns:xmpp-bind');
		$bind2->iq->bind->addChild('resource',$this->resource);
		$res=$this->send_xml($bind2->asXML());

		$sess2=$this->iq('set','_session_auth_2');
		$sess2->iq->addChild('session','','urn:ietf:params:xml:ns:xmpp-session');
		$res=$this->send_xml($sess2->asXML());
	}

	public function getRoster(){
		$xml=$this->iq('get');
		$xml->iq->addChild('query','','jabber:iq:roster');
		$ret=$this->send_xml($xml->asXML());
		return $ret;
	}
	
	public function addRoster($jid,$name=null){
		// Afegir al roster
		$xml=$this->iq('set');
		$xml->iq->addChild('query','','jabber:iq:roster');
		$xml->iq->query->addChild('item');
		$xml->iq->query->item->addAttribute('jid',$jid);
		if($name!=null){ $xml->iq->query->item->addAttribute('name',$name); }
		$ret=$this->send_xml($xml->asXML());

//		$id=$ret->iq->attributes()->id;
//		$xml=$this->iq('result',$id,$this->domain,$this->jid);
//		$this->send_xml($xml->asXML());

		// Peticio de subscribe
		$xml=$this->newBody();
		$xml->addChild('presence');
		$xml->presence->addAttribute('from',$this->username.'@'.$this->domain);
		$xml->presence->addAttribute('to',$jid);
		$xml->presence->addAttribute('type','subscribe');
		$this->send_xml($xml->asXML());

	}

	public function deleteRoster($jid){
		$xml=$this->newBody();
		$xml->addChild('presence');
		$xml->presence->addAttribute('from',$this->username.'@'.$this->domain);
		$xml->presence->addAttribute('to',$jid);
		$xml->presence->addAttribute('type','unsubscribe');
		$this->send_xml($xml->asXML());

		$xml=$this->newBody();
		$xml->addChild('presence');
		$xml->presence->addAttribute('from',$this->username.'@'.$this->domain);
		$xml->presence->addAttribute('to',$jid);
		$xml->presence->addAttribute('type','unsubscribed');
		$this->send_xml($xml->asXML());

		$xml=$this->iq('set',null,null,$this->username.'@'.$this->domain);
		$xml->iq->addChild('query','','jabber:iq:roster');
		$xml->iq->query->addAttribute('jid',$jid);
		$xml->iq->query->addAttribute('subscription','remove');
		$this->send_xml($xml->asXML());
	}

	public function addUser($jid,$passwd,$passwdverify){
		$xml=$this->iq('set','add-user-1',$this->domain,$this->jid);
		$xml->iq->addChild('command','','http://jabber.org/protocol/commands');
		$xml->iq->command->addAttribute('action','execute');
		$xml->iq->command->addAttribute('node','http://jabber.org/protocol/admin#add-user');
		$res=$this->send_xml($xml->asXML());

		$xml=$this->iq('set','add-user-2',$this->domain,$this->jid);
		$xml->iq->addChild('command','','http://jabber.org/protocol/commands');
		$xml->iq->command->addAttribute('node','http://jabber.org/protocol/admin#add-user');
		$xml->iq->command->addAttribute('sessionid',$res->iq->command->attributes()->sessionid);
		$xml->iq->command->addChild('x','','jabber:x:data');
		$xml->iq->command->x->addAttribute('type','submit');
		$field=$xml->iq->command->x->addChild('field');
		$field->addAttribute('type','hidden');
		$field->addAttribute('var','FROM_TYPE');
		$field->addChild('value','http://jabber.org/protocol/admin');
		$field=$xml->iq->command->x->addChild('field');
		$field->addAttribute('var','accountjid');
		$field->addChild('value',$jid);
		$field=$xml->iq->command->x->addChild('field');
		$field->addAttribute('var','password');
		$field->addChild('value',$passwd);
		$field=$xml->iq->command->x->addChild('field');
		$field->addAttribute('var','password-verify');
		$field->addChild('value',$passwdverify);
		$this->send_xml($xml->asXML());
	}

	public function delUser($jid){
		$xml=$this->iq('set','delete-user-1',$this->domain,$this->jid);
		$xml->iq->addChild('command','','http://jabber.org/protocol/commands');
		$xml->iq->command->addAttribute('action','execute');
		$xml->iq->command->addAttribute('node','http://jabber.org/protocol/admin#delete-user');

		$res=$this->send_xml($xml->asXML());
		$sesid=$res->iq->command->attributes()->sessionid;
		$xml=$this->iq('set','delete-user-2',$this->domain,$this->jid);
		$xml->iq->addChild('command','','http://jabber.org/protocol/commands');
		$xml->iq->command->addAttribute('node','http://jabber.org/protocol/admin#delete-user');
		$xml->iq->command->addAttribute('sessionid',$sesid);
		$xml->iq->command->addChild('x','','jabber:x:data');
		$xml->iq->command->x->addAttribute('type','submit');
		$field=$xml->iq->command->x->addChild('field');
		$field->addAttribute('type','hidden');
		$field->addAttribute('var','FROM_TYPE');
		$field->addChild('value','http://jabber.org/protocol/admin');
		
		$field=$xml->iq->command->x->addChild('field');
		$field->addAttribute('var','accountjids');
		$field->addChild('value',$jid);
		$this->send_xml($xml->asXML());
	}

	public function getUserPasswd($jid){
		$xml=$this->iq('set','get-user-password-1',$this->domain,$this->jid);
		$xml->iq->addChild('command','','http://jabber.org/protocol/commands');
		$xml->iq->command->addAttribute('action','execute');
		$xml->iq->command->addAttribute('node','http://jabber.org/protocol/admin#get-user-password');
		$res=$this->send_xml($xml->asXML());
		
		$sessid=$res->iq->command->attributes()->sessionid;
		$xml=$this->iq('set','get-user-password-2',$this->domain,$this->jid);
		$xml->iq->addChild('command','','http://jabber.org/protocol/commands');
		$xml->iq->command->addAttribute('node','http://jabber.org/protocol/admin#get-user-password');
		$xml->iq->command->addAttribute('sessionid',$sessid);
		$xml->iq->command->addChild('x','','jabber:x:data');
		$xml->iq->command->x->addAttribute('type','submit');
		$field=$xml->iq->command->x->addChild('field');
		$field->addAttribute('type','hidden');
		$field->addAttribute('var','FORM_TYPE');
		$field->addChild('value','http://jabber.org/protocol/admin');

		$field=$xml->iq->command->x->addChild('field');
		$field->addAttribute('var','accountjid');
		$field->addChild('value',$jid);
		$res=$this->send_xml($xml->asXML());
		foreach($res->iq->command->x->children() as $field){
			if($field->attributes()->var=='password'){
				$password=$field->value;
			}
		}
		if(isset($password)&&$password!=''){
			return $password;
		}else{
			return false;
		}
	}
}
?>
