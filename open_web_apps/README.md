# owncloud-owa

This owncloud app lets you add open web apps to your owncloud instance.

# install

These instructions assume standard install with apache2 on a debian-like system.

* First, install owncloud 4.5 (*not* owncloud 5.0 or higher) as normal. 
* Copy the open\_web\_apps folder into /var/www/apps/
* choose a storage origin. This can be an additional subdomain, or an additional port on which Apache should listen, like https://example.com:44344
* add this origin as an additional vhost to apache config and point it to /var/www/apps/open\_web\_apps/storage\_root/
* make sure the AllowOverride directive for this vhost allows /var/www/apps/open\_web\_apps/storage\_root/.htaccess to set its RewriteRule
* sudo apt-get install php5-curl libxattr1-dev pear
* sudo pecl install xattr
* copy the 'webfinger' file to /var/www/.well-known/webfinger, changing:
  * 'https://example.com' to the domain you run owncloud on
  * 'https://example.com:8012' to your storage origin
* note that this will only enable remoteStorage for the user called 'admin'! if you want it to work for other users on the same installation, you will need to create a dynamic webfinger file, that replaces the word 'admin' in that static file, to whatever was requested in the "?resource=acct:user@host" query. See http://tools.ietf.org/html/draft-ietf-appsawg-webfinger-14 for more info about serving webfinger records.
* assuming there are no files other than 'webfinger' in that directory, then in /etc/apache2/sites-enabled/default-ssl, add:

````
    <Directory /var/www/.well-known/>
       Header set Access-Control-Allow-Origin "*"
       Header set Content-Type "application/json"
    </Directory>
````
* sudo service apache2 restart
* log in to owncloud as an admin and activate the app
* configure the storage origin in the owncloud admin settings

# Known Bugs

* there is no way to remove apps (other than going into the database)
* install is still very cumbersome
* not ported to owncloud 5.0 yet
* it doesn't warn if you forget to configure the storage origin
* it sends out cookies on the storage origin (probably harmless though)
* /index.php?app=open\_web\_apps&getfile=main.php sometimes incorrectly redirects to /open\_web\_apps/index.php
* expanding permissions to more scopes causes existing ones to be repeated in the database
* it sometimes serves Content-Type 1 instead of what's in xattr.

# license

You hereby have my permission to use this app unlicensed, or under the MIT license, or under the AGPL license. I borrowed the "Open web apps"
rocket glyph from Mozilla Marketplace, since I know of no other good generic icon for open web apps, currently.
