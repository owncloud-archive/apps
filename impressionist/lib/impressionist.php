<?php

/**
 * ownCloud - Impressionist App + Impress App
 *
 * @author Frank Karlitschek
 * @copyright 2012 Frank Karlitschek frank@owncloud.org
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

/*

Todo:
	enable fullscreen Presentation Creation

*/

namespace OCA_Impressionist;

class Storage {

	public static function getPresentations() {
		$presentations=array();
		$list=\OC_FileCache::searchByMime('text', 'impress' ); 
		foreach($list as $l) {
			$info=pathinfo($l);
			$size=\OC_Filesystem::filesize($l);
			$mtime=\OC_Filesystem::filemtime($l);

			$entry=array('url'=>$l,'name'=>$info['filename'],'size'=>$size,'mtime'=>$mtime);
			$presentations[]=$entry;
		}

	
		return $presentations;
	}
 
        public static function showHeader() {
            echo ('
                <!doctype html>
		<html lang="en">
		<head>	
                    <meta charset="utf-8" />
                        <meta name="viewport" content="width=1024" />
                        <title>'.$title.'</title>
                        <link href="'.\OCP\Util::linkToAbsolute('impressionist', 'css/player.css').'" rel="stylesheet" />
                </head>
                <body class="impress-not-supported">
                        <div class="fallback-message">
                                <p>Your browser <b>does not support the features required</b> by impress.js, so you are presented with a simplified version of this presentation.</p>
                                <p>For the best experience please use the latest <b>Chrome</b>, <b>Safari</b> or <b>Firefox</b> browser.</p>
                                <p>Note that Impressionist currently supports only -webkit browsers.</p>
                        </div>
                        <div class="impress">       
                    ');
            
        }

	public static function showFooter() {
			
		echo('
		
                            <div class="hint">
                                <p>Make full screen and use a space bar or arrow keys to navigate</p>
                            </div>
                            <script>
                                if ("ontouchstart" in document.documentElement) { 
                                        document.querySelector(".hint").innerHTML = "<p>Tap on the left or right to navigate</p>";
                                }
                            </script>
                            <script src="'.\OCP\Util::linkToAbsolute('impressionist', 'js/impress.js').'"></script>
                            <script>impress().init();</script>		
                            <script>
                            </script>
		
		</body></html>
		');        
        }
}