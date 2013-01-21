<div id="app"
	ng-app="AppTemplateAdvanced"
	ng-controller="ExampleController"
	ng-init="name='<?php p($_['somesetting']) ?>'">

	<h1 class="heading">This is an advanced app template</h1>

	<p>The URL Parameter for the index page is: <?php p($_['test']) ?></p>

	<p ng-show="name">Welcome home {{name | leetIt}}!</p>

	<form class="centered">
	        My name is <input type="text" placeholder="anonymous" ng-model="name">
	        <button ng-click="saveName(name)">Remember my name</button>
	</form>

	<p>Your username is <?php p($_['item']->getUser()) ?></p>
	<p>Your username entry was saved with the path <?php p($_['item']->getPath()) ?></p>
</div>



