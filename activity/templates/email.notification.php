<?php
/* Copyright (c) 2014, Joas Schilling nickvergessen@gmx.de
 * This file is licensed under the Affero General Public License version 3
 * or later. See the COPYING-README file. */
/** @var OC_L10N $l */
/** @var array $_ */

print_unescaped($l->t("Hello %s,\n", array($_['username']))); ?>

<?php print_unescaped($l->t("You receive this email because %s the following things happened at %s\n", array($_['timeframe'], $_['owncloud_installation']))); ?>

<?php foreach ($_['activities'] as $activity) {
	print_unescaped($l->t("* %s\n", array($activity)));
}
?>
