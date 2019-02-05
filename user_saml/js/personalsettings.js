$(document).ready(function() {
        $('#regenerate_password_button').on( "click", function(){
              OC.dialogs.confirm(
                    t('user_saml', 'Are you sure to reset your desktop client password?'),
                    t('user_saml', 'Reset desktop client password?'),
                    function(reset) {
                        if(reset) {
                           $.post(OC.filePath('user_saml', 'ajax', 'regenerate_password.php'), "regenerate_password", function(data){
                              $("#newpassword").text(data.data);
                              });
                            }
                        },
                    true
                    );

        });
});
