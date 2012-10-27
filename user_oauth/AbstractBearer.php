<?php

require_once 'lib/RemoteResourceServer.php';

abstract class Sabre_DAV_Auth_Backend_AbstractBearer implements Sabre_DAV_Auth_IBackend {

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
        return true;
    }

}

