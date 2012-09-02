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
 <label><?php echo $l->t('Pad Title:') ?></label><input id="ownpad-title" value="eetest" />
 <button id="ownpad-open"><?php echo $l->t('Open') ?></button>
  </div>
 <div id="ownpad-content"></div>
 
 <script type="text/javascript">
 var ownPad = {
	showPad : function(){
		$('#ownpad-content').pad({
			'showControls'     : true,
			'showChat'         : true,
			'showLineNumbers'  : true,
			'border'           : '1px',
			'padId'            : ownPad.getTitle(),
			'userName'         : ownPad.getUsername(),
			'host'             : 'http://beta.etherpad.org',
			'baseUrl'          : '/p/'
		});
	},
	getTitle : function(){
		return $('#ownpad-title').val();
	},
	getUsername : function(){
		return OC.currentUser;
	}
};

$('#ownpad-open').click(ownPad.showPad);
</script>