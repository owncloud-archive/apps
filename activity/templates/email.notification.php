<?php
/* Copyright (c) 2014, Joas Schilling nickvergessen@owncloud.com
 * This file is licensed under the Affero General Public License version 3
 * or later. See the COPYING-README file. */

/** @var OC_L10N $l */
/** @var array $_ */

p($l->t("Hello %s,\n", array($_['username']))); ?>

<?php p($l->t("You receive this email because %s the following things happened at %s\n", array($_['timeframe'], $_['owncloud_installation']))); ?>

<?php foreach ($_['activities'] as $activity) {
	p($l->t("* %s\n", array($activity)));
}
p("\n");
