<?php
OC::$CLASSPATH['OCA\Files_Texteditor\App'] = 'files_texteditor/lib/app.php';

//load the required files
OCP\Util::addStyle( 'files_texteditor', 'DroidSansMono/stylesheet' );
OCP\Util::addStyle( 'files_texteditor', 'style' );
OCP\Util::addscript( 'files_texteditor', 'editor');
OCP\Util::addscript( 'files_texteditor', 'aceeditor/ace');

