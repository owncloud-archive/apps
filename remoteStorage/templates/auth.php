		<section id="main">
		<div id="oauth">
			<h2><img src="<?php print_unescaped(image_path('', 'remoteStorage-big.png')); ?>" alt="remoteStorage" /></h2>
			<p><strong><?php p($_['host']) ?></strong>
			requests read &amp; write access to your 
			<?php
				$categories = $_['categories'];
				if(!count($categories)) {
					p($categories[0]);
				} else {
					print_unescaped('<em>'.OCP\Util::sanitizeHTML($categories[0]).'</em>');
					if(count($categories)==2) {
						print_unescaped(' and <em>'.OCP\Util::sanitizeHTML($categories[1]).'</em>');
					} else if(count($categories)>2) {
						for($i=1; $i<count($categories)-1; $i++) {
							print_unescaped(', <em>'.OCP\Util::sanitizeHTML($categories[$i]).'</em>');
						}
						print_unescaped(', and <em>'.OCP\Util::sanitizeHTML($categories[$i]).'</em>');
					}
				}
			?>.
			</p>
			<form accept-charset="UTF-8" method="post">
				<input id="allow-auth" name="allow" type="submit" value="Allow" />
				<input id="deny-auth" name="deny" type="submit" value="Deny" />
			</form>
		</div>
		</section>
