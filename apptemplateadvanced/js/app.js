
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

  angular.module('AppTemplateAdvanced').run([
    '$rootScope', function($rootScope) {
      var initRequest;
      initRequest = function() {
        $rootScope.$broadcast('routesLoaded');
        return console.log('loading');
      };
      initRequest();
      return OC.Router.registerLoadedCallback(initRequest);
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

      function Request($http, $rootScope, Config, publisher) {
        var _this = this;
        this.$http = $http;
        this.$rootScope = $rootScope;
        this.Config = Config;
        this.publisher = publisher;
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
            'requesttoken': requestToken,
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

  angular.module('OC').factory('_Model', function() {
    var Model;
    Model = (function() {

      function Model() {
        this.foreignKeys = {};
        this.data = [];
        this.ids = {};
      }

      Model.prototype.handle = function(data) {
        var item, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2, _results;
        if (data['create'] !== void 0) {
          _ref = data['create'];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            item = _ref[_i];
            this.create(item);
          }
        }
        if (data['update'] !== void 0) {
          _ref1 = data['update'];
          for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
            item = _ref1[_j];
            this.update(item);
          }
        }
        if (data['delete'] !== void 0) {
          _ref2 = data['delete'];
          _results = [];
          for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
            item = _ref2[_k];
            _results.push(this["delete"](item));
          }
          return _results;
        }
      };

      Model.prototype.hasForeignKey = function(name) {
        return this.foreignKeys[name] = {};
      };

      Model.prototype.create = function(data) {
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
              this._updateForeignKeyCache(key, currentItem, item);
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

      Model.prototype["delete"] = function(item) {
        if (this.getById(item.id) !== void 0) {
          return this.removeById(item.id);
        }
      };

      Model.prototype._updateForeignKeyCache = function(name, currentItem, toItem) {
        var foreignKeyItems, fromValue, toValue;
        fromValue = currentItem[name];
        toValue = toItem[name];
        foreignKeyItems = this.foreignKeys[name][fromValue];
        this._removeForeignKeyCacheItem(foreignKeyItems, currentItem);
        return this.foreignKeys[name][toValue].push(item);
      };

      Model.prototype._removeForeignKeyCacheItem = function(foreignKeyItems, item) {
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
          this._removeForeignKeyCacheItem(foreignKeyItems, item);
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
    '$http', '$rootScope', 'Config', '_AppTemplateAdvancedRequest', 'Publisher', function($http, $rootScope, Config, _AppTemplateAdvancedRequest, Publisher) {
      return new _AppTemplateAdvancedRequest($http, $rootScope, Config, Publisher);
    }
  ]);

  angular.module('AppTemplateAdvanced').factory('ItemModel', [
    '_ItemModel', 'Publisher', function(_ItemModel, Publisher) {
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

        function AppTemplateAdvancedRequest($http, $rootScope, Config, Publisher) {
          AppTemplateAdvancedRequest.__super__.constructor.call(this, $http, $rootScope, Config, Publisher);
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
            scope.name = data.data.somesetting;
            return console.log(data);
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
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  angular.module('AppTemplateAdvanced').factory('_ItemModel', [
    '_Model', function(_Model) {
      var ItemModel;
      ItemModel = (function(_super) {

        __extends(ItemModel, _super);

        function ItemModel() {
          ItemModel.__super__.constructor.call(this);
        }

        return ItemModel;

      })(_Model);
      return ItemModel;
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
        this.$scope.$on('routesLoaded', function() {
          return _this.getName(_this.$scope);
        });
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
