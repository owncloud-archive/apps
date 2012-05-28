<?php

OCP\Util::addScript('mail','settings');

$tmpl = new OCP\Template( 'mail', 'settings');
return $tmpl->fetchPage();
