INTRODUCTION
============

This App provide CAS authentication support, using the phpCAS library of Jasig.


INSTALLATION
============

PREVIOUS DEPENDENCE
-------------------

This App require the phpCAS library of Jasig. https://wiki.jasig.org/display/casc/phpcas

Install at least the version 1.3.2 https://wiki.jasig.org/display/CASC/phpCAS+installation+guide


STEPS
-----

1. Copy the 'user_cas' folder inside the ownCloud's apps folder and give to apache server privileges on whole the folder.
2. Access to ownCloud web with an user with admin privileges.
3. Access to the Applications panel and enable the CAS app.
4. Access to the Administration panel and configure the CAS app.

CONFIGURATION
=============

The App is configured by using the Administration panel. Make sure to fill out the fields provided. 

CAS Server
----------

**CAS Server Version**: Default is version 2.0, if you have no special configuration leave it that way.

**CAS Server Hostname**: the host name of the webserver hosting your CAS

**CAS Server Port**: the port on which the CAS is listening. Usually it should be something like 443.

**CAS Server Path**: the directory to your CAS. In common setups this path is /cas 

**Certification file**: If you don't want to validate the certificate (i.e. self-signed certificates) then leave this empty. Otherwise enter the path to the certificate.

Basic
-----

**Autocreate user**: with this option, users authenticated by CAS that are not stored in the database yet, will be created when they log in the first time. Default: on.

**Update user**: this option uses the data provided by CAS to update user attributes each time they log in.

Mapping
-------

If CAS provides extra attributes, user_cas can retrieve the values of them. Since their name differs in various setups it is necessary to map owncloud-attribute-names to CAS-attribute-names.

**Email**: Name of email attribute in CAS

**Display Name**: Name of display name attribute in CAS (this might be the "real name" of a user)

**Group**: Name of group attribute in CAS 

EXTRA INFO
==========

* If you enable the "Autocreate user after CAS login" option, then if an user does not exist, will be created. If this option is disabled and the user does not existed then the user will be not allowed to log in ownCloud.

* If you enable the "Update user data" option, when an existed user enter, then his email and groups will be updated.

  By default the CAS App will unlink all the groups from a user and will provide the group defined at the groupMapping attribute. If the groupMapping is not defined the value of the defaultGroup field will be used instead. If both are undefined, then the user will be set with no groups.
  But if you configure the "protected groups" field, those groups will not be unlinked from the user.
