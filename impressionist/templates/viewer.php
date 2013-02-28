<?php
    
print_unescaped('<div id="controls">	
	<a href="'.\OCP\Util::linkToAbsolute('impressionist','app.php').'" class="button">'.$l->t('Create Presentation').'</a>
	</div>
	');
    
        if(empty($_['list'])) {

            print_unescaped('<div id="emptyfolder">'.$l->t('No Impress files were created using Impressionist in your ownCloud, <br />Please create a file first. <br />Supports Webkit Browsers only.').'</div>');
        }
        //The files come from the impressionist way of saving things. Check.
        else {
                print_unescaped('<table class="impresslist">');

                foreach($_['list'] as $entry) {

                        print_unescaped('<tr><td width="1"><a target="_blank" href="'.\OCP\Util::linkToAbsolute('impressionist','player.php').'?file='.urlencode($entry['url']).'&name='.urlencode($entry['name']).'"><img align="left" src="'.\OCP\Util::linkToAbsolute('impressionist','img/impress.png').'"></a></td><td><a target="_blank" href="'.\OCP\Util::linkToAbsolute('impressionist','player.php').'?file='.urlencode($entry['url']).'&name='.urlencode($entry['name']).'">'.$entry['name'].'</a></td><td>'.\OCP\Util::formatDate($entry['mtime']).'</td><td>'.\OCP\Util::humanFileSize($entry['size']).'</td></tr>');

                }
                
                print_unescaped('</table>');
     
        }

?>
