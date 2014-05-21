<?php
/* Copyright (c) 2014, Joas Schilling nickvergessen@owncloud.com
 * This file is licensed under the Affero General Public License version 3
 * or later. See the COPYING-README file. */

/** @var $l OC_L10N */
/** @var $_ array */
?>

<div class="box">
	<div class="messagecontainer">
		<?php if ($_['isGrouped']): ?>
			<?php $count = 0; ?>
			<div class="activity-icon <?php p($_['typeIcon']) ?>"></div>
			<ul class="activitysubject grouped">
				<?php foreach($_['event']['events'] as $subEvent):?>
					<li class="activitysubject" title="<?php p($subEvent['subject_long']) ?>">
						<?php if ($subEvent['link']): ?><a href="<?php p($subEvent['link']) ?>"><?php endif ?>
						<?php print_unescaped(
							\OCA\Activity\Data::translation($subEvent['app'], $subEvent['subject'], $subEvent['subjectparams'], true, true)
						) ?>
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
		<?php else: ?>
			<?php if ($_['event']['link']): ?><a href="<?php p($_['event']['link']) ?>"><?php endif ?>
			<div class="activity-icon <?php p($_['typeIcon']) ?>"></div>
			<div class="activitysubject" title="<?php p($_['event']['subject_long']) ?>">
				<?php print_unescaped(
					\OCA\Activity\Data::translation($_['event']['app'], $_['event']['subject'], $_['event']['subjectparams'], true, true)
				) ?>
			</div>
			<span class="activitytime tooltip" title="<?php p($_['formattedDate']) ?>">
				<?php p($_['formattedTimestamp']) ?>
			</span>
			<?php if ($_['event']['message_long']): ?>
				<div class="activitymessage" title="<?php p($_['event']['message_long']) ?>">
					<?php p($_['event']['message_short']) ?>
				</div>
			<?php endif ?>
		<?php endif ?>

		<?php if (!empty($_['previewImageLink'])): ?>
			<img class="preview<?php if (!empty($_['previewLinkIsDir'])): ?> preview-dir-icon<?php endif ?>" src="<?php p($_['previewImageLink']) ?>" alt="<?php p($_['event']['message_long']) ?>"/>
		<?php endif ?>

		<?php if (!$_['isGrouped'] && $_['event']['link']): ?></a><?php endif; ?>
	</div>
</div>
