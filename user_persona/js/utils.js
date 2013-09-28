/**
 * ownCloud - Persona plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

(function() {
	var Persona = document.createElement('script');
	Persona.type = 'text/javascript';
	Persona.async = true;
	Persona.src = 'https://login.persona.org/include.js';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(Persona);
})();

var PersonaAuth = {
	gotAssertion : function(assertion){
		if (assertion !== null) {
			$.post(
				'index.php',
				{   
					'authService' : 'MozillaPersona',
					'user' : $('#user').val(),
					'password' : assertion,
					sectoken : $('#sectoken').val()
				},
				PersonaAuth.authResponse
			);
		}
	},
	authResponse : function(data){
		if (data && data.status=='success'){
			//Logged in
			if (data.msg && data.msg=='Access granted'){
				window.location.reload();
				return;
			//List of users arrived
			} else if (data.list){
				var list = data.list || [];
				var offset = $('#login-persona').offset();
				$('<div id="hint-persona"></div>').css({
					position:'absolute',
					top:offset.top, 
					left:offset.left + $('#login-persona').width(),
					background:'#ccc',
					padding:'10px'
				}).appendTo('body');
				for (var i=0;i<list.length;i++){
					$('<a href="#">'+list[i]+'</a><br />').appendTo($('#hint-persona'));
				}
				$('#hint-persona a').click(PersonaAuth.loginAs);
			}
		//Unknown reponse - login failed
		} else {
			OC.dialogs.alert(t('user_persona', 'Please try different method.'), t('user_persona', 'Failed to Login with Mozilla Persona'));
		}
	},
	loginAs : function(event){
		event.preventDefault();
		$('#user').val(this.innerHTML);
		$('#user').trigger('keydown');	
		$('#hint-persona').remove();
		$('#login-persona').trigger('click');
		return false;
	}
};

$(document).ready(function(){
	var loginMsg = t('user_persona', 'Login with Mozilla Persona');
	$('<img id="login-persona" src="' + OC.imagePath('user_persona', 'sign_in_blue.png') + '" title="'+ loginMsg +'" alt="'+ loginMsg +'" />').css(
	{
		cursor : 'pointer',
		'float' : 'right'
	}).appendTo('form');
    
	$('#login-persona').click(function() {
		navigator.id.get(PersonaAuth.gotAssertion);
		return false;
	});
});
