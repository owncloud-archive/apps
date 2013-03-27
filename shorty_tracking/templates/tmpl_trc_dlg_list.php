<?php
/**
* @package shorty-tracking an ownCloud url shortener plugin addition
* @category internet
* @author Christian Reiner
* @copyright 2012-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty+Tracking?content=152473
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the license, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.
* If not, see <http://www.gnu.org/licenses/>.
*
*/
?>

<?php
/**
 * @file templates/tmpl_trc_dlg.php
 * A table to visualize the hit requests to existing Shortys
 * @access public
 * @author Christian Reiner
 */
?>

<!-- a (usually hidden) dialog used to display a list of all tracked clicks hitting a Shorty -->
<div	id="shorty-tracking-list-dialog"
		class="shorty-dialog shorty-standalone"
		style="max-width:80%">
	<fieldset>
		<legend>
			<a id="close" class="shorty-close-button"
				title="<?php echo OC_Shorty_L10n::t("close"); ?>">
				<img alt="<?php echo OC_Shorty_L10n::t("close"); ?>" class="svg"
					src="<?php echo OCP\Util::imagePath('shorty','actions/shade.svg'); ?>">
			</a>
			<span class="heading"><?php echo OC_ShortyTracking_L10n::t("List of tracked clicks").':';?></span>
		</legend>
		<!-- begin: the dialogs header: linguistic reference to the Shorty and the sparkline -->
		<div id="shorty-header">
			<label for="shorty-title"><?php echo OC_Shorty_L10n::t("Title");?>: </label>
			<span id="shorty-title" class="ellipsis shorty-tracking-reference"></span>
			<br/>
			<label for="shorty-status"><?php echo OC_Shorty_L10n::t("Status");?>: </label>
			<span id="shorty-status" class="shorty-tracking-reference"></span>
			<span id="stats" class="sparkline">
				<img alt="loading…" title="<?php echo OC_Shorty_L10n::t("Loading");?>…"
					src="<?php echo OCP\Util::imagePath('shorty', 'loading-led.gif'); ?>">
			</span>
			<hr>
		</div>
		<!-- end: the dialogs header -->
		<!-- begin: the dialogs list: contains a list header and a list body -->
		<table id="list-of-clicks" class="shorty-list">
			<!-- table header -->
			<thead>
				<tr id="titlebar">
					<th id="status" >
						<div><img id="tools" alt="toolbar" title="<?php echo OC_Shorty_L10n::t("Toggle toolbar");?>"
								class="svg" src="<?php echo OCP\Util::imagePath('shorty','actions/unshade.svg'); ?>"
								data-unshade="actions/unshade"
								data-shade="actions/shade">
						</div>
					</th>
					<th id="result" ><div><?php echo OC_ShortyTracking_L10n::t("Result") ?></div></th>
					<th id="address"><div><?php echo OC_ShortyTracking_L10n::t("Address")?></div></th>
					<th id="host"   ><div><?php echo OC_ShortyTracking_L10n::t("Host")   ?></div></th>
					<th id="user"   ><div><?php echo OC_ShortyTracking_L10n::t("User")   ?></div></th>
					<th id="time"   ><div><?php echo OC_ShortyTracking_L10n::t("Time")   ?></div></th>
					<th id="actions"><div>&nbsp;</div></th>
				</tr>
				<!-- table toolbar -->
				<tr id="toolbar">
					<th id="status">
						<div style="display:none;">
							<a id="reload">
								<img alt="<?php echo OC_Shorty_L10n::t("Reload"); ?>" title="<?php echo OC_Shorty_L10n::t("Reload list"); ?>"
									class="svg" src="<?php echo OCP\Util::imagePath('shorty','actions/reload.svg'); ?>">
							</a>
						</div>
					</th>
					<th id="result">
						<div style="display:none;">
							<span class="shorty-select">
								<select id='filter' value="" data-placeholder=" ">
									<?php foreach($_['shorty-result'] as $option=>$label)
										echo sprintf("<option value=\"%s\">%s</option>\n",($option?$option:''),$label);
									?>
								</select>
							</span>
						</div>
					</th>
					<th id="address">
						<div style="display:none;">
							<input id='filter' type="text" value="">
						</div>
					</th>
					<th id="host">
						<div style="display:none;">
							<input id='filter' type="text" value="">
						</div>
					</th>
					<th id="user">
						<div style="display:none;">
							<input id='filter' type="text" value="">
						</div>
					</th>
					<th id="time">
						<div style="display:none;">
							<input id='filter' type="text" value="">
						</div>
					</th>
				</tr>
				<!-- the 'dummy' row, a blueprint -->
				<tr id="">
					<td id="status"></td>
					<td id="result" class="associative"></td>
					<td id="address" class="associative"></td>
					<td id="host" class="associative"></td>
					<td id="user" class="associative"></td>
					<td id="time"></td>
					<td id="actions">
						<span class="shorty-actions">
							<a id="shorty-tracking-action-details" title="<?php echo OC_Shorty_L10n::t("details"); ?>" data_method="OC.Shorty.Tracking.details">
								<img class="shorty-icon" alt="<?php echo OC_Shorty_L10n::t("details"); ?>"
									title="<?php echo OC_Shorty_L10n::t('Show details'); ?>" class="svg"
									src="<?php echo OCP\Util::imagePath('shorty','actions/info.svg');   ?>" />
							</a>
						</span>
					</td>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<!-- end: the dialogs list -->
		<!-- begin: the dialogs footer: status information and an icon to load the next chunk -->
		<div id="shorty-footer">
			<hr>
			<span id="scrollingTurn">
				<img id="load" alt="load" title="<?php echo OC_ShortyTracking_L10n::t("load");?>"
					class="svg" src="<?php echo OCP\Util::imagePath('shorty','actions/unshade.svg'); ?>">
			</span>
			<span style="float:left;">
				<label for="shorty-clicks"><?php echo OC_ShortyTracking_L10n::t("Clicks");?>: </label>
				<span id="shorty-clicks" class="shorty-tracking-reference"></span>
			</span>
			<span style="float:right;">
				<label for="shorty-until"><?php echo OC_Shorty_L10n::t("Expiration");?>: </label>
				<span id="shorty-until" class="shorty-tracking-reference"></span>
			</span>
		</div>
		<!-- end: the dialogs footer -->
	</fieldset>
</div>
<!-- end of clicks tracking list dialog -->
