<?php

OC::$CLASSPATH['OC_Backgroundjobs_Worker']  = 'apps/backgroundjobs/lib/worker.php';
OC::$CLASSPATH['OC_Backgroundjobs_Status']  = 'apps/backgroundjobs/lib/status.php';
OC::$CLASSPATH['OC_Backgroundjobs_Queue']   = 'apps/backgroundjobs/lib/queue.php';

OC_App::register( array(
  'order' => 99,
  'id' => 'backgroundjobs',
  'name' => 'Background Jobs' ));
