/*!
 * Matomo - free/libre analytics platform
 *
 * A super user decides on the plugin requests of two users on Manage Requests.
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const fs = require('fs');
const path = require('../../../../tests/lib/screenshot-testing/support/path');

describe('Marketplace_ManageRequestsSuperUser', function () {
  this.fixture = "Piwik\\Plugins\\Marketplace\\tests\\Fixtures\\SimpleFixtureTrackFewVisits";

  const manageRequestsUrl = '?module=Marketplace&action=manageRequests&idSite=1&period=day&date=yesterday';
  const requests = [
    ['jane.doe', 'We want to track our videos'],
    ['john.doe', ''],
  ];

  // a free plugin the mocked Marketplace offers for download, from the zip the PluginUpdate spec uploads
  const freePluginName = 'TestPluginUpdate';
  const freePluginPath = path.join(PIWIK_INCLUDE_PATH, '/plugins/', freePluginName);
  const freePluginRequester = 'free.requester';

  function removeFreePlugin() {
    fs.rmSync(freePluginPath, { recursive: true, force: true });
  }

  async function requestAs(login, method, pluginName, message) {
    testEnvironment.fakeIdentity = login;
    testEnvironment.idSitesViewAccess = [1];
    testEnvironment.save();

    await page.goto('?module=CoreHome&action=index&idSite=1&period=day&date=yesterday');
    await page.waitForNetworkIdle();
    await page.evaluate((apiMethod, name, text) => window.CoreHome.AjaxHelper.post(
      { module: 'API', method: apiMethod },
      { pluginName: name, message: text },
    ), method, pluginName, message);

    delete testEnvironment.fakeIdentity;
    delete testEnvironment.idSitesViewAccess;
    testEnvironment.save();
  }

  // the requests and decisions are made today, which the screenshots must not depend on
  async function fixDates() {
    await page.evaluate(() => {
      document.querySelectorAll('.manageRequestsSuperUser__date').forEach((cell) => {
        cell.textContent = '2026-11-05';
      });
    });
  }

  before(async function () {
    removeFreePlugin();

    testEnvironment.overrideConfig('General', 'enable_plugins_admin', '1');

    // a valid license key whose account licenses no plugin, so PaidPlugin1 is trial eligible
    testEnvironment.consumer = 'validLicenseNoPlugins';
    testEnvironment.mockMarketplaceApiService = 1;
    testEnvironment.save();

    for (const [login, message] of requests) {
      await requestAs(login, 'Marketplace.requestTrial', 'PaidPlugin1', message);
    }
  });

  after(async function () {
    removeFreePlugin();
    await testEnvironment.callApi('UsersManager.deleteUser', {
      userLogin: freePluginRequester,
      passwordConfirmation: superUserPassword,
    });

    delete testEnvironment.mockMarketplacePluginUpdate;
    delete testEnvironment.consumer;
    delete testEnvironment.mockMarketplaceApiService;

    // the requests leave PaidPlugin1 requested, which sibling specs sharing this fixture's data would show
    testEnvironment.optionsOverride = testEnvironment.optionsOverride || {};
    testEnvironment.optionsOverride['Marketplace.PluginTrialRequest.PaidPlugin1'] = '[]';
    testEnvironment.save();

    // optionsOverride is applied during proxy bootstrap; trigger one request
    await page.goto('?module=API&method=API.getMatomoVersion&format=json');
  });

  it('should list Manage Requests under Personal for super users', async function () {
    await page.goto('?module=UsersManager&action=userSettings&idSite=1&period=day&date=yesterday');
    await page.waitForNetworkIdle();

    const menu = await page.evaluate(() => document.querySelector('#secondNavBar').textContent);
    expect(menu).to.contain('Manage Requests');
  });

  it('should list the pending requests of each plugin', async function () {
    await page.goto(manageRequestsUrl);
    await page.waitForSelector('.manageRequestsSuperUser__row', { visible: true });

    const tab = await page.evaluate(() => document.querySelector('[data-tab="pending"]').textContent);
    expect(tab).to.equal('Pending (1)');

    await page.click('.manageRequestsSuperUser__toggle');
    await page.waitForSelector('.manageRequestsSuperUser__requesterRow', { visible: true });
    await page.waitForSelector('.manageRequestsSuperUser__requesterReveal:not(.manageRequestsSuperUser__requesterReveal--sliding)');

    const requesters = await page.evaluate(() => Array.from(
      document.querySelectorAll('.manageRequestsSuperUser__requesterRow'),
      (row) => row.textContent,
    ));
    expect(requesters).to.have.length(2);

    await page.evaluate(() => document.activeElement.blur());
    await fixDates();
    expect(await page.screenshotSelector('.manageRequestsSuperUser')).to.matchImage('pending');
  });

  it('should keep the row and show the Marketplace error when the trial does not start', async function () {
    await page.click('.manageRequestsSuperUser__startTrial');
    await page.waitForSelector('#startFreeTrial .trial-start-error', { visible: true });

    const error = await page.evaluate(() => document.querySelector('#startFreeTrial .trial-start-error').textContent);
    expect(error).to.contain('There was an error starting your free trial.');

    await page.click('#startFreeTrial .modal-close');
    await page.waitForSelector('#startFreeTrial', { hidden: true });

    const rows = await page.evaluate(() => document.querySelectorAll('.manageRequestsSuperUser__row').length);
    expect(rows).to.equal(1);
  });

  it('should ask for confirmation and an optional message before declining', async function () {
    await page.click('.manageRequestsSuperUser__decline');
    await page.waitForSelector('.modal.open', { visible: true });
    // the modal is done opening once it has put the focus in the message box
    await page.waitForFunction(() => document.activeElement && document.activeElement.id === 'declineRequestNote');

    const text = await page.evaluate(() => document.querySelector('.modal.open').textContent);
    expect(text).to.contain('Decline request?');
    expect(text).to.contain('2 users requested this plugin.');

    // fixed in place, so captured on its own rather than by its position on the page
    expect(await (await page.$('.modal.open')).screenshot()).to.matchImage('decline_dialog');

    await page.type('.modal.open textarea', 'We have no budget for it this year');
  });

  it('should report how many users were told, and empty the Pending tab', async function () {
    await (await page.jQuery('.modal.open .modal-footer a:contains(Decline request):visible')).click();
    await page.waitForSelector('.modal.open', { hidden: true });
    await page.waitForSelector('.notification-success', { visible: true });

    const notification = await page.evaluate(() => document.querySelector('.notification-success').textContent);
    expect(notification).to.contain('Request declined for 2 users. They\'ll be notified by email where possible.');

    await page.waitForSelector('.manageRequestsSuperUser__empty', { visible: true });
    const tab = await page.evaluate(() => document.querySelector('[data-tab="pending"]').textContent);
    expect(tab).to.equal('Pending (0)');
  });

  it('should list the decision in History', async function () {
    await page.click('[data-tab="history"]');
    await page.waitForSelector('.manageRequestsSuperUser__decision', { visible: true });

    const decision = await page.evaluate(() => document.querySelector('.manageRequestsSuperUser__decision').textContent);
    expect(decision).to.contain('Declined');
    expect(decision).to.contain('We have no budget for it this year');

    await page.evaluate(() => document.activeElement.blur());
    await fixDates();
    expect(await page.screenshotSelector('.manageRequestsSuperUser')).to.matchImage('history');
  });

  it('should install a requested free plugin, approve its request and say who was told', async function () {
    testEnvironment.mockMarketplacePluginUpdate = {
      name: freePluginName,
      version: '1.0.0',
      zip: `plugins/CorePluginsAdmin/tests/resources/${freePluginName}-1.0.0.zip`,
    };
    testEnvironment.save();

    // only requesters with an account are emailed, and so counted
    await testEnvironment.callApi('UsersManager.addUser', {
      userLogin: freePluginRequester,
      password: 'requester-password-1',
      email: 'free.requester@example.com',
      passwordConfirmation: superUserPassword,
    });
    await requestAs(freePluginRequester, 'Marketplace.requestInstall', freePluginName, '');

    await page.goto(manageRequestsUrl);
    const approveAndInstall = `.manageRequestsSuperUser__row[data-plugin="${freePluginName}"] .manageRequestsSuperUser__approveAndInstall`;
    await page.waitForSelector(approveAndInstall, { visible: true });

    await Promise.all([
      page.waitForNavigation(),
      page.click(approveAndInstall),
    ]);

    // installing asks for the password unless it was confirmed in the last 15 minutes
    if (await page.$('#confirm_password_form')) {
      await page.type('#login_form_password', superUserPassword);
      await Promise.all([
        page.waitForNavigation(),
        page.click('#login_form_submit'),
      ]);
    }

    await page.waitForSelector('.notification-success', { visible: true });
    const notification = await page.evaluate(() => document.querySelector('.notification-success').textContent);
    expect(notification).to.contain(`${freePluginName} is now active. 1 user notified by email.`);

    await page.waitForSelector('.manageRequestsSuperUser__empty', { visible: true });

    await page.click('[data-tab="history"]');
    await page.waitForSelector('.manageRequestsSuperUser__decision', { visible: true });
    const decision = await page.evaluate(() => document.querySelector('.manageRequestsSuperUser__decision').textContent);
    expect(decision).to.contain(freePluginName);
    expect(decision).to.contain('Approved');
  });
});
