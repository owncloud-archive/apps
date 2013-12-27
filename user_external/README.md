External user authentication
============================

Authenticate user login against FTP, IMAP or SMB.


## Configuration

### IMAP
Add the following to your `config.php`:

    'user_backends' => array(
        array(
            'class' => 'OC_User_IMAP',
            'arguments' => array(
                '{127.0.0.1:143/imap/readonly}',
            ),
        ),
    ),

This connects to the IMAP server on `localhost`.

Read the [imap_open][0] PHP manual page to learn more about the allowed
parameters.

[0]: http://php.net/imap_open#refsect1-function.imap-open-parameters
