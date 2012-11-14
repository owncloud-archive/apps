<?php
    
echo('<div id="controls">	
	<a href="'.\OCP\Util::linkToAbsolute('impressionist','app.php').'" class="button">'.$l->t('Create Presentation').'</a>
	</div>
	');
    
        if(empty($_['list'])) {

            echo('<div id="emptyfolder">'.$l->t('No Impress files were created using Impressionist in your ownCloud, <br />please create a file first.').'</div>');
        }
        //The files come from the impressionist way of saving things. Check.
        else {
                echo('<table class="impresslist">');

                foreach($_['list'] as $entry) {

                        echo('<tr><td width="1"><a target="_blank" href="'.\OCP\Util::linkToAbsolute('impressionist','player.php').'?file='.urlencode($entry['url']).'&name='.urlencode($entry['name']).'"><img align="left" src="'.\OCP\Util::linkToAbsolute('impressionist','img/impress.png').'"></a></td><td><a target="_blank" href="'.\OCP\Util::linkToAbsolute('impressionist','player.php').'?file='.urlencode($entry['url']).'&name='.urlencode($entry['name']).'">'.$entry['name'].'</a></td><td>'.\OCP\Util::formatDate($entry['mtime']).'</td><td>'.\OCP\Util::humanFileSize($entry['size']).'</td></tr>');

                }
                
                echo('</table>');
     
        }

?>
