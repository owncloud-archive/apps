<?php
/**
 * ownCloud - Activity App
 *
 * @author Joas Schilling
 * @copyright 2014 Joas Schilling nickvergessen@gmx.de
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
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/** @var $l OC_L10N */
/** @var $_ */
?>

<div class="box">
	<div class="header">
		<span class="avatar" data-user="<?php echo $_['user'] ?>"></span>
		<span>
			<span class="user"><?php p($_['displayName']) ?></span>
			<span class="activitytime tooltip" title="<?php p($_['formattedDate']) ?>">
				<?php p($_['formattedTimestamp']) ?>
			</span>
			<span class="appname"><?php p($_['event']['app']) ?></span>
		</span>
	</div>
	<div class="messagecontainer">
		<?php if ($_['isGrouped']): ?>
			<?php $count = 0; ?>
			<ul class="activitysubject grouped">
				<?php foreach($_['event']['events'] as $subEvent):?>
					<li>
						<?php if ($subEvent['link']): ?><a href="<?php echo $subEvent['link'] ?>"><?php endif ?>
						<?php p($subEvent['subject']) ?>
						<?php if ($subEvent['link']): ?></a><?php endif ?>
					</li>
					<?php $count++ ?>
					<?php if ($count > 5): ?>
						<li class="more">
							<?php p($l->n('%n more...', '%n more...', count($_['event']['events']) - $count)) ?>
						</li>
						<?php break ?>
					<?php endif ?>
				<?php endforeach ?>
			</ul>
		<?php endif ?>
		<?php if (!$_['isGrouped']): ?>
			<?php if ($_['event']['link']): ?><a href="<?php echo $_['event']['link'] ?>"><?php endif ?>
			<div class="activitysubject"><?php p($_['event']['subject']) ?></div>
			<div class="activitymessage"><?php p($_['event']['message']) ?></div>
		<?php endif ?>

		<?php if ($_['previewImageLink']): ?>
			<img class="preview" src="<?php echo $_['previewImageLink'] ?>" alt="<?php p($_['event']['message']) ?>"/>
		<?php endif ?>

		<?php if (!$_['isGrouped'] && $_['event']['link']): ?></a><?php endif; ?>
	</div>
</div>
