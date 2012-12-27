INTRODUCTION
============

This App provide CAS authentication support, using the phpCAS library of Jasig.


INSTALLATION
============

PREVIOUS DEPENDENCE
-------------------

This App require the phpCAS library of Jasig. To learn how to install it on your system check:

* `phpCAS installation <https://wiki.jasig.org/display/CASC/phpCAS+installation+guide>`_


STEPS
-----

1. Copy the 'user_cas' folder inside the ownCloud's apps folder and give to apache server privileges on whole the folder.
2. Access to ownCloud web with an user with admin privileges.
3. Access to the Appications pannel and enable the CAS app.
4. Access to the Administration pannel and configure the CAS app.


EXTRA INFO
==========

* If you enable the "Autocreate user after cas login" option, then if an user does not exist, will be created. If this option is disabled and the user does not existed then the user will be not allowed to log in ownCloud.

* If you enable the "Update user data" option, when an existed user enter, then his email and groups will be updated.

  By default the CAS App will unlink all the groups from a user and will provide the group defined at the groupMapping attribute. If the groupMapping is not defined the value of the defaultGroup field will be used instead. If both are undefined, then the user will be set with no groups.
  But if you configure the "protected groups" field, those groups will not be unlinked from the user.
