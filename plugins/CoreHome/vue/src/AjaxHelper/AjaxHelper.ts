/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import PiwikUrl from '../PiwikUrl/PiwikUrl';

window.globalAjaxQueue = [] as GlobalAjaxQueue;
window.globalAjaxQueue.active = 0;

window.globalAjaxQueue.clean = function globalAjaxQueueClean() {
  for (let i = this.length; i >= 0; i -= 1) {
    if (!this[i] || this[i].readyState === 4) {
      this.splice(i, 1);
    }
  }
};

window.globalAjaxQueue.push = function globalAjaxQueuePush(...args: (XMLHttpRequest|null)[]) {
  this.active += args.length;

  // cleanup ajax queue
  this.clean();

  // call original array push
  return Array.prototype.push.call(this, ...args);
};

window.globalAjaxQueue.abort = function globalAjaxQueueAbort() {
  // abort all queued requests if possible
  this.forEach((x) => x && x.abort && x.abort());

  // remove all elements from array
  this.splice(0, this.length);

  this.active = 0;
};

type ParameterValue = string | number | null | undefined | ParameterValue[];
type Parameters = {[name: string]: ParameterValue | Parameters};
type AnyFunction = (...params:any[]) => any; // eslint-disable-line

/**
 * error callback to use by default
 */
function defaultErrorCallback(deferred: XMLHttpRequest, status: string): void {
  // do not display error message if request was aborted
  if (status === 'abort') {
    return;
  }

  const loadingError = $('#loadingError');
  if (Piwik_Popover.isOpen() && deferred && deferred.status === 500) {
    if (deferred && deferred.status === 500) {
      $(document.body).html(piwikHelper.escape(deferred.responseText));
    }
  } else {
    loadingError.show();
  }
}

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
  callback: AnyFunction = null;

  /**
   * Use this.callback if an error is returned
   */
  useRegularCallbackInCaseOfError = false;

  /**
   * Callback function to be executed on error
   */
  errorCallback: AnyFunction;

  withToken = false;

  /**
   * Callback function to be executed on complete (after error or success)
   */
  completeCallback: AnyFunction;

  /**
   * Params to be passed as GET params
   * @see ajaxHelper.mixinDefaultGetParams
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
  getUrl = '?';

  /**
   * Params to be passed as GET params
   * @see ajaxHelper.mixinDefaultPostParams
   */
  postParams: Parameters = {};

  /**
   * Element to be displayed while loading
   */
  loadingElement: HTMLElement|null|JQuery|JQLite|string = null;

  /**
   * Element to be displayed on error
   */
  errorElement: HTMLElement|JQuery|JQLite|string = '#ajaxError';

  /**
   * Handle for current request
   */
  requestHandle: XMLHttpRequest|JQuery.jqXHR|null = null;

  defaultParams = ['idSite', 'period', 'date', 'segment'];

  constructor() {
    this.errorCallback = defaultErrorCallback;
  }

  /**
   * Adds params to the request.
   * If params are given more then once, the latest given value is used for the request
   *
   * @param  params
   * @param  type  type of given parameters (POST or GET)
   * @return {void}
   */
  addParams(params: Parameters|string, type: string): void {
    if (typeof params === 'string') {
      // TODO: add global types for broadcast (multiple uses below)
      params = window['broadcast'].getValuesFromUrl(params); // eslint-disable-line
    }

    const arrayParams = ['compareSegments', 'comparePeriods', 'compareDates'];
    Object.keys(params).forEach((key) => {
      const value = params[key];
      if (arrayParams.indexOf(key) !== -1
        && !value
      ) {
        return;
      }

      if (type.toLowerCase() === 'get') {
        this.getParams[key] = value;
      } else if (type.toLowerCase() === 'post') {
        this.postParams[key] = value;
      }
    });
  }

  withTokenInUrl(): void {
    this.withToken = true;
  }

  /**
   * Sets the base URL to use in the AJAX request.
   */
  setUrl(url: string): void {
    this.addParams(broadcast.getValuesFromUrl(url), 'GET');
  }

  /**
   * Gets this helper instance ready to send a bulk request. Each argument to this
   * function is a single request to use.
   */
  setBulkRequests(...urls: string[]): void {
    const urlsProcessed = urls.map((u) => $.param(u));

    this.addParams({
      module: 'API',
      method: 'API.getBulkRequest',
      urls: urlsProcessed,
      format: 'json',
    }, 'post');
  }

  /**
   * Set a timeout (in milliseconds) for the request. This will override any global timeout.
   *
   * @param timeout  Timeout in milliseconds
   */
  setTimeout(timeout: number): void {
    this.timeout = timeout;
  }

  /**
   * Sets the callback called after the request finishes
   *
   * @param callback  Callback function
   */
  setCallback(callback: AnyFunction): void {
    this.callback = callback;
  }

  /**
   * Set that the callback passed to setCallback() should be used if an application error (i.e. an
   * Exception in PHP) is returned.
   */
  useCallbackInCaseOfError(): void {
    this.useRegularCallbackInCaseOfError = true;
  }

  /**
   * Set callback to redirect on success handler
   * &update=1(+x) will be appended to the current url
   *
   * @param [params] to modify in redirect url
   * @return {void}
   */
  redirectOnSuccess(params: Parameters): void {
    this.setCallback(() => {
      piwikHelper.redirect(params);
    });
  }

  /**
   * Sets the callback called in case of an error within the request
   */
  setErrorCallback(callback: AnyFunction): void {
    this.errorCallback = callback;
  }

  /**
   * Sets the complete callback which is called after an error or success callback.
   */
  setCompleteCallback(callback: AnyFunction): void {
    this.completeCallback = callback;
  }

  /**
   * Sets the response format for the request
   *
   * @param format  response format (e.g. json, html, ...)
   */
  setFormat(format: string): void {
    this.format = format;
  }

  /**
   * Set the div element to show while request is loading
   *
   * @param [element]  selector for the loading element
   */
  setLoadingElement(element: string|HTMLElement|JQuery): void {
    this.loadingElement = element || '#ajaxLoadingDiv';
  }

  /**
   * Set the div element to show on error
   *
   * @param element  selector for the error element
   */
  setErrorElement(element: HTMLElement|JQuery|string): void {
    if (!element) {
      return;
    }
    this.errorElement = element;
  }

  /**
   * Detect whether are allowed to use the given default parameter or not
   */
  private useGETDefaultParameter(parameter: string): boolean {
    if (parameter && this.defaultParams) {
      for (let i = 0; i < this.defaultParams.length; i += 1) {
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
   * @param parameter  A name such as "period", "date", "segment".
   */
  removeDefaultParameter(parameter: string): void {
    if (parameter && this.defaultParams) {
      for (let i = 0; i < this.defaultParams.length; i += 1) {
        if (this.defaultParams[i] === parameter) {
          this.defaultParams.splice(i, 1);
        }
      }
    }
  }

  /**
   * Send the request
   *
   * @deprecated use sendAsync() instead
   */
  send(): void {
    if ($(this.errorElement).length) {
      $(this.errorElement).hide();
    }

    if (this.loadingElement) {
      $(this.loadingElement).fadeIn();
    }

    this.requestHandle = this.buildAjaxCall();
    globalAjaxQueue.push(this.requestHandle);
  }

  sendAsync<T>(): Promise<AjaxHelperResponse<T>> {
    // TODO
  }

  /**
   * Aborts the current request if it is (still) running
   */
  abort(): void {
    if (this.requestHandle && typeof this.requestHandle.abort === 'function') {
      this.requestHandle.abort();
      this.requestHandle = null;
    }
  }

  /**
   * Builds and sends the ajax requests
   */
  private buildAjaxCall(): JQuery.jqXHR {
    const self = this;
    const parameters = this.mixinDefaultGetParams(this.getParams);

    let url = this.getUrl;
    if (url[url.length - 1] !== '?') {
      url += '&';
    }

    // we took care of encoding &segment properly already, so we don't use $.param for it ($.param
    // URL encodes the values)
    if (parameters.segment) {
      url = `${url}segment=${parameters.segment}&`;
      delete parameters.segment;
    }
    if (parameters.date) {
      url = `${url}date=${decodeURIComponent(parameters.date.toString())}&`;
      delete parameters.date;
    }
    url += $.param(parameters);
    const ajaxCall = {
      type: 'POST',
      async: true,
      url,
      dataType: this.format || 'json',
      complete: this.completeCallback,
      error: function errorCallback() {
        globalAjaxQueue.active -= 1;

        if (self.errorCallback) {
          self.errorCallback.apply(this, arguments); // eslint-disable-line
        }
      },
      success: (response, status, request) => {
        if (this.loadingElement) {
          $(this.loadingElement).hide();
        }

        if (response && response.result === 'error' && !this.useRegularCallbackInCaseOfError) {
          let placeAt = null;
          let type = 'toast';
          if ($(this.errorElement).length && response.message) {
            $(this.errorElement).show();
            placeAt = this.errorElement;
            type = null;
          }

          if (response.message) {
            const UI = window['require']('piwik/UI'); // eslint-disable-line
            const notification = new UI.Notification();
            notification.show(response.message, {
              placeat: placeAt,
              context: 'error',
              type,
              id: 'ajaxHelper',
            });
            notification.scrollToNotification();
          }
        } else if (this.callback) {
          this.callback(response, status, request);
        }

        globalAjaxQueue.active -= 1;
        const { piwik } = window;
        if (piwik
          && piwik.ajaxRequestFinished
        ) {
          piwik.ajaxRequestFinished();
        }
      },
      data: this.mixinDefaultPostParams(this.postParams),
      timeout: this.timeout !== null ? this.timeout : undefined,
    };

    return $.ajax(ajaxCall);
  }

  private isRequestToApiMethod() {
    return (this.getParams && this.getParams.module === 'API' && this.getParams.method)
      || (this.postParams && this.postParams.module === 'API' && this.postParams.method);
  }

  isWidgetizedRequest(): boolean {
    return (broadcast.getValueFromUrl('module') === 'Widgetize');
  }

  private getDefaultPostParams() {
    if (this.withToken || this.isRequestToApiMethod() || piwik.shouldPropagateTokenAuth) {
      return {
        token_auth: piwik.token_auth,
        // When viewing a widgetized report there won't be any session that can be used, so don't
        // force session usage
        force_api_session: broadcast.isWidgetizeRequestWithoutSession() ? 0 : 1,
      };
    }

    return {};
  }

  /**
   * Mixin the default parameters to send as POST
   *
   * @param params   parameter object
   */
  private mixinDefaultPostParams(params): Parameters {
    const defaultParams = this.getDefaultPostParams();

    const mergedParams = {
      ...defaultParams,
      ...params,
    };

    return mergedParams;
  }

  /**
   * Mixin the default parameters to send as GET
   *
   * @param   params   parameter object
   */
  private mixinDefaultGetParams(originalParams): Parameters {
    const segment = PiwikUrl.getSearchParam('segment');

    const defaultParams = {
      idSite: piwik.idSite || broadcast.getValueFromUrl('idSite'),
      period: piwik.period || broadcast.getValueFromUrl('period'),
      segment,
    };

    const params = originalParams;

    // never append token_auth to url
    if (params.token_auth) {
      params.token_auth = null;
      delete params.token_auth;
    }

    Object.keys(defaultParams).forEach((key) => {
      if (this.useGETDefaultParameter(key)
        && !params[key]
        && !this.postParams[key]
        && defaultParams[key]
      ) {
        params[key] = defaultParams[key];
      }
    });

    // handle default date & period if not already set
    if (this.useGETDefaultParameter('date') && !params.date && !this.postParams.date) {
      params.date = piwik.currentDateString;
    }

    return params;
  }
}
