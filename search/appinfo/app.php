<?php

OCP\App::addNavigationEntry(array(
    'id' => 'search',
    'href' => OCP\Util::linkTo('search', 'index.php'),
    'icon' => OCP\Util::imagePath('', 'actions/search.svg'),
    'name' => 'Adv. Search',
    'order' => 50
));
