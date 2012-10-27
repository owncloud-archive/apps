<?php

require_once '3rdparty/RemoteResourceServer.php';

class OC_Connector_Sabre_Auth_Bearer implements Sabre_DAV_Auth_IBackend {

    protected $currentUser;
    protected $tokenInfoEndpoint;

    public function __construct($tokenInfoEndpoint) {
        $this->tokenInfoEndpoint = $tokenInfoEndpoint;
    }

    public function getCurrentUser() {
        return $this->currentUser;
    }

    public function authenticate(Sabre_DAV_Server $server, $realm) {
        $config = array(
            "tokenInfoEndpoint" => $this->tokenInfoEndpoint
        );

        $rs = new RemoteResourceServer($config);
        $rs->verifyRequest();

        $attributes = $rs->getAttributes();

        $this->currentUser = $attributes["uid"][0];
        // maybe need to set that we are logged in...?
        OC_Util::setupFS($this->currentUser);

        return true;
    }

}

