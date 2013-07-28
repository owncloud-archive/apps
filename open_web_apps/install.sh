# general
apt-get update && apt-get upgrade

#owncloud
echo 'deb http://download.opensuse.org/repositories/isv:ownCloud:community/Debian_6.0/ /' >> /etc/apt/sources.list.d/owncloud.list 
apt-get update
apt-get install owncloud
/etc/init.d/apache2/restart

cp -r install/open_web_apps /var/www/owncloud/apps/open_web_apps

echo browse to port 80 of this server, set up owncloud, go into admin->apps, activate 'open web apps'

echo go to admin->admin and set the storage origin to 'http://dragon.unhosted.org:8012'

cp -r install/well-known /var/www/.well-known
cp install/default-8012 /etc/apaches2/sites-available/default-8012
a2ensite default-8012
echo edit /var/www/.well-known/webfinger and replace 'dragon.unhosted.org' with your server's hostname.

echo add two lines 'NameVirtualHost *:8012' and 'Listen 8012' to /etc/apache2/ports.conf


echo on line 11 of /etc/apache2/sites-enabled/000-default change 'None' to 'All'
a2enmod headers
apt-get install php5-dev libattr1-dev
pecl install xattr
/etc/init.d/apache2/restart
