
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

  angular.module('AppTemplateAdvanced').controller('ExampleController', [
    '$scope', 'Config', 'AppTemplateAdvancedRequest', '_ExampleController', function($scope, Config, AppTemplateAdvancedRequest, _ExampleController) {
      return new _ExampleController($scope, Config, AppTemplateAdvancedRequest);
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

  angular.module('AppTemplateAdvanced', ['OC']).config([
    '$provide', function($provide) {
      var Config;
      Config = {
        myParam: 'test'
      };
      Config.routes = {
        saveNameRoute: 'apptemplate_advanced_ajax_setsystemvalue'
      };
      return $provide.value('Config', Config);
    }
  ]);

  angular.module('AppTemplateAdvanced').run([
    '$rootScope', function($rootScope) {
      var init;
      init = function() {
        return $rootScope.$broadcast('routesLoaded');
      };
      return OC.Router.registerLoadedCallback(init);
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

  angular.module('AppTemplateAdvanced').filter('leetIt', function() {
    return function(leetThis) {
      return leetThis.replace('e', '3').replace('i', '1');
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

  angular.module('AppTemplateAdvanced').factory('_ExampleController', function() {
    var ExampleController;
    ExampleController = (function() {

      function ExampleController($scope, config, request) {
        var _this = this;
        this.$scope = $scope;
        this.config = config;
        this.request = request;
        this.$scope.saveName = function(name) {
          return _this.saveName(name);
        };
      }

      ExampleController.prototype.saveName = function(name) {
        return this.request.saveName(this.config.routes.saveNameRoute, name);
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
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  angular.module('AppTemplateAdvanced').factory('_AppTemplateAdvancedRequest', [
    '_Request', function(_Request) {
      var AppTemplateAdvancedRequest;
      AppTemplateAdvancedRequest = (function(_super) {

        __extends(AppTemplateAdvancedRequest, _super);

        function AppTemplateAdvancedRequest($http, $rootScope, Config) {
          AppTemplateAdvancedRequest.__super__.constructor.call(this, $http, $rootScope, Config);
        }

        AppTemplateAdvancedRequest.prototype.saveName = function(route, name) {
          var data;
          data = {
            somesetting: name
          };
          return this.post(route, {}, data);
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

  angular.module('AppTemplateAdvanced').factory('AppTemplateAdvancedRequest', [
    '$http', '$rootScope', 'Config', '_AppTemplateAdvancedRequest', function($http, $rootScope, Config, _AppTemplateAdvancedRequest) {
      return new _AppTemplateAdvancedRequest($http, $rootScope, Config);
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

  angular.module('OC').factory('_Request', function() {
    var Request;
    Request = (function() {

      function Request($http, $rootScope, Config) {
        var _this = this;
        this.$http = $http;
        this.$rootScope = $rootScope;
        this.Config = Config;
        this.initialized = false;
        this.shelvedRequests = [];
        this.$rootScope.$on('routesLoaded', function() {
          var req, _i, _len, _ref;
          _ref = _this.shelvedRequests;
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            req = _ref[_i];
            _this.post(req.route, req.routeParams, req.data, req.onSuccess, req.onFailure);
          }
          _this.initialized = true;
          return _this.shelvedRequests = [];
        });
      }

      Request.prototype.post = function(route, routeParams, data, onSuccess, onFailure) {
        var headers, postData, request, url;
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
          if (onSuccess) {
            return onSuccess(data);
          }
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


/*
# This file creates instances of classes
*/


(function() {

  angular.module('OC').factory('ModelPublisher', [
    '_ModelPublisher', function(_ModelPublisher) {
      return new _ModelPublisher();
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

  angular.module('OC').factory('_Model', function() {
    var Model;
    Model = (function() {

      function Model() {
        this.foreignKeys = {};
        this.data = [];
        this.ids = {};
      }

      Model.prototype.hasForeignKey = function(name) {
        return this.foreignKeys[name] = {};
      };

      Model.prototype.add = function(data) {
        var id, ids, name, _base, _ref, _results;
        if (this.ids[data.id] !== void 0) {
          return this.update(data);
        } else {
          this.data.push(data);
          this.ids[data.id] = data;
          _ref = this.foreignKeys;
          _results = [];
          for (name in _ref) {
            ids = _ref[name];
            id = data[name];
            (_base = this.foreignKeys[name])[id] || (_base[id] = []);
            _results.push(this.foreignKeys[name][id].push(data));
          }
          return _results;
        }
      };

      Model.prototype.update = function(item) {
        var currentItem, key, value, _results;
        currentItem = this.ids[item.id];
        _results = [];
        for (key in item) {
          value = item[key];
          if (this.foreignKeys[key] !== void 0) {
            if (value !== currentItem[key]) {
              this.updateForeignKeyCache(key, currentItem, item);
            }
          }
          if (key !== 'id') {
            _results.push(currentItem[key] = value);
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };

      Model.prototype.updateForeignKeyCache = function(name, currentItem, toItem) {
        var foreignKeyItems, fromValue, toValue;
        fromValue = currentItem[name];
        toValue = toItem[name];
        foreignKeyItems = this.foreignKeys[name][fromValue];
        this.removeForeignKeyCacheItem(foreignKeyItems, currentItem);
        return this.foreignKeys[name][toValue].push(item);
      };

      Model.prototype.removeForeignKeyCacheItem = function(foreignKeyItems, item) {
        var fkItem, index, _i, _len, _results;
        _results = [];
        for (index = _i = 0, _len = foreignKeyItems.length; _i < _len; index = ++_i) {
          fkItem = foreignKeyItems[index];
          if (fkItem.id === id) {
            _results.push(this.foreignKeys[key][item[key]].splice(index, 1));
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };

      Model.prototype.removeById = function(id) {
        var foreignKeyItems, ids, index, item, key, _i, _len, _ref, _ref1;
        item = this.getById(id);
        _ref = this.foreignKeys;
        for (key in _ref) {
          ids = _ref[key];
          foreignKeyItems = ids[item[key]];
          this.removeForeignKeyCacheItem(foreignKeyItems, item);
        }
        _ref1 = this.data;
        for (index = _i = 0, _len = _ref1.length; _i < _len; index = ++_i) {
          item = _ref1[index];
          if (item.id === id) {
            this.data.splice(index, 1);
          }
        }
        return delete this.ids[id];
      };

      Model.prototype.getById = function(id) {
        return this.ids[id];
      };

      Model.prototype.getAll = function() {
        return this.data;
      };

      Model.prototype.getAllOfForeignKeyWithId = function(foreignKeyName, foreignKeyId) {
        return this.foreignKeys[foreignKeyName][foreignKeyId];
      };

      return Model;

    })();
    return Model;
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


/*
# Used for properly distributing received model data from the server
*/


(function() {

  angular.module('OC').factory('_ModelPublisher', function() {
    var ModelPublisher;
    ModelPublisher = (function() {

      function ModelPublisher() {
        this.subscriptions = {};
      }

      ModelPublisher.prototype.subscribeModelTo = function(model, name) {
        var _base;
        (_base = this.subscriptions)[name] || (_base[name] = []);
        return this.subscriptions[name].push(model);
      };

      ModelPublisher.prototype.publishDataTo = function(data, name) {
        var subscriber, _i, _len, _ref, _results;
        _ref = this.subscriptions[name] || [];
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          subscriber = _ref[_i];
          _results.push(subscriber.handle(data));
        }
        return _results;
      };

      return ModelPublisher;

    })();
    return ModelPublisher;
  });

}).call(this);
