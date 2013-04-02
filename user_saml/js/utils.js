(function() {
    
	var saml = document.createElement('script');
	saml.type = 'text/javascript';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(saml);
})();

$(document).ready(function(){

	var loginMsg = t('user_saml', 'Login with SAML');

    $('<div id="login-saml"></div>').css({
		'text-align': 'center',
    }).appendTo('form');

	$('<p>or</p>').css(
	{
		'text-align': 'center',
        'font-weight': 'bolder',
        'font-size' : '110%'
	}).appendTo('#login-saml');

    if ($('#user').val() == "") {
        $('#password').parent().hide();
        $('#remember_login').hide();
        $('#remember_login+label').hide();
        $('#submit').hide();
    }

    $('#user').change( function() {
        if ($(this).val() !== "") {
            $('#password').parent().show();
            $('#remember_login').show();
            $('#remember_login+label').show();
            $('#submit').show();
        }
        else {
            $('#password').parent().hide();
            $('#remember_login').hide();
            $('#remember_login+label').hide();
            $('#submit').hide();
        }
    });

	$('<p>Access using SAML authentication</p>').css(
	{
		'text-align': 'center',
        'font-weight': 'bolder',
        'font-size' : '110%'
	}).appendTo('#login-saml');


    $('<a id="login-saml-action" href="?app=user_saml" ></a>').css(
    {
        'text-decoration': 'none'
    }).appendTo('#login-saml');


	$('<img id="login-saml-img" src="' + OC.imagePath('user_saml', 'logo.jpg') + '" title="'+ loginMsg +'" alt="'+ loginMsg +'" />').css(
	{
		cursor : 'pointer',
        border : '1px solid #777'
	}).appendTo('#login-saml-action');


});
