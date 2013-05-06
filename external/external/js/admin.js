/**
 * 2013 Tobia De Koninck tobia@ledfan.be
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

$(document).ready(function(){
  
  	function setAllowUsers(){
		$.post( OC.filePath('external','ajax','allowusers.php') , {allowUsers : $('#allowUsers:checked').val()}, function(data) {
		});
	}
	
	$('#allowUsers').click(function(){
		setAllowUsers();
	});
});

