/*!
 * Matomo - free/libre analytics platform
 *
 * ViewDataTable screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('UserIdVisitorId', function () {

  const liveApiUrl = `${page.baseUrl}index.php?idSite=all&module=API&method=Live.getLastVisitsDetails&format=json`;
  const siteUrl = config.piwikUrl + 'tests/resources/overlay-test-site-real/user-id-visitor-id.php';

  const chromeUserAgent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';
  const safariUserAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A';

  let trackerEventOffset = 0;
  let trackerDate = Date.parse('2024-01-01 10:00:00 UTC');

  after(async function() {
    await page.setUserAgent(page.originalUserAgent);

    delete testEnvironment.configOverride.Tracker;
    testEnvironment.save();
  });

  async function assertCounts(visitsWithActionCount, visitorIdCount) {
    const visits = await fetchLastVisitDetails();

    expect(visits.length).to.be.equal(visitsWithActionCount.length);

    const visitorIds = new Set();

    for (let i = 0; i < visitsWithActionCount.length; i++) {
      expect(visits[i].actionDetails.length).to.be.equal(visitsWithActionCount[i]);

      if (visits[i].visitorId) {
        visitorIds.add(visits[i].visitorId);
      }
    }

    expect(visitorIds.size).to.be.equal(visitorIdCount);
  }

  async function fetchLastVisitDetails() {
    const date = new Date(trackerDate);
    const [dateParam] = date.toISOString().split('T');

    const visits = JSON.parse(await page.downloadUrl(`${liveApiUrl}&period=day&date=${dateParam}`));

    // sort visits by idVisit ascending (API can return different order)
    visits.sort(function (a, b) { return a.idVisit - b.idVisit; });

    return visits;
  }

  async function goToSite({ forceNewVisit, userId } = {}) {
    const appendToUrl = [];

    if (forceNewVisit) {
      appendToUrl.push('forceNewVisit=1');
    }

    if (userId) {
      appendToUrl.push(`userId=${userId}`);
    }

    await page.goto(siteUrl + (appendToUrl.length ? '?' + appendToUrl.join('&') : ''));
    await page.waitForNetworkIdle();
  }

  async function trackAction(url) {
    trackerEventOffset++;

    const visitDateTime = trackerEventOffset + Math.floor(trackerDate / 1000);

    await page.evaluate(function (url, cdt) {
      window.trackAction(url, cdt);
    }, url, visitDateTime);

    await page.waitForNetworkIdle();
  }

  async function trackPageView(url) {
    trackerEventOffset++;

    const visitDateTime = trackerEventOffset + Math.floor(trackerDate / 1000);

    await page.evaluate(function (url, cdt) {
      window.trackPageView(url, cdt);
    }, url, visitDateTime);

    await page.waitForNetworkIdle();
  }

  [true, false].forEach(function (enableUserIdOverwritesVisitorId) {
    describe(`enable_userid_overwrites_visitorid: ${enableUserIdOverwritesVisitorId ? 'enabled' : 'disabled'}`, function () {
      before(function() {
        testEnvironment.configOverride.Tracker = {
          enable_userid_overwrites_visitorid: enableUserIdOverwritesVisitorId ? '1' : '0',
        };
        testEnvironment.save();
      });

      beforeEach(async function () {
        // clear cookies to avoid tests affecting each other
        // and generate new visits where we don't expect
        await page.clearCookies();

        // set our default custom user agent
        await page.setUserAgent(chromeUserAgent);

        // move tracker to the next day
        trackerDate += 86400 * 1000;
        trackerEventOffset = 0;
      });

      it('tracks user that does not log in during visit', async function () {
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await assertCounts([1], 1);

        await goToSite();
        await trackPageView('page-2');
        await assertCounts([2], 1);
        await trackAction('action-1');
        await assertCounts([3], 1);

        await goToSite({ forceNewVisit: true });
        await trackPageView('page-3');
        await assertCounts([3, 1], 1);
        await trackAction('action-2');
        await assertCounts([3, 2], 1);
      });

      it('tracks user that logs in during visit', async function () {
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await assertCounts([1], 1);

        await goToSite();
        await trackPageView('page-2');
        await assertCounts([2], 1);
        await trackAction('action-1');
        await assertCounts([3], 1);

        const visitorId1 = (await fetchLastVisitDetails())[0].visitorId;

        // log in user
        await goToSite({ userId: this.test.title });
        await trackAction('log-in');
        await assertCounts([4], 1);

        const visitorId2 = (await fetchLastVisitDetails())[0].visitorId;

        if (enableUserIdOverwritesVisitorId) {
          expect(visitorId1).to.not.be.equal(visitorId2);
        } else {
          expect(visitorId1).to.be.equal(visitorId2);
        }

        await trackAction('action-2');
        await assertCounts([5], 1);

        await goToSite({ userId: this.test.title });
        await trackPageView('page-3');
        await assertCounts([6], 1);
      });

      it('tracks user that logs in during visit without actions', async function () {
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await assertCounts([1], 1);

        await goToSite();
        await trackPageView('page-2');
        await assertCounts([2], 1);

        const visitorId1 = (await fetchLastVisitDetails())[0].visitorId;

        // log in user
        await goToSite({ userId: this.test.title });
        await trackPageView('page-3');
        await assertCounts([3], 1);

        const visitorId2 = (await fetchLastVisitDetails())[0].visitorId;

        if (enableUserIdOverwritesVisitorId) {
          expect(visitorId1).to.not.be.equal(visitorId2);
        } else {
          expect(visitorId1).to.be.equal(visitorId2);
        }

        await goToSite({ userId: this.test.title });
        await trackPageView('page-4');
        await assertCounts([4], 1);
      });

      it('tracks user that logs in and out during visit', async function () {
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await assertCounts([1], 1);

        await goToSite();
        await trackPageView('page-2');
        await assertCounts([2], 1);
        await trackAction('action-1');
        await assertCounts([3], 1);

        const visitorId1 = (await fetchLastVisitDetails())[0].visitorId;

        // log in user
        await goToSite({ userId: this.test.title });
        await trackAction('log-in');
        await assertCounts([4], 1);

        const visitorId2 = (await fetchLastVisitDetails())[0].visitorId;

        if (enableUserIdOverwritesVisitorId) {
          expect(visitorId1).to.not.be.equal(visitorId2);
        } else {
          expect(visitorId1).to.be.equal(visitorId2);
        }

        await trackAction('action-2');
        await assertCounts([5], 1);

        await goToSite({ userId: this.test.title });
        await trackPageView('page-3');
        await assertCounts([6], 1);

        // log out user
        await goToSite();
        await trackAction('log-out');
        await assertCounts([7], 1);

        const visitorId3 = (await fetchLastVisitDetails())[0].visitorId;

        if (enableUserIdOverwritesVisitorId) {
          expect(visitorId3).to.be.equal(visitorId1);
          expect(visitorId3).to.not.be.equal(visitorId2);
        } else {
          expect(visitorId3).to.be.equal(visitorId1);
          expect(visitorId2).to.be.equal(visitorId3);
        }

        await trackAction('action-3');
        await assertCounts([8], 1);
      });

      it('tracks user that logs in and out during visit without actions', async function () {
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await assertCounts([1], 1);

        await goToSite();
        await trackPageView('page-2');
        await assertCounts([2], 1);

        const visitorId1 = (await fetchLastVisitDetails())[0].visitorId;

        // log in user
        await goToSite({ userId: this.test.title });
        await trackPageView('page-3');
        await assertCounts([3], 1);

        const visitorId2 = (await fetchLastVisitDetails())[0].visitorId;

        if (enableUserIdOverwritesVisitorId) {
          expect(visitorId1).to.not.be.equal(visitorId2);
        } else {
          expect(visitorId1).to.be.equal(visitorId2);
        }

        await goToSite({ userId: this.test.title });
        await trackPageView('page-4');
        await assertCounts([4], 1);

        // log out user
        await goToSite();
        await trackPageView('page-5');
        await assertCounts([5], 1);

        const visitorId3 = (await fetchLastVisitDetails())[0].visitorId;

        if (enableUserIdOverwritesVisitorId) {
          expect(visitorId3).to.be.equal(visitorId1);
          expect(visitorId3).to.not.be.equal(visitorId2);
        } else {
          expect(visitorId1).to.be.equal(visitorId3);
          expect(visitorId2).to.be.equal(visitorId3);
        }

        await trackPageView('page-6');
        await assertCounts([6], 1);
      });

      it('tracks user that logs in on different devices', async function () {
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await goToSite();
        await trackPageView('page-2');
        await trackAction('action-1');
        await goToSite({ userId: this.test.title });
        await trackAction('log-in');
        await assertCounts([4], 1);

        // switch device
        await page.clearCookies();
        await page.setUserAgent(safariUserAgent);

        await goToSite({ forceNewVisit: true });
        await trackPageView('page-3');
        await goToSite();
        await trackPageView('page-4');
        await trackAction('action-2');
        await goToSite({ userId: this.test.title });
        await trackAction('log-in');

        if (enableUserIdOverwritesVisitorId) {
          await assertCounts([5, 3], 2);
        } else {
          await assertCounts([4, 4], 2);
        }

        // expect different fingerprints for different devices
        const visits = await fetchLastVisitDetails();

        expect(visits[0].fingerprint).to.not.be.equal(visits[1].fingerprint);
      });

      it('tracks user that logs in on different devices without actions', async function () {
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await goToSite();
        await trackPageView('page-2');
        await goToSite({ userId: this.test.title });
        await trackPageView('page-3');
        await assertCounts([3], 1);

        // switch device
        await page.clearCookies();
        await page.setUserAgent(safariUserAgent);

        await goToSite({ forceNewVisit: true });
        await trackPageView('page-4');
        await goToSite();
        await trackPageView('page-5');
        await goToSite({ userId: this.test.title });
        await trackPageView('page-6');

        if (enableUserIdOverwritesVisitorId) {
          await assertCounts([4, 2], 2);
        } else {
          await assertCounts([3, 3], 2);
        }

        // expect different fingerprints for different devices
        const visits = await fetchLastVisitDetails();

        expect(visits[0].fingerprint).to.not.be.equal(visits[1].fingerprint);
      });

      it('tracks user that logs in and out multiple times', async function () {
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await goToSite();
        await trackPageView('page-2');
        await trackAction('action-1');
        await goToSite({ userId: this.test.title });
        await trackAction('log-in');
        await assertCounts([4], 1);

        const visitorId1 = (await fetchLastVisitDetails())[0].visitorId;

        await goToSite({ userId: this.test.title });
        await trackAction('action-2');
        await trackPageView('page-3');
        await assertCounts([6], 1);

        await goToSite();
        await trackAction('log-out');
        await assertCounts([7], 1);

        const visitorId2 = (await fetchLastVisitDetails())[0].visitorId;

        // force new visit and log in
        await goToSite({ forceNewVisit: true });
        await trackAction('action-3');
        await trackPageView('page-4');
        await goToSite({ userId: this.test.title });
        await trackAction('log-in');

        if (enableUserIdOverwritesVisitorId) {
          await assertCounts([7, 3], 2);
        } else {
          await assertCounts([7, 3], 1);
        }

        const visitorId3 = (await fetchLastVisitDetails())[1].visitorId;

        await goToSite({ userId: this.test.title });
        await trackAction('action-4');
        await trackPageView('page-5');

        if (enableUserIdOverwritesVisitorId) {
          await assertCounts([7, 5], 2);
        } else {
          await assertCounts([7, 5], 1);
        }

        // await page.clearCookies();
        await goToSite();
        await trackAction('log-out');

        if (enableUserIdOverwritesVisitorId) {
          await assertCounts([8, 5], 2);
        } else {
          await assertCounts([7, 6], 1);
        }

        const visitorId4 = (await fetchLastVisitDetails())[1].visitorId;

        if (enableUserIdOverwritesVisitorId) {
          expect(visitorId1).to.not.be.equal(visitorId2);
          expect(visitorId1).to.be.equal(visitorId3);
          expect(visitorId1).to.be.equal(visitorId4);
        } else {
          expect(visitorId1).to.be.equal(visitorId2);
          expect(visitorId1).to.be.equal(visitorId3);
          expect(visitorId3).to.be.equal(visitorId4);
        }
      });

      it('tracks user that logs in and out multiple times without actions', async function () {
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await goToSite();
        await trackPageView('page-2');
        await goToSite({ userId: this.test.title });
        await trackPageView('log-in');
        await assertCounts([3], 1);

        const visitorId1 = (await fetchLastVisitDetails())[0].visitorId;

        await goToSite({ userId: this.test.title });
        await trackPageView('page-4');
        await assertCounts([4], 1);

        await goToSite();
        await trackPageView('log-out');
        await assertCounts([5], 1);

        const visitorId2 = (await fetchLastVisitDetails())[0].visitorId;

        // force new visit and log in
        await page.clearCookies();
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-6');
        await goToSite({ userId: this.test.title });
        await trackPageView('log-in');

        await assertCounts([5, 2], 2);

        const visitorId3 = (await fetchLastVisitDetails())[1].visitorId;

        await goToSite({ userId: this.test.title });
        await trackPageView('page-7');

        await assertCounts([5, 3], 2);

        await goToSite();
        await trackPageView('log-out');

        await assertCounts([5, 4], 2);

        const visitorId4 = (await fetchLastVisitDetails())[1].visitorId;

        if (enableUserIdOverwritesVisitorId) {
          expect(visitorId1).to.not.be.equal(visitorId2);
          expect(visitorId1).to.be.equal(visitorId3);
          expect(visitorId2).to.be.not.equal(visitorId4);
        } else {
          expect(visitorId1).to.be.equal(visitorId2);
          expect(visitorId1).to.be.not.equal(visitorId3);
          expect(visitorId3).to.be.equal(visitorId4);
        }
      });

      it('tracks user with new visit and login/logout combination', async function () {
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await goToSite();
        await trackPageView('page-2');
        await assertCounts([2], 1);

        await goToSite({ forceNewVisit: true });
        await trackPageView('page-3');
        await assertCounts([2, 1], 1);

        const visitorId1 = (await fetchLastVisitDetails())[1].visitorId;

        await goToSite({ userId: this.test.title });
        await trackPageView('page-4');

        const visitorId2 = (await fetchLastVisitDetails())[1].visitorId;

        if (enableUserIdOverwritesVisitorId) {
          expect(visitorId1).to.be.not.equal(visitorId2);
          await assertCounts([2, 2], 2);
        } else {
          expect(visitorId1).to.be.equal(visitorId2);
          await assertCounts([2, 2], 1);
        }

        await goToSite();
        await trackPageView('page-5');

        if (enableUserIdOverwritesVisitorId) {
          await assertCounts([3, 2], 2);
        } else {
          await assertCounts([2, 3], 1);
        }
      });

      it('tracks new visit due to inactivity', async function () {
        const initialTrackerDate = trackerDate;

        let visits;

        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await goToSite();
        await trackPageView('page-2');
        await trackAction('action-1');
        await goToSite({ userId: this.test.title });
        await trackAction('log-in');
        await assertCounts([4], 1);

        visits = await fetchLastVisitDetails();

        const visitorId1 = visits[0].visitorId;
        const userId1 = visits[0].userId;

        // move tracker date beyond default visit length (6 hours should be enough)
        trackerDate += (6 * 3600) * 1000;

        await goToSite({ userId: this.test.title });
        await trackPageView('page-3');
        await goToSite({ userId: this.test.title });
        await trackPageView('page-4');
        await trackAction('action-5');
        await assertCounts([4, 3], 1);

        visits = await fetchLastVisitDetails();

        const visitorId2 = visits[0].visitorId;
        const userId2 = visits[0].userId;

        expect(visitorId1).to.be.equal(visitorId2);
        expect(userId1).to.be.equal(userId2);

        // move tracker date beyond default visit length (6 hours should be enough)
        trackerDate += (6 * 3600) * 1000;

        await goToSite({ userId: this.test.title });
        await trackPageView('page-5');
        await assertCounts([4, 3, 1], 1);

        // reset tracker date back to initial value for next test
        trackerDate = initialTrackerDate;
      });

      it('tracks new visit at midnight', async function () {
        let visits;

        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1');
        await trackAction('action-1');
        await assertCounts([2], 1);

        visits = await fetchLastVisitDetails();

        const visitId1 = visits[0].idVisit;
        const visitorId1 = visits[0].visitorId;

        // move tracker date to next day
        trackerDate += 86400 * 1000;

        await goToSite();
        await trackPageView('page-2');
        await trackAction('action-2');

        // counting is done for actions of a single day only
        // previous day visit has to be compared separately
        await assertCounts([2], 1);

        visits = await fetchLastVisitDetails();

        const visitId2 = visits[0].idVisit;
        const visitorId2 = visits[0].visitorId;

        expect(visitId1).to.be.not.equal(visitId2);
        expect(visitorId1).to.be.equal(visitorId2);
      });

      it('tracks new visit when campaign changes', async function () {
        await goToSite({ forceNewVisit: true });
        await trackPageView('page-1?utm_campaign=first');

        await goToSite();
        await trackPageView('page-1?utm_campaign=second');

        await assertCounts([1, 1], 1);
      });
    });
  });
});
