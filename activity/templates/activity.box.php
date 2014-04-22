<?php
/* Copyright (c) 2014, Joas Schilling nickvergessen@gmx.de
 * This file is licensed under the Affero General Public License version 3
 * or later. See the COPYING-README file. */

/** @var $l OC_L10N */
/** @var $_ array */
?>

<div class="box">
	<div class="header">
		<span class="avatar" data-user="<?php p($_['user']) ?>"></span>
		<span>
			<span class="user"><?php p($_['displayName']) ?></span>
			<span class="activitytime tooltip" title="<?php p($_['formattedDate']) ?>">
				<?php p($_['formattedTimestamp']) ?>
			</span>
			<!--<span class="appname"><?php p($_['event']['app']) ?></span>-->
		</span>
	</div>
	<div class="messagecontainer">
		<?php if ($_['isGrouped']): ?>
			<?php $count = 0; ?>
			<ul class="activitysubject grouped">
				<?php foreach($_['event']['events'] as $subEvent):?>
					<li>
						<?php if ($subEvent['link']): ?><a href="<?php p($subEvent['link']) ?>"><?php endif ?>
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
			<?php if ($_['event']['link']): ?><a href="<?php p($_['event']['link']) ?>"><?php endif ?>
			<div class="activitysubject"><?php p($_['event']['subject']) ?></div>
			<div class="activitymessage"><?php p($_['event']['message']) ?></div>
		<?php endif ?>

		<?php if (!empty($_['previewImageLink'])): ?>
			<img class="preview" src="<?php p($_['previewImageLink']) ?>" alt="<?php p($_['event']['message']) ?>"/>
		<?php endif ?>

		<?php if (!$_['isGrouped'] && $_['event']['link']): ?></a><?php endif; ?>
	</div>
</div>
