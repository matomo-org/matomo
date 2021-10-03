/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * global ajax queue
 *
 * @type {Array} array holding XhrRequests with automatic cleanup
 */
interface GlobalAjaxQueue extends Array<XMLHttpRequest|null> {
  active:number;

  /**
   * Removes all finished requests from the queue.
   *
   * @return {void}
   */
  clean();

  /**
   * Extend Array.push with automatic cleanup for finished requests
   *
   * @return {Object}
   */
  push();

  /**
   * Extend with abort function to abort all queued requests
   *
   * @return {void}
   */
  abort();
}

declare global {
  interface Window {
    globalAjaxQueue: GlobalAjaxQueue;
  }
}

window.globalAjaxQueue = [] as GlobalAjaxQueue;
window.globalAjaxQueue.active = 0;

window.globalAjaxQueue.clean = function clean() {
  const removed = this.filter(x => !x || x.readyState === 4);
  this.splice(0, this.length);
  this.push(...removed);
};

window.globalAjaxQueue.push = function push(...args: (XMLHttpRequest|null)[]) {
  this.active += args.length;

  // cleanup ajax queue
  this.clean();

  // call original array push
  return Array<XMLHttpRequest|null>.prototype.push.call(this, ...args);
};

window.globalAjaxQueue.abort = function () {
  // abort all queued requests if possible
  this.forEach(x => x && x.abort && x.abort());

  // remove all elements from array
  this.splice(0, this.length);

  this.active = 0;
};

type ParameterValue = string | number | null | undefined | ParameterValue[];
type Parameters = {[name: string]: ParameterValue | Parameters};

/**
 * Global ajax helper to handle requests within piwik
 */
export default class AjaxHelper {
  /**
   * Format of response
   */
  format = 'json';

  /**
   * A timeout for the request which will override any global timeout
   */
  timeout = null;

  /**
   * Callback function to be executed on success
   */
  callback: Function = function () {};

  /**
   * Use this.callback if an error is returned
   */
  useRegularCallbackInCaseOfError = false;

  /**
   * Callback function to be executed on error
   */
  errorCallback: Function;

  withToken = false;

  /**
   * Callback function to be executed on complete (after error or success)
   */
  completeCallback: Function = function () {};

  /**
   * Params to be passed as GET params
   * @see ajaxHelper._mixinDefaultGetParams
   */
  getParams: Parameters = {};

  /**
   * Base URL used in the AJAX request. Can be set by setUrl.
   *
   * It is set to '?' rather than 'index.php?' to increase chances that it works
   * including for users who have an automatic 301 redirection from index.php? to ?
   * POST values are missing when there is such 301 redirection. So by by-passing
   * this 301 redirection, we avoid this issue.
   *
   * @see ajaxHelper.setUrl
   */
  getUrl: string = '?';

  /**
   * Params to be passed as GET params
   * @see ajaxHelper._mixinDefaultPostParams
   */
  postParams: Parameters = {};

  /**
   * Element to be displayed while loading
   */
  loadingElement: HTMLElement|null|JQuery|JQLite = null;

  /**
   * Element to be displayed on error
   */
  errorElement: string = '#ajaxError';

  /**
   * Handle for current request
   */
  requestHandle: XMLHttpRequest|null =  null;

  defaultParams = ['idSite', 'period', 'date', 'segment'];

  /**
   * Adds params to the request.
   * If params are given more then once, the latest given value is used for the request
   *
   * @param {object}  params
   * @param {string}  type  type of given parameters (POST or GET)
   * @return {void}
   */
  addParams(params: Parameters|string, type: string) {
    if (typeof params == 'string') {
      // TODO: add global types for broadcast (multiple uses below)
      params = window['broadcast'].getValuesFromUrl(params); // eslint-disable-line
    }

    const arrayParams = ['compareSegments', 'comparePeriods', 'compareDates'];
    for (let key, value of params) {
      if (arrayParams.indexOf(key) !== -1
        && !value
      ) {
        continue;
      }

      if(type.toLowerCase() == 'get') {
        this.getParams[key] = value;
      } else if(type.toLowerCase() == 'post') {
        this.postParams[key] = value;
      }
    }
  }

  withTokenInUrl() {
    this.withToken = true;
  }

  /**
   * Sets the base URL to use in the AJAX request.
   */
  setUrl(url: string) {
    this.addParams(window['broadcast'].getValuesFromUrl(url), 'GET');
  }

  /**
   * Gets this helper instance ready to send a bulk request. Each argument to this
   * function is a single request to use.
   */
  setBulkRequests(...urls: string[]) {
    const urlsProcessed = urls.map(u => $.param(u));

    this.addParams({
      module: 'API',
      method: 'API.getBulkRequest',
      urls: urlsProcessed,
      format: 'json'
    }, 'post');
  }

  /**
   * Set a timeout (in milliseconds) for the request. This will override any global timeout.
   *
   * @param timeout  Timeout in milliseconds
   */
  setTimeout(timeout: number) {
    this.timeout = timeout;
  }

  /**
   * Sets the callback called after the request finishes
   *
   * @param callback  Callback function
   */
  setCallback(callback) {
    this.callback = callback;
  }

  /**
   * Set that the callback passed to setCallback() should be used if an application error (i.e. an
   * Exception in PHP) is returned.
   */
  useCallbackInCaseOfError() {
    this.useRegularCallbackInCaseOfError = true;
  }

  /**
   * Set callback to redirect on success handler
   * &update=1(+x) will be appended to the current url
   *
   * @param [params] to modify in redirect url
   * @return {void}
   */
  redirectOnSuccess(params: Parameters) {
    this.setCallback(function() {
      // TODO: piwik helper
      window['piwikHelper'].redirect(params); // eslint-disable-line
    });
  };

  /**
   * Sets the callback called in case of an error within the request
   *
   * @param {function} callback  Callback function
   * @return {void}
   */
  this.setErrorCallback = function (callback) {
    this.errorCallback = callback;
  };

  /**
   * Sets the complete callback which is called after an error or success callback.
   *
   * @param {function} callback  Callback function
   * @return {void}
   */
  this.setCompleteCallback = function (callback) {
    this.completeCallback = callback;
  };

  /**
   * error callback to use by default
   *
   * @param deferred
   * @param status
   */
  this.defaultErrorCallback = function(deferred, status)
  {
    // do not display error message if request was aborted
    if(status == 'abort') {
      return;
    }

    var loadingError = $('#loadingError');
    if (Piwik_Popover.isOpen() && deferred && deferred.status === 500) {
      if (deferred && deferred.status === 500) {
        $(document.body).html(piwikHelper.escape(deferred.responseText));
      }
    } else {
      loadingError.show();
    }
  }

  this.errorCallback =  this.defaultErrorCallback;

  /**
   * Sets the response format for the request
   *
   * @param {string} format  response format (e.g. json, html, ...)
   * @return {void}
   */
  this.setFormat = function (format) {
    this.format = format;
  };

  /**
   * Set the div element to show while request is loading
   *
   * @param {String} [element]  selector for the loading element
   */
  this.setLoadingElement = function (element) {
    if (!element) {
      element = '#ajaxLoadingDiv';
    }
    this.loadingElement = element;
  };

  /**
   * Set the div element to show on error
   *
   * @param {String} element  selector for the error element
   */
  this.setErrorElement = function (element) {
    if (!element) {
      return;
    }
    this.errorElement = element;
  };

  /**
   * Detect whether are allowed to use the given default parameter or not
   * @param string parameter
   * @returns {boolean}
   * @private
   */
  this._useGETDefaultParameter = function (parameter) {
    if (parameter && this.defaultParams) {
      var i;
      for (i = 0; i < this.defaultParams.length; i++) {
        if (this.defaultParams[i] === parameter) {
          return true;
        }
      }
    }

    return false;
  }

  /**
   * Removes a default parameter that is usually send automatically along the request.
   *
   * @param {String} parameter  A name such as "period", "date", "segment".
   */
  this.removeDefaultParameter = function (parameter) {
    if (parameter && this.defaultParams) {

      var i;
      for (i = 0; i < this.defaultParams.length; i++) {
        if (this.defaultParams[i] === parameter) {
          this.defaultParams.splice(i, 1);
        }
      }
    }
  }

  /**
   * Send the request
   * @return {void}
   */
  this.send = function () {
    if ($(this.errorElement).length) {
      $(this.errorElement).hide();
    }

    if (this.loadingElement) {
      $(this.loadingElement).fadeIn();
    }

    this.requestHandle = this._buildAjaxCall();
    globalAjaxQueue.push(this.requestHandle);
  };

  /**
   * Aborts the current request if it is (still) running
   * @return {void}
   */
  this.abort = function () {
    if (this.requestHandle && typeof this.requestHandle.abort == 'function') {
      this.requestHandle.abort();
      this.requestHandle = null;
    }
  };

  /**
   * Builds and sends the ajax requests
   * @return {XMLHttpRequest}
   * @private
   */
  this._buildAjaxCall = function () {
    var that = this;

    var parameters = this._mixinDefaultGetParams(this.getParams);

    var url = this.getUrl;
    if (url[url.length - 1] != '?') {
      url += '&';
    }

    // we took care of encoding &segment properly already, so we don't use $.param for it ($.param URL encodes the values)
    if(parameters['segment']) {
      url += 'segment=' + parameters['segment'] + '&';
      delete parameters['segment'];
    }
    if(parameters['date']) {
      url += 'date=' + decodeURIComponent(parameters['date']) + '&';
      delete parameters['date'];
    }
    url += $.param(parameters);
    var ajaxCall = {
      type:     'POST',
      async:    true,
      url:      url,
      dataType: this.format || 'json',
      complete: this.completeCallback,
      error:    function () {
        --globalAjaxQueue.active;

        if (that.errorCallback) {
          that.errorCallback.apply(this, arguments);
        }
      },
      success:  function (response, status, request) {
        if (that.loadingElement) {
          $(that.loadingElement).hide();
        }

        if (response && response.result == 'error' && !that.useRegularCallbackInCaseOfError) {

          var placeAt = null;
          var type    = 'toast';
          if ($(that.errorElement).length && response.message) {
            $(that.errorElement).show();
            placeAt = that.errorElement;
            type    = null;
          }

          if (response.message) {

            var UI = require('piwik/UI');
            var notification = new UI.Notification();
            notification.show(response.message, {
              placeat: placeAt,
              context: 'error',
              type: type,
              id: 'ajaxHelper'
            });
            notification.scrollToNotification();
          }

        } else {
          that.callback(response, status, request);
        }

        --globalAjaxQueue.active;
        var piwik = window.piwik;
        if (piwik
          && piwik.ajaxRequestFinished
        ) {
          piwik.ajaxRequestFinished();
        }
      },
      data:     this._mixinDefaultPostParams(this.postParams)
    };

    if (this.timeout !== null) {
      ajaxCall.timeout = this.timeout;
    }

    return $.ajax(ajaxCall);
  };

  this._isRequestToApiMethod = function () {
    return (this.getParams && this.getParams['module'] === 'API' && this.getParams['method']) ||
      (this.postParams && this.postParams['module'] === 'API' && this.postParams['method']);
  };

  this._isWidgetizedRequest = function () {
    return (broadcast.getValueFromUrl('module') == 'Widgetize');
  };

  this._getDefaultPostParams = function () {
    if (this.withToken || this._isRequestToApiMethod() || piwik.shouldPropagateTokenAuth) {
      return {
        token_auth: piwik.token_auth,
        // When viewing a widgetized report there won't be any session that can be used, so don't force session usage
        force_api_session: broadcast.isWidgetizeRequestWithoutSession() ? 0 : 1
      };
    }

    return {};
  };

  /**
   * Mixin the default parameters to send as POST
   *
   * @param {object}   params   parameter object
   * @return {object}
   * @private
   */
  this._mixinDefaultPostParams = function (params) {

    var defaultParams = this._getDefaultPostParams();

    for (var index in defaultParams) {

      if (!params[index]) {

        params[index] = defaultParams[index];
      }
    }

    return params;
  };

  /**
   * Mixin the default parameters to send as GET
   *
   * @param {object}   params   parameter object
   * @return {object}
   * @private
   */
  this._mixinDefaultGetParams = function (params) {
    var piwikUrl = piwikHelper.getAngularDependency('piwikUrl');

    var segment = piwikUrl.getSearchParam('segment');

    var defaultParams = {
      idSite:  piwik.idSite || broadcast.getValueFromUrl('idSite'),
      period:  piwik.period || broadcast.getValueFromUrl('period'),
      segment: segment
    };

    // never append token_auth to url
    if (params.token_auth) {
      params.token_auth = null;
      delete params.token_auth;
    }

    for (var key in defaultParams) {
      if (this._useGETDefaultParameter(key) && !params[key] && !this.postParams[key] && defaultParams[key]) {
        params[key] = defaultParams[key];
      }
    }

    // handle default date & period if not already set
    if (this._useGETDefaultParameter('date') && !params.date && !this.postParams.date) {
      params.date = piwik.currentDateString;
    }

    return params;
  };
}
