<?php

/**
 * ownCloud
 *
 * @author Michal Jaskurzynski
 * @copyright 2012 Michal Jaskurzynski mjaskurzynski@gmail.com
 *
 */

OC::$CLASSPATH['OCA_mozilla_sync\InputData'] = 'apps/mozilla_sync/lib/inputdata.php';
OC::$CLASSPATH['OCA_mozilla_sync\OutputData'] = 'apps/mozilla_sync/lib/outputdata.php';
OC::$CLASSPATH['OCA_mozilla_sync\User'] = 'apps/mozilla_sync/lib/user.php';
OC::$CLASSPATH['OCA_mozilla_sync\UrlParser'] = 'apps/mozilla_sync/lib/urlparser.php';
OC::$CLASSPATH['OCA_mozilla_sync\Utils'] = 'apps/mozilla_sync/lib/utils.php';
OC::$CLASSPATH['OCA_mozilla_sync\Storage'] = 'apps/mozilla_sync/lib/storage.php';

OC::$CLASSPATH['OCA_mozilla_sync\Service'] = 'apps/mozilla_sync/lib/service.php';
OC::$CLASSPATH['OCA_mozilla_sync\StorageService'] = 'apps/mozilla_sync/lib/storageservice.php';
OC::$CLASSPATH['OCA_mozilla_sync\UserService'] = 'apps/mozilla_sync/lib/userservice.php';

OCP\App::registerPersonal('mozilla_sync', 'settings');
