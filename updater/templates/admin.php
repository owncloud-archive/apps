<?php

/**
 * ownCloud - Updater plugin
 *
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

?>

<fieldset class="personalblock">
	<legend><strong>Updater</strong></legend>
	<a href="<?php p(\OCP\Util::linkTo('updater', 'update.php')) ?>"><?php p($l->t('Update Center')) ?></a>
</fieldset>
