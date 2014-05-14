<?php
/* Copyright (c) 2014, Joas Schilling nickvergessen@gmx.de
 * This file is licensed under the Affero General Public License version 3
 * or later. See the COPYING-README file. */

/** @var $l OC_L10N */
/** @var $_ array */
?>

<div class="box">
	<div class="messagecontainer">
		<?php if ($_['isGrouped']): ?>
			<?php $count = 0; ?>
			<?php if (!empty($_['typeIcon'])): ?><div class="activity-icon <?php p($_['typeIcon']) ?>"></div><?php endif ?>
			<ul class="activitysubject grouped">
				<?php foreach($_['event']['events'] as $subEvent):?>
					<li title="<?php p($subEvent['subject']) ?>">
						<?php if ($subEvent['link']): ?><a href="<?php p($subEvent['link']) ?>"><?php endif ?>
						<?php p($subEvent['subject_short']) ?>
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
			<?php if (!empty($_['typeIcon'])): ?><div class="activity-icon <?php p($_['typeIcon']) ?>"></div><?php endif ?>
			<div class="activitysubject" title="<?php p($_['event']['subject']) ?>">
				<?php p($_['event']['subject_short']) ?>
			</div>
			<span class="activitytime tooltip" title="<?php p($_['formattedDate']) ?>">
				<?php p($_['formattedTimestamp']) ?>
			</span>
			<div class="activitymessage"><?php p($_['event']['message']) ?></div>
		<?php endif ?>

		<?php if (!empty($_['previewImageLink'])): ?>
			<img class="preview" src="<?php p($_['previewImageLink']) ?>" alt="<?php p($_['event']['message']) ?>"/>
		<?php endif ?>

		<?php if (!$_['isGrouped'] && $_['event']['link']): ?></a><?php endif; ?>
	</div>
</div>
