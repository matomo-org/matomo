/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import AjaxHelper from './AjaxHelper';

type AjaxMode = 'success' | 'apiErrorEachChunk' | 'abort' | 'sessionTimeout';
type JQueryXhr = JQuery.jqXHR;

describe('CoreHome/AjaxHelper', () => {
  const unsupportedBulkResponseObjectError = 'AjaxHelper returnResponseObject is not supported for bulk requests.';
  const originalAjax = window.$.ajax;
  const originalRequire = window.require;
  const originalRedirect = window.piwikHelper.redirect;
  const originalRefreshAfter = window.piwikHelper.refreshAfter;

  let notificationCallCount = 0;
  let consoleLogSpy: jest.SpyInstance;

  function makeBulkRequests(count: number): QueryParameters[] {
    return Array.from({ length: count }, (_, index) => ({
      method: 'API.getMatomoVersion',
      format: 'json',
      index,
    }));
  }

  function installAjaxMock(mode: AjaxMode, chunkSizes: number[]): void {
    (window.$ as JQueryStatic & { ajax: typeof window.$.ajax }).ajax = ((ajaxOptions: any) => {
      const urls = Array.isArray(ajaxOptions?.data?.urls) ? ajaxOptions.data.urls : [];
      const chunkOffset = chunkSizes.reduce((sum, chunkSize) => sum + chunkSize, 0);
      chunkSizes.push(urls.length);
      const chunkNumber = chunkSizes.length - 1;

      const data = urls.map((url: string, index: number) => {
        if (mode === 'apiErrorEachChunk' && index === 0) {
          return {
            result: 'error',
            message: `chunk-${chunkOffset + index}`,
          };
        }

        return {
          url,
          index: chunkOffset + index,
        };
      });

      const xhr = {
        readyState: 4,
        status: 200,
        statusText: 'success',
        responseJSON: data,
        abort: jest.fn(),
        getResponseHeader(headerName: string) {
          if (mode === 'sessionTimeout' && headerName === 'X-Matomo-Session-Timed-Out') {
            return '1';
          }
          if (headerName === 'X-Test-Header') {
            return `chunk-${chunkNumber}`;
          }
          return null;
        },
        then(callback: (response: unknown, status: string, request: JQueryXhr) => void) {
          if (mode !== 'abort' && mode !== 'sessionTimeout') {
            callback(data, 'success', this as unknown as JQueryXhr);
          }
          return this;
        },
        fail(callback: (request: JQueryXhr, status: string, errorThrown: unknown) => void) {
          if (mode === 'abort') {
            this.status = 0;
            this.statusText = 'abort';
            callback(this as unknown as JQueryXhr, 'abort', new Error('aborted'));
          }
          if (mode === 'sessionTimeout') {
            this.status = 401;
            this.statusText = 'error';
            callback(this as unknown as JQueryXhr, 'error', new Error('session timed out'));
          }
          return this;
        },
      };

      return xhr as unknown as JQueryXhr;
    }) as typeof window.$.ajax;
  }

  beforeEach(() => {
    consoleLogSpy = jest.spyOn(console, 'log').mockImplementation(() => {});
    notificationCallCount = 0;
    document.body.innerHTML = '<div id="ajaxError"></div>';

    (window as Window & { require: (name: string) => any }).require = jest.fn().mockImplementation((moduleName: string) => {
      if (moduleName !== 'piwik/UI') {
        throw new Error(`unexpected module ${moduleName}`);
      }

      return {
        Notification: class Notification {
          show() {
            notificationCallCount += 1;
          }

          scrollToNotification() {
          }
        },
      };
    });

    window.piwikHelper.redirect = jest.fn();
    window.piwikHelper.refreshAfter = jest.fn();
    window.piwik.idSite = 1;
    window.piwik.period = 'day';
    window.piwik.currentDateString = 'yesterday';
    window.piwik.token_auth = 'testtoken';
    window.piwik.shouldPropagateTokenAuth = false;
    window.piwik.apiBulkRequestLimit = 2;
    window.piwik.ajaxRequestFinished = undefined;
  });

  afterEach(() => {
    consoleLogSpy.mockRestore();
    (window.$ as JQueryStatic & { ajax: typeof window.$.ajax }).ajax = originalAjax;
    (window as Window & { require: (name: string) => any }).require = originalRequire;
    window.piwikHelper.redirect = originalRedirect;
    window.piwikHelper.refreshAfter = originalRefreshAfter;
  });

  it('should chunk bulk requests when request count is higher than configured limit', async () => {
    const chunkSizes: number[] = [];
    installAjaxMock('success', chunkSizes);

    const result = await AjaxHelper.fetch<Array<{ index: number }>>(makeBulkRequests(5));

    expect(chunkSizes).toEqual([2, 2, 1]);
    expect(result.length).toBe(5);
    expect(result[0].index).toBe(0);
    expect(result[result.length - 1].index).toBe(4);
  });

  it('should not chunk bulk requests when bulk limit is disabled', async () => {
    window.piwik.apiBulkRequestLimit = -1;
    const chunkSizes: number[] = [];
    installAjaxMock('success', chunkSizes);

    const result = await AjaxHelper.fetch<Array<{ index: number }>>(makeBulkRequests(5));

    expect(chunkSizes).toEqual([5]);
    expect(result.length).toBe(5);
    expect(result[0].index).toBe(0);
    expect(result[result.length - 1].index).toBe(4);
  });

  it('should chunk bulk requests when using the helper instance API', async () => {
    const chunkSizes: number[] = [];
    installAjaxMock('success', chunkSizes);

    const helper = new AjaxHelper<Array<{ index: number }>>();
    helper.setBulkRequests(...makeBulkRequests(5));
    const result = await helper.send();

    expect(chunkSizes).toEqual([2, 2, 1]);
    expect((result as Array<{ index: number }>).length).toBe(5);
    expect((result as Array<{ index: number }>)[0].index).toBe(0);
    expect((result as Array<{ index: number }>)[4].index).toBe(4);
  });

  it('should call complete callback once for chunked helper instance requests', async () => {
    const chunkSizes: number[] = [];
    installAjaxMock('success', chunkSizes);
    let completeCallbackCallCount = 0;
    let completeCallbackStatus = '';
    let completeCallbackHeaderValue: string|null = null;

    const helper = new AjaxHelper<Array<{ index: number }>>();
    helper.setBulkRequests(...makeBulkRequests(5));
    helper.setCompleteCallback((xhr: JQueryXhr, status: string) => {
      completeCallbackCallCount += 1;
      completeCallbackStatus = status;
      completeCallbackHeaderValue = xhr.getResponseHeader('X-Test-Header');
    });

    await helper.send();

    expect(chunkSizes).toEqual([2, 2, 1]);
    expect(completeCallbackCallCount).toBe(1);
    expect(completeCallbackStatus).toBe('success');
    expect(completeCallbackHeaderValue).toBe('chunk-2');
  });

  it('should preserve useCallbackInCaseOfError for chunked helper instance requests', async () => {
    const chunkSizes: number[] = [];
    installAjaxMock('apiErrorEachChunk', chunkSizes);
    let callbackCallCount = 0;
    let callbackResultLength = 0;

    const helper = new AjaxHelper<Array<{ result?: string; message?: string }>>();
    helper.setBulkRequests(...makeBulkRequests(5));
    helper.useCallbackInCaseOfError();
    helper.setCallback((result: Array<{ result?: string; message?: string }>) => {
      callbackCallCount += 1;
      callbackResultLength = Array.isArray(result) ? result.length : 0;
    });

    const result = await helper.send();

    expect(chunkSizes).toEqual([2, 2, 1]);
    expect((result as Array<{ result?: string; message?: string }>).length).toBe(5);
    expect(callbackCallCount).toBe(1);
    expect(callbackResultLength).toBe(5);
  });

  it('should process all chunks and show one notification before rejecting for API errors', async () => {
    const chunkSizes: number[] = [];
    installAjaxMock('apiErrorEachChunk', chunkSizes);

    let error: Error|null = null;
    try {
      await AjaxHelper.fetch(makeBulkRequests(5));
    } catch (caught) {
      error = caught as Error;
    }

    expect(chunkSizes).toEqual([2, 2, 1]);
    expect(error).not.toBeNull();
    expect(error!.message).toContain('chunk-0');
    expect(error!.message).toContain('chunk-2');
    expect(error!.message).toContain('chunk-4');
    expect(notificationCallCount).toBe(1);
  });

  it('should call redirectOnSuccess only once for chunked fetch requests', async () => {
    const chunkSizes: number[] = [];
    installAjaxMock('success', chunkSizes);

    await AjaxHelper.fetch(makeBulkRequests(5), {
      redirectOnSuccess: { update: 1 },
    });

    expect(chunkSizes).toEqual([2, 2, 1]);
    expect(window.piwikHelper.redirect).toHaveBeenCalledTimes(1);
    expect(window.piwikHelper.redirect).toHaveBeenCalledWith({ update: 1 });
  });

  it('should reject returnResponseObject for chunked bulk requests', async () => {
    expect(() => {
      AjaxHelper.fetch(makeBulkRequests(5), { returnResponseObject: true });
    }).toThrow(unsupportedBulkResponseObjectError);
  });

  it('should reject returnResponseObject for chunked helper instance requests', async () => {
    const helper = new AjaxHelper();
    helper.resolveWithHelper = true;
    helper.setBulkRequests(...makeBulkRequests(5));

    expect(() => {
      helper.send();
    }).toThrow(unsupportedBulkResponseObjectError);
  });

  it('should reject returnResponseObject for non-chunked helper instance requests', async () => {
    window.piwik.apiBulkRequestLimit = 10;
    const helper = new AjaxHelper();
    helper.resolveWithHelper = true;
    helper.setBulkRequests(...makeBulkRequests(5));

    expect(() => {
      helper.send();
    }).toThrow(unsupportedBulkResponseObjectError);
  });

  it('should reject chunked fetch requests when aborted', async () => {
    const chunkSizes: number[] = [];
    installAjaxMock('abort', chunkSizes);

    await expect(AjaxHelper.fetch(makeBulkRequests(5))).rejects.toThrow('Chunked bulk request was aborted.');
    expect(chunkSizes).toEqual([2]);
  });

  it('should refresh and reject chunked fetch requests when the session times out', async () => {
    const chunkSizes: number[] = [];
    installAjaxMock('sessionTimeout', chunkSizes);

    await expect(AjaxHelper.fetch(makeBulkRequests(5))).rejects.toThrow('Chunked bulk request timed out due to session expiration.');
    expect(chunkSizes).toEqual([2]);
    expect(window.piwikHelper.refreshAfter).toHaveBeenCalledTimes(1);
    expect(window.piwikHelper.refreshAfter).toHaveBeenCalledWith(0);
  });
});
