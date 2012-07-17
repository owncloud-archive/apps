(function() {
	var saml = document.createElement('script');
	saml.type = 'text/javascript';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(saml);
})();

$(document).ready(function(){

	var loginMsg = t('user_saml', 'Login with SAML');

    $('<div id="login-saml"></div>').css(
    {
		'text-align': 'center',
    }).appendTo('form');

// Hide or print the 'or' and the buttons
//    $('fieldset').hide();


	$('<p>or</p>').css(
	{
		'text-align': 'center',
        'font-weight': 'bolder',
        'font-size' : '110%'
	}).appendTo('#login-saml');

    $('#remember_login').show();
    $('#remember_login+label').show();
    $('#submit').show(); 

//

	$('<p>Access using SAML authentication</p>').css(
	{
		'text-align': 'center',
        'font-weight': 'bolder',
        'font-size' : '110%'
	}).appendTo('#login-saml');


    $('<a id="login-saml-action" href="apps/user_saml/auth.php" ></a>').css(
    {
        'text-decoration': 'none'
    }).appendTo('#login-saml');


	$('<img id="login-saml-img" src="' + OC.imagePath('user_saml', 'logo.jpg') + '" title="'+ loginMsg +'" alt="'+ loginMsg +'" />').css(
	{
		cursor : 'pointer',
        border : '1px solid #777'
	}).appendTo('#login-saml-action');


});
