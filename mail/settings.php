<?php

OC_Util::addScript('mail','settings');

$tmpl = new OC_Template( 'mail', 'settings');
return $tmpl->fetchPage();
