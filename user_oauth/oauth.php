<?php

require_once '3rdparty/RemoteResourceServer.php';

class OC_Connector_Sabre_OAuth implements Sabre_DAV_Auth_IBackend {

    private $currentUser;
    private $tokenInfoEndpoint;

    public function __construct($tokenInfoEndpoint) {
        $this->tokenInfoEndpoint = $tokenInfoEndpoint;
    }

    public function getCurrentUser() {
        return $this->currentUser;
    }

    public function authenticate(Sabre_DAV_Server $server, $realm) {
        $config = array(
            "tokenInfoEndpoint" => $this->tokenInfoEndpoint,
            "throwException" => TRUE,
            "resourceServerRealm" => $realm,
        );

        $authorizationHeader = $server->httpRequest->getHeader('Authorization');

        // Apache could prefix environment variables with REDIRECT_ when urls
        // are passed through mod_rewrite
        if (!$authorizationHeader) {
            $authorizationHeader = $server->httpRequest->getRawServerValue('REDIRECT_HTTP_AUTHORIZATION');
        }

        try { 
            $resourceServer = new RemoteResourceServer($config);

            $resourceServer->verifyAuthorizationHeader($authorizationHeader);
            $attributes = $resourceServer->getAttributes();

            $this->currentUser = $attributes["uid"][0];
            OC_Util::setupFS($this->currentUser);
            return true;

        } catch(RemoteResourceServerException $e) {
            $server->httpResponse->setHeader('WWW-Authenticate', $e->getAuthenticateHeader());

            // FIXME: do we need to set the status here explicitly, or does the 
            // Exception below take care of this?
            $server->httpResponse->sendStatus($e->getResponseCode());
            if("403" === $e->getResponseCode()) { 
                throw new Sabre_DAV_Exception_Forbidden($e->getDescription());
            } else {
                throw new Sabre_DAV_Exception_NotAuthenticated($e->getDescription());
            }
        }
    }

}

