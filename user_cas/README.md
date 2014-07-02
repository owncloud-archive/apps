INTRODUCTION
============

This App provide CAS authentication support, using the phpCAS library of Jasig.


INSTALLATION
============

PREVIOUS DEPENDENCE
-------------------

This app requires the phpCAS library of Jasig. https://wiki.jasig.org/display/casc/phpcas

Install (at least) version 1.3.2 https://wiki.jasig.org/display/CASC/phpCAS+installation+guide


STEPS
-----

1. Copy the 'user_cas' folder into the ownCloud's apps folder and make sure to set correct permissions for Apache.
2. Access the ownCloud web interface with an user with admin privileges.
3. Access the applications panel and enable the CAS app.
4. Access the administration panel and configure the CAS app.

CONFIGURATION
=============

The app is configured by using the administration panel. Make sure to fill in all the fields provided. 

CAS Server
----------

**CAS Server Version**: Default is version 2.0, if you have no special configuration leave it as is.

**CAS Server Hostname**: The host name of the webserver hosting your CAS, lookup /etc/hosts or your DNS configuration

**CAS Server Port**: The port CAS is listening to. Default for HTTPS is 443.

**CAS Server Path**: The directory of your CAS. In common setups this path is /cas 

**Certification file**: If you don't want to validate the certificate (i.e. self-signed certificates) then leave this blank. Otherwise enter the path to the certificate.

Basic
-----

**Autocreate user**: This option enables automatic creation of users authenticated against CAS. This means, users which do not exist in the database yet authenticat against CAS and the app will create and store them in the database on their first login. Default: on.

**Update user**: This option uses the data provided by CAS to update user attributes each time they log in.

Mapping
-------

If CAS provides extra attributes, user_cas can retrieve the values of them. Since their name differs in various setups it is necessary to map owncloud-attribute-names to CAS-attribute-names.

**Email**: Name of email attribute in CAS

**Display Name**: Name of display name attribute in CAS (this might be the "real name" of a user)

**Group**: Name of group attribute in CAS 

EXTRA INFO
==========

* If you enable the "Autocreate user after CAS login" option, a user will be created if he does not exist. If this option is disabled and the user does not exist, then the user will be not allowed to log in ownCloud.

* If you enable the "Update user data" option, the app updates the user's email and group membership.

  By default the CAS App will unlink all the groups from a user and will provide the group defined at the groupMapping attribute. If the groupMapping is not defined, the value of the defaultGroup field will be used instead. If both are undefined, then the user will be set with no groups.
If you set the "protected groups" field, those groups will not be unlinked from the user.
