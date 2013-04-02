<?php
$l=new OC_L10N('tasks');
OC::$CLASSPATH['OC_Task_App'] = 'apps/tasks/lib/app.php';

OCP\App::addNavigationEntry( array(
  'id' => 'tasks_index',
  'order' => 11,
  'href' => OCP\Util::linkTo( 'tasks', 'index.php' ),
  'icon' => OCP\Util::imagePath( 'tasks', 'tasks.svg' ),
  'name' => $l->t('Tasks')));
