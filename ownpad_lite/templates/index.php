<?php

/**
 * ownCloud - ownpad_lite plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */
 ?>
  <div id="ownpad-location">
 <label><?php echo $l->t('Pad Title:') ?></label><input id="ownpad-title" value="eetest" /><button id="ownpad-open"><?php echo $l->t('Open') ?></button>
 </div>
 <div id="ownpad-content"></div>
 
 <script type="text/javascript">
 var ownPad = {
	showPad : function(title){ 
		$('#ownpad-content').pad({
			'padId'            : title, 
			'showControls'     : true,
			'showChat'         : true,
			'showLineNumbers'  : true,
			'border'           : '1px'
		});
	}
}
$('#ownpad-open').click(function(){
	ownPad.showPad($('#ownpad-title').val());
});
 </script>