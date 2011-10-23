<?php 
/**
* ownCloud - Background Job
*
* @author Jakob Sack
* @copyright 2011 Jakob Sack owncloud@jakobsack.de
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
OC_Util::addScript('backgroundjobs','interface','user');
?>
<table id="backgroundjobs_reportstable">
	<thead>
		<tr>
			<th><?php echo $l->t('App'); ?></th>
			<th><?php echo $l->t('Task'); ?></th>
			<th><?php echo $l->t('Report'); ?></th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $_['reports'] as $report ): ?>
			<tr data-id="<?php echo $report['id']; ?>">
				<td><?php echo $report['app']; ?></td>
				<td><?php echo $report['task']; ?></td>
				<td><?php echo $report['report']; ?></td>
				<td><img class="svg action" id="backgroundjobs_deletereport" src="<?php echo image_path('', 'actions/delete.svg'); ?>" title="<?php echo $l->t('Delete report');?>" /></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
