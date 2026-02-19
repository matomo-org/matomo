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
      const chunkSizes = [];

      window.piwik.apiBulkRequestLimit = bulkRequestLimit;

      window.$.ajax = (ajaxOptions) => {
        const urls = Array.isArray(ajaxOptions.data.urls) ? ajaxOptions.data.urls : [];
        const chunkOffset = chunkSizes.reduce((sum, chunkSize) => sum + chunkSize, 0);
        chunkSizes.push(urls.length);

        const data = urls.map((url, index) => ({
          url,
          index: chunkOffset + index,
        }));

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

        if (mode === 'instance') {
          const helper = new window.ajaxHelper();
          helper.setBulkRequests(...requests);
          response = await helper.send();
        } else if (mode === 'responseObject') {
          try {
            await window.ajaxHelper.fetch(requests, { returnResponseObject: true });
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
        } else {
          response = await window.ajaxHelper.fetch(requests);
        }

        return {
          chunkSizes,
          errorMessage,
          resultLength: response ? response.length : null,
          firstIndex: response ? response[0].index : null,
          lastIndex: response ? response[response.length - 1].index : null,
        };
      } finally {
        window.$.ajax = originalAjax;
        window.piwik.apiBulkRequestLimit = originalBulkLimit;
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
