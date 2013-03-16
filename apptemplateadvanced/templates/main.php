{{ script('vendor/angular/angular') }}
{{ script('public/app') }}
{{ style('style') }}
{{ style('animation') }}

<div id="app"
	ng-app="AppTemplateAdvanced"
	ng-controller="ExampleController">

	<h1 class="heading">This is an advanced app template</h1>
	<p>{{ trans('This string will be %s', 'translated') }}</p>
	<p>The URL Parameter for the index page is: {{test}}</p>

	<p ng-show="name">Welcome home [[name | leetIt]]!</p>

	<form class="centered">
	        My name is <input type="text" placeholder="anonymous" ng-model="name">
	        <button ng-click="saveName(name)">Remember my name</button>
	</form>

	<p>Your username is {{item.getUser}}</p>
	<p>Your username entry was saved with the path {{item.getPath}}</p>

	<p>You can also <a href="{{ url('apptemplate_advanced_index_param', {test: 'ho'}) }}">link</a> to
		a specific route </p>

	<p>If you need an absolute url use <a href="{{ abs_url('apptemplate_advanced_index_param', {test: 'ho'}) }}">link</a> to
		a specific route </p>
</div>



