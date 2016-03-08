<?php
OC::$CLASSPATH['OC_ReaderSearchProvider'] = 'reader/lib/search.php';

OCP\App::register(array(
  'order' => 20,
  'id' => 'reader',
  'name' => 'reader'));

OCP\App::addNavigationEntry( array(
 'id' => 'reader_index',
 'order' => 20,
 'href' => OCP\Util::linkTo('reader', 'index.php'),
 'icon' => OCP\Util::imagePath( 'reader', 'reader.png' ),
 'name'=>'Reader'));
 
OC_Search::registerProvider('OC_ReaderSearchProvider');


?>
