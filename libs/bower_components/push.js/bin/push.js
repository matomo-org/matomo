/**
 * Push v1.0
 * =========
 * A compact, cross-browser solution for the JavaScript Notifications API
 *
 * Credits
 * -------
 * Tsvetan Tsvetkov (ttsvetko)
 * Alex Gibson (alexgibson)
 *
 * License
 * -------
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015-2017 Tyler Nickerson
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
	typeof define === 'function' && define.amd ? define(factory) :
	(global.Push = factory());
}(this, (function () { 'use strict';

function _typeof(obj) {
  if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
    _typeof = function (obj) {
      return typeof obj;
    };
  } else {
    _typeof = function (obj) {
      return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    };
  }

  return _typeof(obj);
}

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      enumerable: false,
      writable: true,
      configurable: true
    }
  });
  if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
}

function _possibleConstructorReturn(self, call) {
  if (call && (typeof call === "object" || typeof call === "function")) {
    return call;
  }

  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

var errorPrefix = 'PushError:';
var Messages = {
  errors: {
    incompatible: "".concat(errorPrefix, " Push.js is incompatible with browser."),
    invalid_plugin: "".concat(errorPrefix, " plugin class missing from plugin manifest (invalid plugin). Please check the documentation."),
    invalid_title: "".concat(errorPrefix, " title of notification must be a string"),
    permission_denied: "".concat(errorPrefix, " permission request declined"),
    sw_notification_error: "".concat(errorPrefix, " could not show a ServiceWorker notification due to the following reason: "),
    sw_registration_error: "".concat(errorPrefix, " could not register the ServiceWorker due to the following reason: "),
    unknown_interface: "".concat(errorPrefix, " unable to create notification: unknown interface")
  }
};

var Permission =
/*#__PURE__*/
function () {
  // Private members
  // Public members
  function Permission(win) {
    _classCallCheck(this, Permission);
    this._win = win;
    this.GRANTED = 'granted';
    this.DEFAULT = 'default';
    this.DENIED = 'denied';
    this._permissions = [this.GRANTED, this.DEFAULT, this.DENIED];
  }
  /**
  * Requests permission for desktop notifications
  * @param {Function} onGranted - Function to execute once permission is granted
  * @param {Function} onDenied - Function to execute once permission is denied
  * @return {void, Promise}
  */


  _createClass(Permission, [{
    key: "request",
    value: function request(onGranted, onDenied) {
      return arguments.length > 0 ? this._requestWithCallback.apply(this, arguments) : this._requestAsPromise();
    }
    /**
    * Old permissions implementation deprecated in favor of a promise based one
    * @deprecated Since V1.0.4
    * @param {Function} onGranted - Function to execute once permission is granted
    * @param {Function} onDenied - Function to execute once permission is denied
    * @return {void}
    */

  }, {
    key: "_requestWithCallback",
    value: function _requestWithCallback(onGranted, onDenied) {
      var _this = this;

      var existing = this.get();

      var resolve = function resolve() {
        var result = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : _this._win.Notification.permission;
        if (typeof result === 'undefined' && _this._win.webkitNotifications) result = _this._win.webkitNotifications.checkPermission();

        if (result === _this.GRANTED || result === 0) {
          if (onGranted) onGranted();
        } else if (onDenied) onDenied();
      };
      /* Permissions already set */


      if (existing !== this.DEFAULT) {
        resolve(existing);
      } else if (this._win.webkitNotifications && this._win.webkitNotifications.checkPermission) {
        /* Safari 6+, Legacy webkit browsers */
        this._win.webkitNotifications.requestPermission(resolve);
      } else if (this._win.Notification && this._win.Notification.requestPermission) {
        /* Chrome 23+ */
        this._win.Notification.requestPermission().then(resolve).catch(function () {
          if (onDenied) onDenied();
        });
      } else if (onGranted) {
        /* Let the user continue by default */
        onGranted();
      }
    }
    /**
    * Requests permission for desktop notifications in a promise based way
    * @return {Promise}
    */

  }, {
    key: "_requestAsPromise",
    value: function _requestAsPromise() {
      var _this2 = this;

      var existing = this.get();

      var isGranted = function isGranted(result) {
        return result === _this2.GRANTED || result === 0;
      };
      /* Permissions already set */


      var hasPermissions = existing !== this.DEFAULT;
      /* Safari 6+, Chrome 23+ */

      var isModernAPI = this._win.Notification && this._win.Notification.requestPermission;
      /* Legacy webkit browsers */

      var isWebkitAPI = this._win.webkitNotifications && this._win.webkitNotifications.checkPermission;
      return new Promise(function (resolvePromise, rejectPromise) {
        var resolver = function resolver(result) {
          return isGranted(result) ? resolvePromise() : rejectPromise();
        };

        if (hasPermissions) {
          resolver(existing);
        } else if (isWebkitAPI) {
          _this2._win.webkitNotifications.requestPermission(function (result) {
            resolver(result);
          });
        } else if (isModernAPI) {
          _this2._win.Notification.requestPermission().then(function (result) {
            resolver(result);
          }).catch(rejectPromise);
        } else resolvePromise();
      });
    }
    /**
    * Returns whether Push has been granted permission to run
    * @return {Boolean}
    */

  }, {
    key: "has",
    value: function has() {
      return this.get() === this.GRANTED;
    }
    /**
    * Gets the permission level
    * @return {Permission} The permission level
    */

  }, {
    key: "get",
    value: function get() {
      var permission;
      /* Safari 6+, Chrome 23+ */

      if (this._win.Notification && this._win.Notification.permission) permission = this._win.Notification.permission;else if (this._win.webkitNotifications && this._win.webkitNotifications.checkPermission)
        /* Legacy webkit browsers */
        permission = this._permissions[this._win.webkitNotifications.checkPermission()];else if (navigator.mozNotification)
        /* Firefox Mobile */
        permission = this.GRANTED;else if (this._win.external && this._win.external.msIsSiteMode)
        /* IE9+ */
        permission = this._win.external.msIsSiteMode() ? this.GRANTED : this.DEFAULT;else permission = this.GRANTED;
      return permission;
    }
  }]);
  return Permission;
}();

var Util =
/*#__PURE__*/
function () {
  function Util() {
    _classCallCheck(this, Util);
  }

  _createClass(Util, null, [{
    key: "isUndefined",
    value: function isUndefined(obj) {
      return obj === undefined;
    }
  }, {
    key: "isNull",
    value: function isNull(obs) {
      return obj === null;
    }
  }, {
    key: "isString",
    value: function isString(obj) {
      return typeof obj === 'string';
    }
  }, {
    key: "isFunction",
    value: function isFunction(obj) {
      return obj && {}.toString.call(obj) === '[object Function]';
    }
  }, {
    key: "isObject",
    value: function isObject(obj) {
      return _typeof(obj) === 'object';
    }
  }, {
    key: "objectMerge",
    value: function objectMerge(target, source) {
      for (var key in source) {
        if (target.hasOwnProperty(key) && this.isObject(target[key]) && this.isObject(source[key])) {
          this.objectMerge(target[key], source[key]);
        } else {
          target[key] = source[key];
        }
      }
    }
  }]);
  return Util;
}();

var AbstractAgent = function AbstractAgent(win) {
  _classCallCheck(this, AbstractAgent);
  this._win = win;
};

/**
 * Notification agent for modern desktop browsers:
 * Safari 6+, Firefox 22+, Chrome 22+, Opera 25+
 */
var DesktopAgent$$1 =
/*#__PURE__*/
function (_AbstractAgent) {
  _inherits(DesktopAgent$$1, _AbstractAgent);

  function DesktopAgent$$1() {
    _classCallCheck(this, DesktopAgent$$1);
    return _possibleConstructorReturn(this, (DesktopAgent$$1.__proto__ || Object.getPrototypeOf(DesktopAgent$$1)).apply(this, arguments));
  }

  _createClass(DesktopAgent$$1, [{
    key: "isSupported",

    /**
     * Returns a boolean denoting support
     * @returns {Boolean} boolean denoting whether webkit notifications are supported
     */
    value: function isSupported() {
      return this._win.Notification !== undefined;
    }
    /**
     * Creates a new notification
     * @param title - notification title
     * @param options - notification options array
     * @returns {Notification}
     */

  }, {
    key: "create",
    value: function create(title, options) {
      return new this._win.Notification(title, {
        icon: Util.isString(options.icon) || Util.isUndefined(options.icon) || Util.isNull(options.icon) ? options.icon : options.icon.x32,
        body: options.body,
        tag: options.tag,
        requireInteraction: options.requireInteraction
      });
    }
    /**
     * Close a given notification
     * @param notification - notification to close
     */

  }, {
    key: "close",
    value: function close(notification) {
      notification.close();
    }
  }]);
  return DesktopAgent$$1;
}(AbstractAgent);

/**
 * Notification agent for modern desktop browsers:
 * Safari 6+, Firefox 22+, Chrome 22+, Opera 25+
 */
var MobileChromeAgent$$1 =
/*#__PURE__*/
function (_AbstractAgent) {
  _inherits(MobileChromeAgent$$1, _AbstractAgent);

  function MobileChromeAgent$$1() {
    _classCallCheck(this, MobileChromeAgent$$1);
    return _possibleConstructorReturn(this, (MobileChromeAgent$$1.__proto__ || Object.getPrototypeOf(MobileChromeAgent$$1)).apply(this, arguments));
  }

  _createClass(MobileChromeAgent$$1, [{
    key: "isSupported",

    /**
     * Returns a boolean denoting support
     * @returns {Boolean} boolean denoting whether webkit notifications are supported
     */
    value: function isSupported() {
      return this._win.navigator !== undefined && this._win.navigator.serviceWorker !== undefined;
    }
    /**
     * Returns the function body as a string
     * @param func
     */

  }, {
    key: "getFunctionBody",
    value: function getFunctionBody(func) {
      var str = func.toString().match(/function[^{]+{([\s\S]*)}$/);
      return typeof str !== 'undefined' && str !== null && str.length > 1 ? str[1] : null;
    }
    /**
     * Creates a new notification
     * @param id                ID of notification
     * @param title             Title of notification
     * @param options           Options object
     * @param serviceWorker     ServiceWorker path
     * @param callback          Callback function
     */

  }, {
    key: "create",
    value: function create(id, title, options, serviceWorker, callback) {
      var _this = this;

      /* Register ServiceWorker */
      this._win.navigator.serviceWorker.register(serviceWorker);

      this._win.navigator.serviceWorker.ready.then(function (registration) {
        /* Local data the service worker will use */
        var localData = {
          id: id,
          link: options.link,
          origin: document.location.href,
          onClick: Util.isFunction(options.onClick) ? _this.getFunctionBody(options.onClick) : '',
          onClose: Util.isFunction(options.onClose) ? _this.getFunctionBody(options.onClose) : ''
        };
        /* Merge the local data with user-provided data */

        if (options.data !== undefined && options.data !== null) localData = Object.assign(localData, options.data);
        /* Show the notification */

        registration.showNotification(title, {
          icon: options.icon,
          body: options.body,
          vibrate: options.vibrate,
          tag: options.tag,
          data: localData,
          requireInteraction: options.requireInteraction,
          silent: options.silent
        }).then(function () {
          registration.getNotifications().then(function (notifications) {
            /* Send an empty message so the ServiceWorker knows who the client is */
            registration.active.postMessage('');
            /* Trigger callback */

            callback(notifications);
          });
        }).catch(function (error) {
          throw new Error(Messages.errors.sw_notification_error + error.message);
        });
      }).catch(function (error) {
        throw new Error(Messages.errors.sw_registration_error + error.message);
      });
    }
    /**
     * Close all notification
     */

  }, {
    key: "close",
    value: function close() {// Can't do this with service workers
    }
  }]);
  return MobileChromeAgent$$1;
}(AbstractAgent);

/**
 * Notification agent for modern desktop browsers:
 * Safari 6+, Firefox 22+, Chrome 22+, Opera 25+
 */
var MobileFirefoxAgent$$1 =
/*#__PURE__*/
function (_AbstractAgent) {
  _inherits(MobileFirefoxAgent$$1, _AbstractAgent);

  function MobileFirefoxAgent$$1() {
    _classCallCheck(this, MobileFirefoxAgent$$1);
    return _possibleConstructorReturn(this, (MobileFirefoxAgent$$1.__proto__ || Object.getPrototypeOf(MobileFirefoxAgent$$1)).apply(this, arguments));
  }

  _createClass(MobileFirefoxAgent$$1, [{
    key: "isSupported",

    /**
     * Returns a boolean denoting support
     * @returns {Boolean} boolean denoting whether webkit notifications are supported
     */
    value: function isSupported() {
      return this._win.navigator.mozNotification !== undefined;
    }
    /**
     * Creates a new notification
     * @param title - notification title
     * @param options - notification options array
     * @returns {Notification}
     */

  }, {
    key: "create",
    value: function create(title, options) {
      var notification = this._win.navigator.mozNotification.createNotification(title, options.body, options.icon);

      notification.show();
      return notification;
    }
  }]);
  return MobileFirefoxAgent$$1;
}(AbstractAgent);

/**
 * Notification agent for IE9
 */
var MSAgent$$1 =
/*#__PURE__*/
function (_AbstractAgent) {
  _inherits(MSAgent$$1, _AbstractAgent);

  function MSAgent$$1() {
    _classCallCheck(this, MSAgent$$1);
    return _possibleConstructorReturn(this, (MSAgent$$1.__proto__ || Object.getPrototypeOf(MSAgent$$1)).apply(this, arguments));
  }

  _createClass(MSAgent$$1, [{
    key: "isSupported",

    /**
     * Returns a boolean denoting support
     * @returns {Boolean} boolean denoting whether webkit notifications are supported
     */
    value: function isSupported() {
      return this._win.external !== undefined && this._win.external.msIsSiteMode !== undefined;
    }
    /**
     * Creates a new notification
     * @param title - notification title
     * @param options - notification options array
     * @returns {Notification}
     */

  }, {
    key: "create",
    value: function create(title, options) {
      /* Clear any previous notifications */
      this._win.external.msSiteModeClearIconOverlay();

      this._win.external.msSiteModeSetIconOverlay(Util.isString(options.icon) || Util.isUndefined(options.icon) ? options.icon : options.icon.x16, title);

      this._win.external.msSiteModeActivate();

      return null;
    }
    /**
     * Close a given notification
     * @param notification - notification to close
     */

  }, {
    key: "close",
    value: function close() {
      this._win.external.msSiteModeClearIconOverlay();
    }
  }]);
  return MSAgent$$1;
}(AbstractAgent);

/**
 * Notification agent for old Chrome versions (and some) Firefox
 */
var WebKitAgent$$1 =
/*#__PURE__*/
function (_AbstractAgent) {
  _inherits(WebKitAgent$$1, _AbstractAgent);

  function WebKitAgent$$1() {
    _classCallCheck(this, WebKitAgent$$1);
    return _possibleConstructorReturn(this, (WebKitAgent$$1.__proto__ || Object.getPrototypeOf(WebKitAgent$$1)).apply(this, arguments));
  }

  _createClass(WebKitAgent$$1, [{
    key: "isSupported",

    /**
     * Returns a boolean denoting support
     * @returns {Boolean} boolean denoting whether webkit notifications are supported
     */
    value: function isSupported() {
      return this._win.webkitNotifications !== undefined;
    }
    /**
     * Creates a new notification
     * @param title - notification title
     * @param options - notification options array
     * @returns {Notification}
     */

  }, {
    key: "create",
    value: function create(title, options) {
      var notification = this._win.webkitNotifications.createNotification(options.icon, title, options.body);

      notification.show();
      return notification;
    }
    /**
     * Close a given notification
     * @param notification - notification to close
     */

  }, {
    key: "close",
    value: function close(notification) {
      notification.cancel();
    }
  }]);
  return WebKitAgent$$1;
}(AbstractAgent);

var Push$$1 =
/*#__PURE__*/
function () {
  // Private members
  // Public members
  function Push$$1(win) {
    _classCallCheck(this, Push$$1);

    /* Private variables */

    /* ID to use for new notifications */
    this._currentId = 0;
    /* Map of open notifications */

    this._notifications = {};
    /* Window object */

    this._win = win;
    /* Public variables */

    this.Permission = new Permission(win);
    /* Agents */

    this._agents = {
      desktop: new DesktopAgent$$1(win),
      chrome: new MobileChromeAgent$$1(win),
      firefox: new MobileFirefoxAgent$$1(win),
      ms: new MSAgent$$1(win),
      webkit: new WebKitAgent$$1(win)
    };
    this._configuration = {
      serviceWorker: '/serviceWorker.min.js',
      fallback: function fallback(payload) {}
    };
  }
  /**
   * Closes a notification
   * @param id            ID of notification
   * @returns {boolean}   denotes whether the operation was successful
   * @private
   */


  _createClass(Push$$1, [{
    key: "_closeNotification",
    value: function _closeNotification(id) {
      var success = true;
      var notification = this._notifications[id];

      if (notification !== undefined) {
        success = this._removeNotification(id);
        /* Safari 6+, Firefox 22+, Chrome 22+, Opera 25+ */

        if (this._agents.desktop.isSupported()) this._agents.desktop.close(notification);else if (this._agents.webkit.isSupported())
          /* Legacy WebKit browsers */
          this._agents.webkit.close(notification);else if (this._agents.ms.isSupported())
          /* IE9 */
          this._agents.ms.close();else {
          success = false;
          throw new Error(Messages.errors.unknown_interface);
        }
        return success;
      }

      return false;
    }
    /**
    * Adds a notification to the global dictionary of notifications
    * @param {Notification} notification
    * @return {Integer} Dictionary key of the notification
    * @private
    */

  }, {
    key: "_addNotification",
    value: function _addNotification(notification) {
      var id = this._currentId;
      this._notifications[id] = notification;
      this._currentId++;
      return id;
    }
    /**
    * Removes a notification with the given ID
    * @param  {Integer} id - Dictionary key/ID of the notification to remove
    * @return {Boolean} boolean denoting success
    * @private
    */

  }, {
    key: "_removeNotification",
    value: function _removeNotification(id) {
      var success = false;

      if (this._notifications.hasOwnProperty(id)) {
        /* We're successful if we omit the given ID from the new array */
        delete this._notifications[id];
        success = true;
      }

      return success;
    }
    /**
    * Creates the wrapper for a given notification
    *
    * @param {Integer} id - Dictionary key/ID of the notification
    * @param {Map} options - Options used to create the notification
    * @returns {Map} wrapper hashmap object
    * @private
    */

  }, {
    key: "_prepareNotification",
    value: function _prepareNotification(id, options) {
      var _this = this;

      var wrapper;
      /* Wrapper used to get/close notification later on */

      wrapper = {
        get: function get() {
          return _this._notifications[id];
        },
        close: function close() {
          _this._closeNotification(id);
        }
      };
      /* Autoclose timeout */

      if (options.timeout) {
        setTimeout(function () {
          wrapper.close();
        }, options.timeout);
      }

      return wrapper;
    }
    /**
    * Find the most recent notification from a ServiceWorker and add it to the global array
    * @param notifications
    * @private
    */

  }, {
    key: "_serviceWorkerCallback",
    value: function _serviceWorkerCallback(notifications, options, resolve) {
      var _this2 = this;

      var id = this._addNotification(notifications[notifications.length - 1]);
      /* Listen for close requests from the ServiceWorker */


      if (navigator && navigator.serviceWorker) {
        navigator.serviceWorker.addEventListener('message', function (event) {
          var data = JSON.parse(event.data);
          if (data.action === 'close' && Number.isInteger(data.id)) _this2._removeNotification(data.id);
        });
        resolve(this._prepareNotification(id, options));
      }

      resolve(null);
    }
    /**
    * Callback function for the 'create' method
    * @return {void}
    * @private
    */

  }, {
    key: "_createCallback",
    value: function _createCallback(title, options, resolve) {
      var _this3 = this;

      var notification = null;
      var onClose;
      /* Set empty settings if none are specified */

      options = options || {};
      /* onClose event handler */

      onClose = function onClose(id) {
        /* A bit redundant, but covers the cases when close() isn't explicitly called */
        _this3._removeNotification(id);

        if (Util.isFunction(options.onClose)) {
          options.onClose.call(_this3, notification);
        }
      };
      /* Safari 6+, Firefox 22+, Chrome 22+, Opera 25+ */


      if (this._agents.desktop.isSupported()) {
        try {
          /* Create a notification using the API if possible */
          notification = this._agents.desktop.create(title, options);
        } catch (e) {
          var id = this._currentId;
          var sw = this.config().serviceWorker;

          var cb = function cb(notifications) {
            return _this3._serviceWorkerCallback(notifications, options, resolve);
          };
          /* Create a Chrome ServiceWorker notification if it isn't supported */


          if (this._agents.chrome.isSupported()) {
            this._agents.chrome.create(id, title, options, sw, cb);
          }
        }
        /* Legacy WebKit browsers */

      } else if (this._agents.webkit.isSupported()) notification = this._agents.webkit.create(title, options);else if (this._agents.firefox.isSupported())
        /* Firefox Mobile */
        this._agents.firefox.create(title, options);else if (this._agents.ms.isSupported())
        /* IE9 */
        notification = this._agents.ms.create(title, options);else {
        /* Default fallback */
        options.title = title;
        this.config().fallback(options);
      }

      if (notification !== null) {
        var _id = this._addNotification(notification);

        var wrapper = this._prepareNotification(_id, options);
        /* Notification callbacks */


        if (Util.isFunction(options.onShow)) notification.addEventListener('show', options.onShow);
        if (Util.isFunction(options.onError)) notification.addEventListener('error', options.onError);
        if (Util.isFunction(options.onClick)) notification.addEventListener('click', options.onClick);
        notification.addEventListener('close', function () {
          onClose(_id);
        });
        notification.addEventListener('cancel', function () {
          onClose(_id);
        });
        /* Return the wrapper so the user can call close() */

        resolve(wrapper);
      }
      /* By default, pass an empty wrapper */


      resolve(null);
    }
    /**
    * Creates and displays a new notification
    * @param {Array} options
    * @return {Promise}
    */

  }, {
    key: "create",
    value: function create(title, options) {
      var _this4 = this;

      var promiseCallback;
      /* Fail if no or an invalid title is provided */

      if (!Util.isString(title)) {
        throw new Error(Messages.errors.invalid_title);
      }
      /* Request permission if it isn't granted */


      if (!this.Permission.has()) {
        promiseCallback = function promiseCallback(resolve, reject) {
          _this4.Permission.request().then(function () {
            _this4._createCallback(title, options, resolve);
          }).catch(function () {
            reject(Messages.errors.permission_denied);
          });
        };
      } else {
        promiseCallback = function promiseCallback(resolve, reject) {
          try {
            _this4._createCallback(title, options, resolve);
          } catch (e) {
            reject(e);
          }
        };
      }

      return new Promise(promiseCallback);
    }
    /**
    * Returns the notification count
    * @return {Integer} The notification count
    */

  }, {
    key: "count",
    value: function count() {
      var count = 0;
      var key;

      for (key in this._notifications) {
        if (this._notifications.hasOwnProperty(key)) count++;
      }

      return count;
    }
    /**
    * Closes a notification with the given tag
    * @param {String} tag - Tag of the notification to close
    * @return {Boolean} boolean denoting success
    */

  }, {
    key: "close",
    value: function close(tag) {
      var key, notification;

      for (key in this._notifications) {
        if (this._notifications.hasOwnProperty(key)) {
          notification = this._notifications[key];
          /* Run only if the tags match */

          if (notification.tag === tag) {
            /* Call the notification's close() method */
            return this._closeNotification(key);
          }
        }
      }
    }
    /**
    * Clears all notifications
    * @return {Boolean} boolean denoting whether the clear was successful in closing all notifications
    */

  }, {
    key: "clear",
    value: function clear() {
      var key,
          success = true;

      for (key in this._notifications) {
        if (this._notifications.hasOwnProperty(key)) success = success && this._closeNotification(key);
      }

      return success;
    }
    /**
    * Denotes whether Push is supported in the current browser
    * @returns {boolean}
    */

  }, {
    key: "supported",
    value: function supported() {
      var supported = false;

      for (var agent in this._agents) {
        if (this._agents.hasOwnProperty(agent)) supported = supported || this._agents[agent].isSupported();
      }

      return supported;
    }
    /**
    * Modifies settings or returns all settings if no parameter passed
    * @param settings
    */

  }, {
    key: "config",
    value: function config(settings) {
      if (typeof settings !== 'undefined' || settings !== null && Util.isObject(settings)) Util.objectMerge(this._configuration, settings);
      return this._configuration;
    }
    /**
    * Copies the functions from a plugin to the main library
    * @param plugin
    */

  }, {
    key: "extend",
    value: function extend(manifest) {
      var plugin,
          Plugin,
          hasProp = {}.hasOwnProperty;

      if (!hasProp.call(manifest, 'plugin')) {
        throw new Error(Messages.errors.invalid_plugin);
      } else {
        if (hasProp.call(manifest, 'config') && Util.isObject(manifest.config) && manifest.config !== null) {
          this.config(manifest.config);
        }

        Plugin = manifest.plugin;
        plugin = new Plugin(this.config());

        for (var member in plugin) {
          if (hasProp.call(plugin, member) && Util.isFunction(plugin[member])) // $FlowFixMe
            this[member] = plugin[member];
        }
      }
    }
  }]);
  return Push$$1;
}();

var index = new Push$$1(typeof window !== 'undefined' ? window : global);

return index;

})));
//# sourceMappingURL=push.js.map
