<?php
/**
 * ownCloud - Persona plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */
?>
<fieldset class="personalblock">
    <legend><strong><?php p($l->t('Mozilla Persona login settings')) ?></strong>:</legend>
	<?php p($l->t('IF more than one user has email provided by Persona THEN')); ?>
    <select id="mozilla-persona-policy">
		<?php foreach ($_['allPolicies'] as $pValue => $pTitle) { ?>
			<?php $isCurrent = $pValue == $_['currentPolicy']; ?>
			<option <?php print_unescaped($isCurrent ? 'selected="selected"' : '') ?> value="<?php p($pValue) ?>">
				<?php p($l->t($pTitle)); ?>
			</option>
		<?php } ?>
    </select>
</fieldset>
