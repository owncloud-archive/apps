<?php $count=0; ?>
<form id='user_vd_form' method="POST" action="#">
	<div id="uservdSettings" class="personalblock">
		<ul>
			<li><a href="#uservdSettings-1">Domain relations</a></li>
			<li><a href="#uservdSettings-2">Configuration</a></li>
		</ul>

		<fieldset id="uservdSettings-1">
			<?php foreach ($_['domains'] as $domain): ?>
				<p>
				<input type="text" name="domain[<?=$count?>]" value="<?=$domain['domain']?>">
				<input type="text" name="fqdn[<?=$count?>]" value="<?=$domain['fqdn']?>">
				</p>
			<?php $count++; endforeach; ?>
			<p>
			<input type="text" name="domain[<?=$count?>]" value="">
			<input type="text" name="fqdn[<?=$count?>]" value="">
			</p>
		</fieldset>
		<fieldset id="uservdSettings-2">
			<p>
				Force user_vd backend to create new users? 
				<input type="checkbox" name="forceCreateUsers" id="forceCreateUsers" <?php if ($_['forceCreateUsers']) p(' checked'); ?>>
			</p>
			<p>
				Disable other backends? 
				<input type="checkbox" name="disableBackends" id="disableBackends" <?php if ($_['disableBackends']) p(' checked'); ?>>
			</p>
		</fieldset>
		<input type='submit' value='Save'>
	</div>
</form>
