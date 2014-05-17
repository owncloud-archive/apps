<?php

/**
 * ownCloud - Activity Application
 *
 * @author Frank Karlitschek
 * @copyright 2013 Frank Karlitschek frank@owncloud.org
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/** @var $l OC_L10N */
/** @var $theme OC_Defaults */
/** @var $_ array */
?>
<a href="<?php p($_['rsslink']) ?>" class="button rssbutton"><?php p($l->t('RSS feed')) ?></a>

<?php if (empty($_['activity'])): ?>
	<div class="noactivities">
		<div class="head"><?php p($l->t('No activities yet.')) ?></div>
		<div class="body"><?php p($l->t('You will see a list of events here when you start to use your %s.', $theme->getTitle())) ?></div>
	</div>
<?php else:
	// Show the activities. The container is needed for the endless scrolling
	?>
	<div id="container">
		<?php
			$tmpl = new \OCP\Template('activity', 'activities.part', '');
			$tmpl->assign('activity', $_['activity']);
			$tmpl->printPage();
		?>
	</div>

	<?php
	// Dummy navigation. Needed for endless scrolling
	if (isset($_['nextpage'])): ?>
		<nav id="page-nav">
			<a href="<?php p($_['nextpage']) ?>"></a>
		</nav>
	<?php endif; ?>
<?php endif; ?>
