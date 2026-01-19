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

      const refreshCalled = await page.evaluate((value) => {
        document.cookie = 'matomo_session_timed_out=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
        window._ajaxSessionTimedOutRefresh = false;
        const originalRefresh = window.piwikHelper.refreshAfter;
        const originalAjax = window.$.ajax;

        window.piwikHelper.refreshAfter = (timeout) => {
          window._ajaxSessionTimedOutRefresh = timeout === 0;
        };

        const mockXhr = {
          status: 401,
          statusText: 'error',
          getResponseHeader: (name) => (name === 'X-Matomo-Session-Timed-Out' ? value : null),
          then() {
            return this;
          },
          fail(callback) {
            this._fail = callback;
            return this;
          },
        };

        window.$.ajax = () => mockXhr;

        const helper = new window.ajaxHelper();
        helper.send();
        if (typeof mockXhr._fail === 'function') {
          mockXhr._fail(mockXhr);
        }

        window.$.ajax = originalAjax;
        window.piwikHelper.refreshAfter = originalRefresh;

        return window._ajaxSessionTimedOutRefresh;
      }, headerValue);

      expect(refreshCalled).to.equal(expectedRefresh);
    });
  });
});
