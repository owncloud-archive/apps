(function() {
    
	var cas = document.createElement('script');
	cas.type = 'text/javascript';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(cas);
})();

$(document).ready(function(){

	var loginMsg = t('user_cas', 'Login with CAS');

    $('<div id="login-cas"></div>').css({
		'text-align': 'center',
    }).appendTo('form');

	$('<p>or</p>').css(
	{
		'text-align': 'center',
        'font-weight': 'bolder',
        'font-size' : '110%'
	}).appendTo('#login-cas');

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

	$('<p>Access using CAS authentication</p>').css(
	{
		'text-align': 'center',
        'font-weight': 'bolder',
        'font-size' : '110%'
	}).appendTo('#login-cas');


    $('<a id="login-cas-action" href="?app=user_cas" ></a>').css(
    {
        'text-decoration': 'none'
    }).appendTo('#login-cas');


	$('<img id="login-cas-img" src="' + OC.imagePath('user_cas', 'logo.jpg') + '" title="'+ loginMsg +'" alt="'+ loginMsg +'" />').css(
	{
		cursor : 'pointer',
		border : '1px solid #777'
	}).appendTo('#login-cas-action');


});
