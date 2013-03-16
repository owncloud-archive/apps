
/*
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
*/


(function() {

  angular.module('OC', []);

}).call(this);



/*
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
*/


(function() {

  angular.module('AppTemplateAdvanced', ['OC']).config([
    '$provide', '$interpolateProvider', function($provide, $interpolateProvider) {
      var Config;
      $interpolateProvider.startSymbol('[[');
      $interpolateProvider.endSymbol(']]');
      Config = {
        myParam: 'test'
      };
      Config.routes = {
        saveNameRoute: 'apptemplate_advanced_ajax_setsystemvalue',
        getNameRoute: 'apptemplate_advanced_ajax_getsystemvalue'
      };
      return $provide.value('Config', Config);
    }
  ]);

}).call(this);



/*
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
*/


/*
# Used for properly distributing received model data from the server
*/


(function() {

  angular.module('OC').factory('_Publisher', function() {
    var Publisher;
    Publisher = (function() {

      function Publisher() {
        this.subscriptions = {};
      }

      Publisher.prototype.subscribeModelTo = function(model, name) {
        var _base;
        (_base = this.subscriptions)[name] || (_base[name] = []);
        return this.subscriptions[name].push(model);
      };

      Publisher.prototype.publishDataTo = function(data, name) {
        var subscriber, _i, _len, _ref, _results;
        _ref = this.subscriptions[name] || [];
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          subscriber = _ref[_i];
          _results.push(subscriber.handle(data));
        }
        return _results;
      };

      return Publisher;

    })();
    return Publisher;
  });

}).call(this);



/*
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
*/


(function() {

  angular.module('OC').factory('_Request', function() {
    var Request;
    Request = (function() {

      function Request($http, Config, publisher) {
        var _this = this;
        this.$http = $http;
        this.Config = Config;
        this.publisher = publisher;
        this.initialized = false;
        this.shelvedRequests = [];
        OC.Router.registerLoadedCallback(function() {
          var req, _i, _len, _ref;
          _this.initialized = true;
          _ref = _this.shelvedRequests;
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            req = _ref[_i];
            _this.post(req.route, req.routeParams, req.data, req.onSuccess, req.onFailure);
          }
          return _this.shelvedRequests = [];
        });
      }

      Request.prototype.post = function(route, routeParams, data, onSuccess, onFailure) {
        var headers, postData, request, url,
          _this = this;
        if (!this.initialized) {
          request = {
            route: route,
            routeParams: routeParams,
            data: data,
            onSuccess: onSuccess,
            onFailure: onFailure
          };
          this.shelvedRequests.push(request);
          return;
        }
        if (routeParams) {
          url = OC.Router.generate(route, routeParams);
        } else {
          url = OC.Router.generate(route);
        }
        data || (data = {});
        postData = $.param(data);
        headers = {
          headers: {
            'requesttoken': oc_requesttoken,
            'Content-Type': 'application/x-www-form-urlencoded'
          }
        };
        return this.$http.post(url, postData, headers).success(function(data, status, headers, config) {
          var name, value, _ref, _results;
          if (onSuccess) {
            onSuccess(data);
          }
          _ref = data.data;
          _results = [];
          for (name in _ref) {
            value = _ref[name];
            _results.push(_this.publisher.publishDataTo(name, value));
          }
          return _results;
        }).error(function(data, status, headers, config) {
          if (onFailure) {
            return onFailure(data);
          }
        });
      };

      return Request;

    })();
    return Request;
  });

}).call(this);



/*
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
*/


(function() {

  angular.module('AppTemplateAdvanced').filter('leetIt', function() {
    return function(leetThis) {
      if (leetThis !== void 0) {
        return leetThis.replace('e', '3').replace('i', '1');
      } else {
        return '';
      }
    };
  });

}).call(this);



/*
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
*/


(function() {

  angular.module('AppTemplateAdvanced').factory('AppTemplateAdvancedRequest', [
    '$http', 'Config', '_AppTemplateAdvancedRequest', 'Publisher', function($http, Config, _AppTemplateAdvancedRequest, Publisher) {
      return new _AppTemplateAdvancedRequest($http, Config, Publisher);
    }
  ]);

  angular.module('AppTemplateAdvanced').factory('ItemModel', [
    '_ItemModel', function(_ItemModel) {
      return new _ItemModel();
    }
  ]);

  angular.module('AppTemplateAdvanced').factory('Publisher', [
    '_Publisher', 'ItemModel', function(_Publisher, ItemModel) {
      var publisher;
      publisher = new _Publisher();
      publisher.subscribeModelTo(ItemModel, 'items');
      return publisher;
    }
  ]);

}).call(this);



/*
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
*/


(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  angular.module('AppTemplateAdvanced').factory('_AppTemplateAdvancedRequest', [
    '_Request', function(_Request) {
      var AppTemplateAdvancedRequest;
      AppTemplateAdvancedRequest = (function(_super) {

        __extends(AppTemplateAdvancedRequest, _super);

        function AppTemplateAdvancedRequest($http, Config, Publisher) {
          AppTemplateAdvancedRequest.__super__.constructor.call(this, $http, Config, Publisher);
        }

        AppTemplateAdvancedRequest.prototype.saveName = function(route, name) {
          var data;
          data = {
            somesetting: name
          };
          return this.post(route, {}, data);
        };

        AppTemplateAdvancedRequest.prototype.getName = function(route, scope) {
          var success;
          success = function(data) {
            return scope.name = data.data.somesetting;
          };
          return this.post(route, {}, {}, success);
        };

        return AppTemplateAdvancedRequest;

      })(_Request);
      return AppTemplateAdvancedRequest;
    }
  ]);

}).call(this);



/*
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
*/


(function() {

  angular.module('AppTemplateAdvanced').factory('_ItemModel', function() {
    var ItemModel;
    ItemModel = (function() {

      function ItemModel() {}

      ItemModel.prototype.handle = function(data) {};

      return ItemModel;

    })();
    return ItemModel;
  });

}).call(this);



/*
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
*/


(function() {

  angular.module('AppTemplateAdvanced').factory('_ExampleController', function() {
    var ExampleController;
    ExampleController = (function() {

      function ExampleController($scope, config, request, itemModel) {
        var _this = this;
        this.$scope = $scope;
        this.config = config;
        this.request = request;
        this.itemModel = itemModel;
        this.$scope.saveName = function(name) {
          return _this.saveName(name);
        };
        this.getName(this.$scope);
      }

      ExampleController.prototype.saveName = function(name) {
        return this.request.saveName(this.config.routes.saveNameRoute, name);
      };

      ExampleController.prototype.getName = function(scope) {
        return this.request.getName(this.config.routes.getNameRoute, scope);
      };

      return ExampleController;

    })();
    return ExampleController;
  });

}).call(this);



/*
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
*/


(function() {

  angular.module('AppTemplateAdvanced').controller('ExampleController', [
    '$scope', 'Config', 'AppTemplateAdvancedRequest', '_ExampleController', 'ItemModel', function($scope, Config, AppTemplateAdvancedRequest, _ExampleController, ItemModel) {
      return new _ExampleController($scope, Config, AppTemplateAdvancedRequest, ItemModel);
    }
  ]);

}).call(this);
