/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable max-classes-per-file */

import jqXHR = JQuery.jqXHR;
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import Matomo from '../Matomo/Matomo';

export interface AjaxOptions {
  withTokenInUrl?: boolean;
  postParams?: QueryParameters;
  headers?: Record<string, string>;
  format?: string;
  createErrorNotification?: boolean;
  abortController?: AbortController;
  returnResponseObject?: boolean;
  errorElement?: HTMLElement|JQuery|string;
  redirectOnSuccess?: QueryParameters|boolean;
  abortable?: boolean;
}

interface ErrorResponse {
  result: string;
  message: string;
}

const { $ } = window;

window.globalAjaxQueue = [] as unknown as GlobalAjaxQueue;
window.globalAjaxQueue.active = 0;

window.globalAjaxQueue.clean = function globalAjaxQueueClean() {
  for (let i = this.length; i >= 0; i -= 1) {
    if (!this[i] || this[i]!.readyState === 4) {
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

type AnyFunction = (...params:any[]) => any; // eslint-disable-line

/**
 * error callback to use by default
 */
function defaultErrorCallback(deferred: XMLHttpRequest, status: string): void {
  // do not display error message if request was aborted
  if (status === 'abort' || !deferred || deferred.status === 0) {
    return;
  }

  if (typeof Piwik_Popover === 'undefined') {
    console.log(`Request failed: ${deferred.responseText}`); // mostly for tests
    return;
  }

  if (Piwik_Popover.isOpen() && deferred && deferred.status === 500) {
    $(document.body).html(piwikHelper.escape(deferred.responseText));
  } else {
    $('#loadingError').show();
  }
}

class ApiResponseError extends Error {}

/**
 * Global ajax helper to handle requests within Matomo
 */
export default class AjaxHelper<T = any> { // eslint-disable-line
  /**
   * Format of response
   */
  format = 'json';

  /**
   * A timeout for the request which will override any global timeout
   */
  timeout: number|null = null;

  /**
   * Callback function to be executed on success
   */
  callback: AnyFunction|null = null;

  /**
   * Use this.callback if an error is returned
   */
  useRegularCallbackInCaseOfError = false;

  /**
   * Callback function to be executed on error
   *
   * @deprecated use the jquery promise API
   */
  errorCallback: AnyFunction|null;

  withToken = false;

  /**
   * Callback function to be executed on complete (after error or success)
   *
   * @deprecated use the jquery promise API
   */
  completeCallback?: AnyFunction;

  /**
   * Params to be passed as GET params
   * @see ajaxHelper.mixinDefaultGetParams
   */
  getParams: QueryParameters = {};

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
  postParams: QueryParameters = {};

  /**
   * Element to be displayed while loading
   */
  loadingElement: HTMLElement|null|JQuery|string = null;

  /**
   * Element to be displayed on error
   */
  errorElement: HTMLElement|JQuery|string = '#ajaxError';

  /**
   * Extra headers to add to the request.
   */
  headers?: Record<string, string> = {
    'X-Requested-With': 'XMLHttpRequest',
  };

  /**
   * Handle for current request
   */
  requestHandle: JQuery.jqXHR|null = null;

  abortController: AbortController|null = null;

  abortable = true;

  defaultParams = ['idSite', 'period', 'date', 'segment'];

  resolveWithHelper = false;

  // helper method entry point
  static fetch<R = any>( // eslint-disable-line
    params: QueryParameters|QueryParameters[],
    options: AjaxOptions = {},
  ): Promise<R> {
    const helper = new AjaxHelper<R>();
    if (options.withTokenInUrl) {
      helper.withTokenInUrl();
    }
    if (options.errorElement) {
      helper.setErrorElement(options.errorElement);
    }
    if (options.redirectOnSuccess) {
      helper.redirectOnSuccess(
        options.redirectOnSuccess !== true ? options.redirectOnSuccess : undefined,
      );
    }
    helper.setFormat(options.format || 'json');
    if (Array.isArray(params)) {
      helper.setBulkRequests(...(params as QueryParameters[]));
    } else {
      Object.keys(params).forEach((key) => {
        if (/password/i.test(key)) {
          throw new Error(`Password parameters are not allowed to be sent as GET parameter. Please send ${key} as POST parameter instead.`);
        }
      });

      helper.addParams({
        module: 'API',
        format: options.format || 'json',
        ...params,
        // ajax helper does not encode the segment parameter assuming it is already encoded. this is
        // probably for pre-angularjs code, so we don't want to do this now, but just treat segment
        // as a normal query parameter input (so it will have double encoded values in input params
        // object, then naturally triple encoded in the URL after a $.param call), however we need
        // to support any existing uses of the old code, so instead we do a manual encode here. new
        // code that uses .fetch() will not need to pre-encode the parameter, while old code
        // can pre-encode it.
        segment: params.segment ? encodeURIComponent(params.segment as string) : undefined,
      }, 'get');
    }
    if (options.postParams) {
      helper.addParams(options.postParams, 'post');
    }
    if (options.headers) {
      helper.headers = { ...helper.headers, ...options.headers };
    }

    let createErrorNotification = true;
    if (typeof options.createErrorNotification !== 'undefined'
      && !options.createErrorNotification
    ) {
      helper.useCallbackInCaseOfError();
      helper.setErrorCallback(null);
      createErrorNotification = false;
    }

    if (options.abortController) {
      helper.abortController = options.abortController;
    }

    if (options.returnResponseObject) {
      helper.resolveWithHelper = true;
    }

    if (options.abortable === false) {
      helper.abortable = false;
    }

    return helper.send().then((result: R | ErrorResponse | AjaxHelper) => {
      const data = result instanceof AjaxHelper ? result.requestHandle!.responseJSON : result;

      // check for error if not using default notification behavior
      const results = helper.postParams.method === 'API.getBulkRequest' && Array.isArray(data) ? data : [data];
      const errors = results.filter((r) => r.result === 'error').map((r) => r.message as string);

      if (errors.length) {
        throw new ApiResponseError(errors.filter((e) => e.length).join('\n'));
      }

      return result as R;
    }).catch((xhr: jqXHR) => {
      if (createErrorNotification || xhr instanceof ApiResponseError) {
        throw xhr;
      }

      let message = 'Something went wrong';
      if (xhr.status === 504) {
        message = 'Request was possibly aborted';
      }
      if (xhr.status === 429) {
        message = 'Rate Limit was exceed';
      }
      throw new Error(message);
    });
  }

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  static post<R = any>(
    params: QueryParameters,
    // eslint-disable-next-line
    postParams: any = {},
    options: AjaxOptions = {},
  ): Promise<R> {
    return AjaxHelper.fetch<R>(params, { ...options, postParams });
  }

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  static oneAtATime<R = any>(
    method: string,
    options?: AjaxOptions,
  ): (params: QueryParameters, postParams?: QueryParameters) => Promise<R> {
    let abortController: AbortController|null = null;

    return (params: QueryParameters, postParams?: QueryParameters) => {
      if (abortController) {
        abortController.abort();
      }

      abortController = new AbortController();
      return AjaxHelper.post<R>(
        {
          ...params,
          method,
        },
        postParams,
        {
          ...options,
          abortController,
        },
      ).finally(() => {
        abortController = null;
      });
    };
  }

  constructor() {
    this.errorCallback = defaultErrorCallback;
  }

  /**
   * Adds params to the request.
   * If params are given more then once, the latest given value is used for the request
   *
   * @param  initialParams
   * @param  type  type of given parameters (POST or GET)
   * @return {void}
   */
  addParams(initialParams: QueryParameters|string, type: string): void {
    const params: QueryParameters = typeof initialParams === 'string'
      ? window.broadcast.getValuesFromUrl(initialParams) : initialParams;

    const arrayParams = ['compareSegments', 'comparePeriods', 'compareDates'];
    Object.keys(params).forEach((key) => {
      let value = params[key];
      if (arrayParams.indexOf(key) !== -1
        && !value
      ) {
        return;
      }

      if (typeof value === 'boolean') {
        value = value ? 1 : 0;
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
  setBulkRequests(...urls: Array<string|QueryParameters>): void {
    const urlsProcessed = urls.map((u) => (typeof u === 'string' ? u : $.param(u)));

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
   * @deprecated use the jquery promise API
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
  redirectOnSuccess(params?: QueryParameters): void {
    this.setCallback(() => {
      piwikHelper.redirect(params);
    });
  }

  /**
   * Sets the callback called in case of an error within the request
   *
   * @deprecated use the jquery promise API
   */
  setErrorCallback(callback: AnyFunction|null): void {
    this.errorCallback = callback;
  }

  /**
   * Sets the complete callback which is called after an error or success callback.
   *
   * @deprecated use the jquery promise API
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
   */
  send(): Promise<T | ErrorResponse> {
    if ($(this.errorElement).length) {
      $(this.errorElement).hide();
    }

    if (this.loadingElement) {
      $(this.loadingElement).fadeIn();
    }

    this.requestHandle = this.buildAjaxCall();
    if (this.abortable) {
      window.globalAjaxQueue.push(this.requestHandle);
    }

    if (this.abortController) {
      this.abortController.signal.addEventListener('abort', () => {
        if (this.requestHandle) {
          this.requestHandle.abort();
        }
      });
    }

    const result = new Promise<T | ErrorResponse>((resolve, reject) => {
      this.requestHandle!.then((data: unknown) => {
        if (this.resolveWithHelper) {
          // NOTE: we can't resolve w/ the jquery xhr, because it's a promise, and will
          // just result in following the promise chain back to 'data'
          resolve(this as unknown as (T | ErrorResponse)); // casting hack here
        } else {
          resolve(data as (T | ErrorResponse)); // ignoring textStatus/jqXHR
        }
      }).fail((xhr: jqXHR) => {
        if (xhr.status === 429) {
          console.log(`Warning: the '${$.param(this.getParams)}' request was rate limited!`);
          reject(xhr);
          return;
        }

        if (xhr.statusText === 'abort' || xhr.status === 0) {
          return;
        }

        console.log(`Warning: the ${$.param(this.getParams)} request failed!`);

        reject(xhr);
      });
    });

    return result;
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
      headers: this.headers ? this.headers : undefined,
      error: function errorCallback(...args: any[]) { // eslint-disable-line
        if (self.abortable) {
          window.globalAjaxQueue.active -= 1;
        }

        if (self.errorCallback) {
          self.errorCallback.apply(this, args);
        }
      },
      success: (response: any, status: string, request: jqXHR) => { // eslint-disable-line
        if (this.loadingElement) {
          $(this.loadingElement).hide();
        }

        const results = this.postParams.method === 'API.getBulkRequest' && Array.isArray(response) ? response : [response];
        const errors = results.filter((r) => r.result === 'error')
          .map((r) => r.message as string)
          .filter((e) => e.length)
          // count occurrences of error messages
          .reduce((acc: Record<string, number>, e: string) => {
            acc[e] = (acc[e] || 0) + 1;
            return acc;
          }, {});

        if (errors && Object.keys(errors).length && !this.useRegularCallbackInCaseOfError) {
          let errorMessage = '';
          Object.keys(errors).forEach((error) => {
            if (errorMessage.length) {
              errorMessage += '<br />';
            }
            // append error count if it occured more than once
            if (errors[error] > 1) {
              errorMessage += `${error} (${errors[error]}x)`;
            } else {
              errorMessage += error;
            }
          });
          let placeAt = null;
          let type: string|null = 'toast';
          if ($(this.errorElement).length && errorMessage.length) {
            $(this.errorElement).show();
            placeAt = this.errorElement;
            type = null;
          }

          const isLoggedIn = !document.querySelector('#login_form');
          if (errorMessage && isLoggedIn) {
            const UI = window['require']('piwik/UI'); // eslint-disable-line
            const notification = new UI.Notification();
            notification.show(errorMessage, {
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

        if (self.abortable) {
          window.globalAjaxQueue.active -= 1;
        }
        if (Matomo.ajaxRequestFinished) {
          Matomo.ajaxRequestFinished();
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
    if (this.withToken || this.isRequestToApiMethod() || Matomo.shouldPropagateTokenAuth) {
      return {
        token_auth: Matomo.token_auth,
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
  private mixinDefaultPostParams(params: QueryParameters): QueryParameters {
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
  public mixinDefaultGetParams(originalParams: QueryParameters): QueryParameters {
    const segment = MatomoUrl.getSearchParam('segment');

    const defaultParams: Record<string, string> = {
      idSite: Matomo.idSite ? Matomo.idSite.toString() : broadcast.getValueFromUrl('idSite'),
      period: Matomo.period || broadcast.getValueFromUrl('period'),
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
        && (params[key] === null || typeof params[key] === 'undefined' || params[key] === '')
        && (this.postParams[key] === null
          || typeof this.postParams[key] === 'undefined'
          || this.postParams[key] === '')
        && defaultParams[key]
      ) {
        params[key] = defaultParams[key];
      }
    });

    // handle default date & period if not already set
    if (this.useGETDefaultParameter('date') && !params.date && !this.postParams.date) {
      params.date = Matomo.currentDateString;
    }

    return params;
  }

  getRequestHandle(): jqXHR|null {
    return this.requestHandle;
  }
}
