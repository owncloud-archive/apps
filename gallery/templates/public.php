<header>
	<div id="header">
		<a href="<?php print_unescaped(link_to('', 'index.php')); ?>" title="" id="owncloud">
			<img class="svg" src="<?php print_unescaped(image_path('', 'logo-wide.svg')); ?>" alt="<?php p($theme->getName()); ?>" /></a>
		<div id="logo-claim" style="display:none;"><?php p($theme->getLogoClaim()); ?></div>
		<div class="header-right">
			<span id="details"><?php p($l->t('%s shared the album "%s" with you', array($_['displayName'], $_['albumName']))) ?></span>
		</div>
	</div>
</header>
<div id="content">
	<div id="controls">
		<div id='breadcrumbs'></div>
		<div id='openAsFileListButton' class="button" style="float: right;"><?php p($l->t('Open as file list')) ?></div>
	</div>

	<div id='gallery' class="hascontrols" data-token="<?php isset($_['token']) ? p($_['token']) : p(false) ?>"></div>
</div>
<footer>
	<p class="info">
		<?php print_unescaped($theme->getLongFooter()); ?>
	</p>
</footer>
