== Users ==

Q: How do I activate Background Jobs
A: Add the following line to your crontab:
     */5 * * * * /path/to/owncloud/apps/backgroundjobs/cron.php 2>&1
   See the documentation of your distribution for more details.

== Developers ==

Use Background Jobs for adding more comfort but do not make it a requirement!

Q: How do I find out if cronjobs are enabled
A: OC_Backgroundjobs_Status::enabled() == true

Q: How do I add a cron job
A: Add the tasks to appinfo/backgroundjobs.php . That's it. Background Jobs
   calls this file everytime it is executed. Ideally, cron.php is executed
   every five minutes.

Q: But I my task does not need to run every five minutes ...
A: Background Jobs provides a variable $RUN that is increased every time
   cron.php is executed. If you want to start the task every hour, then start
   appinfo/backgroundjobs.php with

     if( $RUN % 12 == 0 ){
       return;
     }
     // Do your work

Q: I don't have to run a task regulary but it takes too long to be done while
   serving the page
A: You can use OC_Backgroundjobs_Queue::add($class,$method,$parameters) . This
   translates to call_user_func( array( $class, $method ), $parameters ); , so
   you have to make sure ownCloud knows about the module.

Q: I want to save some information the task gathered
A: Use OC_Log for this! But don't spam it.

Q: I want to improve Background Jobs.
A: Go ahead!
