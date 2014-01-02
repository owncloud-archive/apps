<?php

// only load text editor if the user is logged in
if (\OCP\User::isLoggedIn()) {
	OCP\Util::addStyle('files_texteditor', 'DroidSansMono/stylesheet');
	OCP\Util::addStyle('files_texteditor', 'style');
	OCP\Util::addscript('files_texteditor', 'editor');
	OCP\Util::addscript('files_texteditor', 'vendor/ace/src-noconflict/ace');
}
