<?php

/**
 * ownCloud - Updater plugin
 *
 * @author Victor Dubiniuk
 * @copyright 2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

?>
<?php $data = OC_Updater::check(); ?>
<?php $isNewVersionAvailable = isset($data['version']) && $data['version'] != '' && $data['version'] !== Array() ?>
<div id="updater-content" ng-app="updater" ng-init="navigation='backup'">
	<ul ng-model="navigation">
		<li ng-click="navigation='backup'" ng-class="{current : navigation=='backup'}"><?php p($l->t('Backup Management')) ?></li>
		<li ng-click="navigation='update'" ng-class="{current : navigation=='update'}"><?php p($l->t('Update')) ?></li>
	</ul>
	<fieldset ng-controller="backupCtrl" ng-show="navigation=='backup'">
		<label for="backupbase"><?php p($l->t('Backup directory')) ?></label>
		<input readonly="readonly" type="text" id="backupbase" value="<?php p(\OCA\Updater\App::getBackupBase()); ?>" />
		<table ng-controller="backupCtrl">
			<thead ng-hide="!entries.length">
				<tr>
					<th><?php p($l->t('Backup')) ?></th>
					<th><?php p($l->t('Done on')) ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<tr ng-repeat="entry in entries">
					<td title="<?php p($l->t('Download')) ?>" class="item" ng-click="doDownload(entry.title)">{{entry.title}}</td>
					<td title="<?php p($l->t('Download')) ?>" class="item" ng-click="doDownload(entry.title)">{{entry.date}}</td>
					<td title="<?php p($l->t('Delete')) ?>" class="item" ng-click="doDelete(entry.title)"><?php p($l->t('Delete')) ?></td>
				</tr>
				<tr ng-show="!entries.length"><td colspan="3"><?php p($l->t('No backups found')) ?></td></tr>
			</tbody>
		</table>
	</fieldset>
	<fieldset ng-controller="updateCtrl" ng-show="navigation=='update'">
		<button ng-click="update()" ng-show="<?php p($isNewVersionAvailable) ?>" id="updater-start">
			<?php p($l->t('Update')) ?>
		</button>
		<div id="upd-progress" style="display:none;"><div></div></div>
	</fieldset>
</div>
