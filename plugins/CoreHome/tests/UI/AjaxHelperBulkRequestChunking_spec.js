/*!
 * Matomo - free/libre analytics platform
 *
 * AjaxHelper bulk request chunking UI test.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('AjaxHelperBulkRequestChunking', function () {
  this.fixture = "Piwik\\Tests\\Fixtures\\OneVisitorTwoVisits";

  const reportUrl = '?module=CoreHome&action=index&idSite=1&period=day&date=yesterday';
  const unsupportedBulkResponseObjectError = 'AjaxHelper returnResponseObject is not supported for bulk requests.';

  async function loadReportPage() {
    await page.goto(reportUrl);
    await page.waitForNetworkIdle();
    await page.waitForFunction(() => window.ajaxHelper && window.piwik);
  }

  async function runBulkRequestWithLimit(limit, executionMode = 'fetch') {
    return page.evaluate(async (bulkRequestLimit, mode) => {
      const originalAjax = window.$.ajax;
      const originalBulkLimit = window.piwik.apiBulkRequestLimit;
      const originalRedirect = window.piwikHelper.redirect;
      const chunkSizes = [];

      let redirectCallCount = 0;
      const redirectCalls = [];

      window.piwikHelper.redirect = (params) => {
        redirectCallCount += 1;
        redirectCalls.push(params || null);
      };

      window.piwik.apiBulkRequestLimit = bulkRequestLimit;

      window.$.ajax = (ajaxOptions) => {
        const urls = Array.isArray(ajaxOptions.data.urls) ? ajaxOptions.data.urls : [];
        const chunkOffset = chunkSizes.reduce((sum, chunkSize) => sum + chunkSize, 0);
        chunkSizes.push(urls.length);
        const chunkNumber = chunkSizes.length - 1;

        const data = urls.map((url, index) => {
          if (mode === 'instanceUseCallbackInCaseOfError') {
            return {
              result: 'error',
              message: `chunk-${chunkOffset + index}`,
            };
          }

          if (mode === 'fetchChunkErrors' && index === 0) {
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

        return {
          readyState: 4,
          responseJSON: data,
          then(callback) {
            callback(data, 'success', this);
            return this;
          },
          fail() {
            return this;
          },
          abort() {
          },
          getResponseHeader(headerName) {
            if (headerName === 'X-Test-Header') {
              return `chunk-${chunkNumber}`;
            }
            return null;
          },
        };
      };

      try {
        const requests = Array.from({ length: 5 }, (_, index) => ({
          method: 'API.getMatomoVersion',
          format: 'json',
          index,
        }));
        let response = null;
        let errorMessage = null;
        let callbackCallCount = 0;
        let callbackResultLength = null;
        let completeCallbackCallCount = 0;
        let completeCallbackStatus = null;
        let completeCallbackHasHeaderApi = null;
        let completeCallbackHeaderValue = null;

        if (mode === 'instance') {
          const helper = new window.ajaxHelper();
          helper.setBulkRequests(...requests);
          response = await helper.send();
        } else if (mode === 'instanceCompleteCallback') {
          const helper = new window.ajaxHelper();
          helper.setBulkRequests(...requests);
          helper.setCompleteCallback((xhr, status) => { // eslint-disable-line no-unused-vars
            completeCallbackCallCount += 1;
            completeCallbackStatus = status;
            completeCallbackHasHeaderApi = typeof xhr.getResponseHeader === 'function';
            completeCallbackHeaderValue = completeCallbackHasHeaderApi
              ? xhr.getResponseHeader('X-Test-Header')
              : null;
          });
          response = await helper.send();
        } else if (mode === 'instanceUseCallbackInCaseOfError') {
          const helper = new window.ajaxHelper();
          helper.setBulkRequests(...requests);
          helper.useCallbackInCaseOfError();
          helper.setCallback((result) => {
            callbackCallCount += 1;
            callbackResultLength = Array.isArray(result) ? result.length : null;
          });
          try {
            response = await helper.send();
          } catch (error) {
            errorMessage = error && error.message ? error.message : `${error}`;
          }
        } else if (mode === 'responseObject') {
          try {
            await window.ajaxHelper.fetch(requests, { returnResponseObject: true });
          } catch (error) {
            errorMessage = error && error.message ? error.message : `${error}`;
          }
        } else if (mode === 'fetchChunkErrors') {
          try {
            await window.ajaxHelper.fetch(requests);
          } catch (error) {
            errorMessage = error && error.message ? error.message : `${error}`;
          }
        } else if (mode === 'instanceResponseObject') {
          try {
            const helper = new window.ajaxHelper();
            helper.resolveWithHelper = true;
            helper.setBulkRequests(...requests);
            await helper.send();
          } catch (error) {
            errorMessage = error && error.message ? error.message : `${error}`;
          }
        } else if (mode === 'fetchRedirectOnSuccess') {
          response = await window.ajaxHelper.fetch(requests, {
            redirectOnSuccess: { update: 1 },
          });
        } else {
          response = await window.ajaxHelper.fetch(requests);
        }

        return {
          chunkSizes,
          errorMessage,
          resultLength: response ? response.length : null,
          firstIndex: response ? response[0].index : null,
          lastIndex: response ? response[response.length - 1].index : null,
          callbackCallCount,
          callbackResultLength,
          completeCallbackCallCount,
          completeCallbackStatus,
          completeCallbackHasHeaderApi,
          completeCallbackHeaderValue,
          redirectCallCount,
          redirectCalls,
        };
      } finally {
        window.$.ajax = originalAjax;
        window.piwik.apiBulkRequestLimit = originalBulkLimit;
        window.piwikHelper.redirect = originalRedirect;
      }
    }, limit, executionMode);
  }

  it('should chunk bulk requests when request count is higher than configured limit', async function () {
    await loadReportPage();

    const result = await runBulkRequestWithLimit(2);

    expect(result.chunkSizes).to.deep.equal([2, 2, 1]);
    expect(result.resultLength).to.equal(5);
    expect(result.firstIndex).to.equal(0);
    expect(result.lastIndex).to.equal(4);
  });

  it('should not chunk bulk requests when bulk limit is disabled', async function () {
    await loadReportPage();

    const result = await runBulkRequestWithLimit(-1);

    expect(result.chunkSizes).to.deep.equal([5]);
    expect(result.resultLength).to.equal(5);
    expect(result.firstIndex).to.equal(0);
    expect(result.lastIndex).to.equal(4);
  });

  it('should chunk bulk requests when using the helper instance API', async function () {
    await loadReportPage();

    const result = await runBulkRequestWithLimit(2, 'instance');

    expect(result.chunkSizes).to.deep.equal([2, 2, 1]);
    expect(result.resultLength).to.equal(5);
    expect(result.firstIndex).to.equal(0);
    expect(result.lastIndex).to.equal(4);
  });

  it('should call complete callback for chunked helper instance requests', async function () {
    await loadReportPage();

    const result = await runBulkRequestWithLimit(2, 'instanceCompleteCallback');

    expect(result.chunkSizes).to.deep.equal([2, 2, 1]);
    expect(result.completeCallbackCallCount).to.equal(1);
    expect(result.completeCallbackStatus).to.equal('success');
    expect(result.completeCallbackHasHeaderApi).to.equal(true);
    expect(result.completeCallbackHeaderValue).to.equal('chunk-2');
  });

  it('should preserve useCallbackInCaseOfError for chunked helper instance requests', async function () {
    await loadReportPage();

    const result = await runBulkRequestWithLimit(2, 'instanceUseCallbackInCaseOfError');

    expect(result.chunkSizes).to.deep.equal([2, 2, 1]);
    expect(result.errorMessage).to.equal(null);
    expect(result.resultLength).to.equal(5);
    expect(result.callbackCallCount).to.equal(1);
    expect(result.callbackResultLength).to.equal(5);
  });

  it('should process all chunks before rejecting for API errors in chunked fetch requests', async function () {
    await loadReportPage();

    const result = await runBulkRequestWithLimit(2, 'fetchChunkErrors');

    expect(result.chunkSizes).to.deep.equal([2, 2, 1]);
    expect(result.resultLength).to.equal(null);
    expect(result.errorMessage).to.contain('chunk-0');
    expect(result.errorMessage).to.contain('chunk-2');
    expect(result.errorMessage).to.contain('chunk-4');
  });

  it('should call redirectOnSuccess only once for chunked fetch requests', async function () {
    await loadReportPage();

    const result = await runBulkRequestWithLimit(2, 'fetchRedirectOnSuccess');

    expect(result.chunkSizes).to.deep.equal([2, 2, 1]);
    expect(result.resultLength).to.equal(5);
    expect(result.redirectCallCount).to.equal(1);
    expect(result.redirectCalls).to.deep.equal([{ update: 1 }]);
  });

  it('should reject returnResponseObject for chunked bulk requests', async function () {
    await loadReportPage();

    const result = await runBulkRequestWithLimit(2, 'responseObject');

    expect(result.chunkSizes).to.deep.equal([]);
    expect(result.errorMessage).to.equal(unsupportedBulkResponseObjectError);
  });

  it('should reject returnResponseObject for chunked helper instance requests', async function () {
    await loadReportPage();

    const result = await runBulkRequestWithLimit(2, 'instanceResponseObject');

    expect(result.chunkSizes).to.deep.equal([]);
    expect(result.errorMessage).to.equal(unsupportedBulkResponseObjectError);
  });

  it('should reject returnResponseObject for non-chunked helper instance requests', async function () {
    await loadReportPage();

    const result = await runBulkRequestWithLimit(10, 'instanceResponseObject');

    expect(result.chunkSizes).to.deep.equal([]);
    expect(result.errorMessage).to.equal(unsupportedBulkResponseObjectError);
  });
});
