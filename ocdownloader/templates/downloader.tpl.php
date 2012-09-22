<?php

/**
* ownCloud - ocDownloader plugin
*
* @author Xavier Beurois
* @copyright 2012 Xavier Beurois www.djazz-lab.net
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
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

OC_Util::addStyle('ocdownloader', 'styles');
OC_Util::addScript('3rdparty','chosen/chosen.jquery.min');
OC_Util::addStyle('3rdparty','chosen');

OC_Util::addScript('ocdownloader', 'functions');

?>

<div id="ocdownloader">
	<div class="personalblock topblock titleblock">
		ocDownloader
	</div>
	<?php if(isset($_['curl_error'])) { ?>
	<div class="personalblock red">
		<?php print($_['curl_error']); ?>
	</div>	
	<?php }else{ ?>
	<div class="personalblock">
		<form>
			<fieldset class="personalblock">
				<label for="provsel">Select the provider to use</label><br />
				<select id="provsel" class="chzen-select" name="provsel">
					<option value="0"></option>
					<option value="web">From the WEB</option>
					<?php foreach($_['user_prov_set'] as $setting) { ?>
					<option value="<?php print($setting['pr_id']); ?>"><?php print($setting['pr_name']); ?></option>
					<?php } ?>
				</select>
			</fieldset>
		</form>
	</div>
	<div id="pr_url" class="personalblock">
		<img id="pr_logo" src="" />
		<input type="text" name="pr_txt_url" id="pr_txt_url" value="" placeholder="URL of the file to download" />
		<span id="action"><button id="geturl" rel="" title="Download">Download</button></span>
		<div id="result_state"></div>
	</div>
	<?php } ?>
</div>
