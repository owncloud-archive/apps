<?php

// show toolbar
print_unescaped('<div id="controls">	
	<a href="'.\OCP\Util::linkToAbsolute('impress','documentation.php').'" class="button docu">'.$l->t('Documentation').'</a>
	</div>
	');

if(empty($_['list'])) {
	print_unescaped('<div id="emptyfolder">'.$l->t('No Impress files are found in your ownCloud. Please upload a .impress file.').'</div>');
} else {
	print_unescaped('<table class="impresslist"><thead id="impressHeader"><tr><th id="impressImg"></th><th id="impressName">'.$l->t("Name").'</th><th id="impressSize">'.$l->t("Size").'</th><th id="impressDate">'.$l->t("Date").'</th></tr></thead>');
	foreach($_['list'] as $entry) {
		print_unescaped('<tr><td width="1"><a target="_blank" href="'.\OCP\Util::linkToAbsolute('impress','player.php').'?file='.urlencode($entry['url']).'&name='.urlencode($entry['name']).'"><img align="left" src="'.\OCP\Util::linkToAbsolute('impress','img/impressbig.png').'"></a></td><td><a target="_blank" href="'.\OCP\Util::linkToAbsolute('impress','player.php').'?file='.urlencode($entry['url']).'&name='.urlencode($entry['name']).'">'.$entry['name'].'</a></td><td class="tablefilesize">'.\OCP\Util::humanFileSize($entry['size']).'</td><td>'.\OCP\Util::formatDate($entry['mtime']).'</td></tr>');
	}
	print_unescaped('</table>');
}