/*!
 * Matomo - free/libre analytics platform
 *
 * AjaxHelper session timeout UI test.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('AjaxHelperSessionTimeout', function () {
  this.fixture = "Piwik\\Tests\\Fixtures\\OneVisitorTwoVisits";

  const reportUrl = '?module=CoreHome&action=index&idSite=1&period=day&date=yesterday';

  async function loadReportPage() {
    await page.goto(reportUrl);
    await page.waitForNetworkIdle();
    await page.waitForFunction(() => window.ajaxHelper && window.piwikHelper);
  }

  async function runRefreshCheck(options) {
    return page.evaluate((opts) => {
      document.cookie = 'matomo_session_timed_out=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
      window._ajaxSessionTimedOutRefresh = false;
      const originalRefresh = window.piwikHelper.refreshAfter;
      const originalAjax = window.$.ajax;

      window.piwikHelper.refreshAfter = (timeout) => {
        window._ajaxSessionTimedOutRefresh = timeout === 0;
      };

      const mockXhr = {
        status: opts.status,
        statusText: opts.statusText,
        getResponseHeader: (name) => (name === 'X-Matomo-Session-Timed-Out' ? opts.headerValue : null),
        then(callback) {
          this._then = callback;
          return this;
        },
        fail(callback) {
          this._fail = callback;
          return this;
        },
      };

      window.$.ajax = () => mockXhr;

      const helper = new window.ajaxHelper();
      helper.resolveWithHelper = !!opts.resolveWithHelper;
      helper.send();
      if (opts.trigger === 'then' && typeof mockXhr._then === 'function') {
        mockXhr._then({}, 'success', mockXhr);
      }
      if (opts.trigger === 'fail' && typeof mockXhr._fail === 'function') {
        mockXhr._fail(mockXhr);
      }

      window.$.ajax = originalAjax;
      window.piwikHelper.refreshAfter = originalRefresh;

      return window._ajaxSessionTimedOutRefresh;
    }, options);
  }

  const cases = [
    {
      name: 'should refresh when a request indicates the session has timed out',
      headerValue: '1',
      expectedRefresh: true,
    },
    {
      name: 'should not refresh when the session timeout header is missing',
      headerValue: null,
      expectedRefresh: false,
    },
  ];

  cases.forEach(({ name, headerValue, expectedRefresh }) => {
    it(name, async function () {
      await loadReportPage();

      const refreshCalled = await runRefreshCheck({
        headerValue,
        status: 401,
        statusText: 'error',
        trigger: 'fail',
        resolveWithHelper: false,
      });

      expect(refreshCalled).to.equal(expectedRefresh);
    });
  });

  it('should refresh when a successful request returns the session timeout header', async function () {
    await loadReportPage();

    const refreshCalled = await runRefreshCheck({
      headerValue: '1',
      status: 200,
      statusText: 'success',
      trigger: 'then',
      resolveWithHelper: true,
    });

    expect(refreshCalled).to.equal(true);
  });

  it('should not refresh when a successful request is missing the session timeout header', async function () {
    await loadReportPage();

    const refreshCalled = await runRefreshCheck({
      headerValue: null,
      status: 200,
      statusText: 'success',
      trigger: 'then',
      resolveWithHelper: true,
    });

    expect(refreshCalled).to.equal(false);
  });
});
