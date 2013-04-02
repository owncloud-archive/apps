# Introduction
This is a library to implement an OAuth 2.0 resource server (RS). The library
can be used by any service that wants to accept OAuth 2.0 bearer tokens.

It is compatible with and was tested with 
[php-oauth](https://github.com/fkooman/php-oauth), and should work with Google.

# License
Licensed under the Apache License, Version 2.0;

   http://www.apache.org/licenses/LICENSE-2.0

# API
Using the library is straightforward:

    <?php
    require_once 'extlib/php-lib-remote-rs/lib/OAuth/RemoteResourceServer.php';

    use \OAuth\RemoteResourceServer as RemoteResourceServer;

    $config = array(
        "tokenInfoEndpoint" => "http://localhost/php-oauth/tokeninfo.php",
        "resourceServerRealm" => "My Demo Service",
        "throwException" => FALSE
    );

    $rs = new RemoteResourceServer($config);
    $rs->verifyRequest();

Onlt the `tokenInfoEndpoint` configuration parameter is required, the others
are optional:

* `tokenInfoEndpoint` - specify the location at which to verify the OAuth token;
* `resourceServerRealm` - specify the "realm" of the RS that is used when 
  returning errors to the client using the `WWW-Authenticate` header;
* `throwException` - throw a `RemoteResourceServerException` instead of handling 
  the failure in the library by sending a response back to the client. This is 
  useful if you want to integrate the library in your own framework, you can
  use the information from the exception to craft your own response.

After the `verifyRequest()` some methods are available to retrieve information
about the resource owner and client.

* `getResourceOwnerId()` (the unique resource owner identifier)
* `getAttributes()` (additional attributes associated with the resource owner)
* `getScope()` (the scope granted to the client accessing this resource)
* `getEntitlement()` (the entitlement the resource owner has when accessing this 
  resource)
