<?php
/**
 * ownCloud - push notifications app
 *
 * @author Frank Karlitschek
 * @copyright 2014 Frank Karlitschek frank@owncloud.org
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */


$this->create('pushnotifications_ajax_changepushid', 'ajax/changepushid.php')
        ->actionInclude('pushnotifications/ajax/changepushid.php');

