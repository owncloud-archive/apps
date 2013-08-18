# Introduction
This app implements server side OAuth 2.0 "Bearer" token verification against 
an external authorization server. It aims at supporting the 
[php-oauth](https://github.com/fkooman/php-oauth) service. However, any other 
OAuth 2.0 AS supporting `draft-richer-oauth-introspection` should work.

# Requirements
* PHP cURL extension
* Apache (because we use `apache_request_headers()` at the moment)

# Installation
Install this code in the directory `user_oauth` in the `apps` directory of your 
ownCloud installation.

This module needs an external library to verify the OAuth tokens at the OAuth 
authorization server. [Composer](http://www.getcomposer.org) can be used to 
install this dependency, by default is in included in the `3rdparty` directory. 
So you only need this if you want to download the library again or update it.

    $ cd /path/to/owncloud/apps/user_oauth
    $ php composer.phar install

Or to update:

    $ php composer.phar update

You can enable the `user_oauth` app after login with the `admin` account. Go to 
`Settings`, then `Apps` and finally select the `OAuth` module from the list of 
modules, select it and press the `Enable` button.

# Configuration
There currently is only one configuration parameter: the introspection 
endpoint. For quick tests, you can use the playground environment, installed 
using [this](https://github.com/fkooman/oauth-install-all) script located at 
https://frko.surfnetlabs.nl/workshop/.

For the "workshop" installation the introspection endpoint would be:

    https://frko.surfnetlabs.nl/workshop/php-oauth/introspect.php

You can set this endpoint by going to `Settings`, then `Admin` and then under
the section head `OAuth` configure the URL.

# Applications
An application needs to use the OAuth service to retrieve an access token to
use this with the OAuth enabled WebDAV endpoint. The endpoint, assuming you run 
the service on https://www.example.org/owncloud, note `odav` instead of 
`webdav`:

    https://www.example.org/owncloud/remote.php/odav/<FILE.EXT>

So, in order for an application to work it needs to obtain an access token from 
the OAuth authorization server that you configured as an introspection endpoint 
in the OAuth app configuration in ownCloud. If you used the playground 
mentioned above that would mean using the following URLs for authorization and 
token endpoints:

	https://frko.surfnetlabs.nl/workshop/php-oauth/authorize.php
	https://frko.surfnetlabs.nl/workshop/php-oauth/token.php

It seems the Android app of ownCloud should support OAuth at the server, but so 
far we were unable to make it work. We tested version 1.4.1 of the Android app 
from the F-Droid repository.

# Compatibilty
The app was tested with version 5 of ownCloud.
