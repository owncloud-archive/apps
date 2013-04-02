<?php

$tmpl = new OCP\Template( 'contacts', 'settings');
$tmpl->assign('addressbooks', OCA\Contacts\Addressbook::all(OCP\USER::getUser()));

$tmpl->printPage();
