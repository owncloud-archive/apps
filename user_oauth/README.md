# Introduction
This version implements the server side OAuth "Bearer" token verification
against an external authorization server. It aims at supporting both 
the [php-oauth](https://github.com/fkooman/php-oauth) service and the 
[Google](https://developers.google.com/accounts/docs/OAuth2Login#validatingtoken) service.

# Requirements
* PHP cURL extension
* Apache (because we use `apache_request_headers()` at the moment)

# Installation
Install this code in the directory `user_oauth` in the `apps` directory of
your Owncloud installation.

This module needs an external dependency to verify the OAuth tokens at the
OAuth authorization server. A script can be used to install this dependency:

    $ cd /path/to/owncloud/apps/user_oauth
    $ cd 3rdparty
    $ sh fetch_3rdparty_libs.sh

You need Git installed on your server to fetch the 3rd party dependency.

You can enable the `user_oauth` app after login with the `admin` account. Go
to `Settings`, then `Apps` and finally select the `OAuth` module from the list
of modules, select it and press the `Enable` button.

# Configuration
There currently is only one configuration parameter: the Token Info Endpoint.
For quick tests, one can use the playground environment, installed using
[this](https://github.com/fkooman/oauth-install-all) script located at 
https://frko.surfnetlabs.nl/workshop/. 

For the "workshop" installation the Token Info Endpoint would be 

    https://frko.surfnetlabs.nl/workshop/php-oauth/tokeninfo.php

For Google the Token Info Endpoint is:

    https://www.googleapis.com/oauth2/v1/tokeninfo

You can set this endpoint by going to `Settings`, then `Admin` and then under
the section head `OAuth` configure the URL.

# Applications
An application needs to use the OAuth service to retrieve an access token to
use this with the OAuth enabled WebDAV endpoint. The endpoint, assuming you
run the service on https://www.example.org/owncloud, note `odav` instead of 
`webdav`:

    https://www.example.org/owncloud/remote.php/odav/<FILE.EXT>

So, in order for an application to work it needs to obtain an access token
from the OAuth authorization server that you configured as a Token Info 
Endpoint in the OAuth app configuration in Owncloud. If you used the 
playground mentioned above that would mean using the following URLs:

	https://frko.surfnetlabs.nl/workshop/php-oauth/authorize.php
	https://frko.surfnetlabs.nl/workshop/php-oauth/token.php

For Google this will probably be some Google URL. You also need to register the
app at Google. In the playground environment you can also register an OAuth 
client yourself.

Template applications for both 
[Android](https://github.com/OpenConextApps/android-oauth-app) and 
[iOS](https://github.com/OpenConextApps/ios-oauth-app) are available that 
implement OAuth 2.0 and can be used to modify the Owncloud Mobile Apps.

So far, the Owncloud Mobile Apps have not been updated to support OAuth 2.0.

# Compatibilty
The app was tested with version 5 of Owncloud.
