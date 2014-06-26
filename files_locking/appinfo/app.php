<?php

OCP\Util::connectHook('OC_Filesystem', 'setup', '\OCA\Files_Locking\LockingWrapper', 'setupWrapper');
