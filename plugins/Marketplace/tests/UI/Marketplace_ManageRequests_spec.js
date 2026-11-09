/*!
 * Matomo - free/libre analytics platform
 *
 * A user who is not a super user requests a trial with a message and follows it on Manage Requests.
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Marketplace_ManageRequests', function () {
  this.fixture = "Piwik\\Plugins\\Marketplace\\tests\\Fixtures\\SimpleFixtureTrackFewVisits";

  // the mock's paid plugins sort below the landing view's single row per section
  const pluginsUrl = '?module=Marketplace&action=overview&idSite=1&period=day&date=yesterday#?query='
    + encodeURIComponent('Paid Plugin 1');
  const requestTrialSelector = '.pluginCard__actions .btn.purchaseable';

  before(function () {
    testEnvironment.overrideConfig('General', 'enable_plugins_admin', '1');
    testEnvironment.overrideConfig('FeatureFlags', 'PluginRequests_feature', 'enabled');

    // a valid license key whose account licenses no plugin, so PaidPlugin1 is trial eligible
    testEnvironment.consumer = 'validLicenseNoPlugins';
    testEnvironment.idSitesViewAccess = [1];
    // requests are permanent per login, so a rerun on persisted fixture data starts without one
    testEnvironment.fakeIdentity = 'manageRequestsUser' + Date.now();
    testEnvironment.mockMarketplaceApiService = 1;
    testEnvironment.save();
  });

  after(async function () {
    delete testEnvironment.consumer;
    delete testEnvironment.fakeIdentity;
    delete testEnvironment.idSitesViewAccess;
    delete testEnvironment.mockMarketplaceApiService;
    delete testEnvironment.configOverride.FeatureFlags;

    // the request leaves PaidPlugin1 pending, which sibling specs sharing this fixture's data would show
    testEnvironment.optionsOverride = testEnvironment.optionsOverride || {};
    testEnvironment.optionsOverride['Marketplace.PluginTrialRequest.PaidPlugin1'] = '[]';
    testEnvironment.save();

    // optionsOverride is applied during proxy bootstrap; trigger one request
    await page.goto('?module=API&method=API.getMatomoVersion&format=json');
  });

  it('should list Manage Requests under Personal', async function () {
    await page.goto('?module=UsersManager&action=userSettings&idSite=1&period=day&date=yesterday');
    await page.waitForNetworkIdle();

    const menu = await page.evaluate(() => document.querySelector('#secondNavBar').textContent);
    expect(menu).to.contain('Manage Requests');
  });

  it('should show an empty Manage Requests page before any request', async function () {
    await page.goto('?module=Marketplace&action=manageRequests&idSite=1&period=day&date=yesterday');
    await page.waitForSelector('.manageRequests__empty', { visible: true });

    const text = await page.evaluate(() => document.querySelector('.manageRequests__empty').textContent);
    expect(text).to.contain("You haven't requested any plugins yet.");
  });

  it('should ask for a message when requesting a trial', async function () {
    await page.goto(pluginsUrl);
    await page.waitForSelector(requestTrialSelector, { visible: true });

    await page.click(requestTrialSelector);
    await page.waitForSelector('.modal.open', { visible: true });
    // the modal is done opening once it has put the focus in the message box
    await page.waitForFunction(() => document.activeElement && document.activeElement.id === 'requestPluginMessage');

    const title = await page.evaluate(() => document.querySelector('.modal.open h2').textContent);
    expect(title).to.equal('Request a free trial of Paid Plugin 1?');

    // fixed in place, so captured on its own rather than by its position on the page
    expect(await (await page.$('.modal.open')).screenshot()).to.matchImage('request_dialog');

    await page.type('.modal.open textarea', 'We want to track our videos');
  });

  it('should say where to follow the request once it is sent', async function () {
    await (await page.jQuery('.modal.open .modal-footer a:contains(Send request):visible')).click();
    await page.waitForSelector('.modal.open', { hidden: true });
    await page.waitForSelector('.notification-success', { visible: true });

    const notification = await page.evaluate(() => document.querySelector('.notification-success').textContent);
    expect(notification).to.contain('Request sent.');
    expect(notification).to.contain('Manage Requests');
  });

  it('should show the request on the card with a link to it', async function () {
    await page.waitForSelector('.pluginCard__actions .ctaContainer__viewRequest', { visible: true });

    const actions = await page.evaluate(() => document.querySelector('.pluginCard__actions').textContent);
    expect(actions).to.contain('Trial requested');
    expect(actions).to.contain('View request');
  });

  it('should open Manage Requests from View request and list the request', async function () {
    const link = await page.evaluate(() => {
      const viewRequest = document.querySelector('.pluginCard__actions .ctaContainer__viewRequest');
      return { href: viewRequest.getAttribute('href'), target: viewRequest.getAttribute('target') };
    });
    // in a new tab, so the Marketplace stays where the user left it
    expect(link.target).to.equal('_blank');

    await page.goto(link.href);
    await page.waitForSelector('.manageRequests__row', { visible: true });

    const row = await page.evaluate(() => document.querySelector('.manageRequests__row').textContent);
    expect(row).to.contain('Paid Plugin 1');
    expect(row).to.contain('We want to track our videos');
    expect(row).to.contain('Pending');

    // the request was made today, which the screenshot must not depend on
    await page.evaluate(() => {
      document.querySelector('.manageRequests__requestedOn').textContent = 'Thursday, November 5, 2026';
    });
    expect(await page.screenshotSelector('.manageRequests')).to.matchImage('manage_requests');
  });
});
