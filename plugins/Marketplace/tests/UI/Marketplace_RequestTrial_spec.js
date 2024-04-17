/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for Marketplace.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Marketplace_RequestTrial', function () {
  this.fixture = "Piwik\\Plugins\\Marketplace\\tests\\Fixtures\\SimpleFixtureTrackFewVisits";

  const pluginsUrl = '?module=Marketplace&action=overview';
  const requestTrialSelector = '.card-content .cta-container .btn.purchaseable';

  before(function () {
    testEnvironment.overrideConfig('General', 'enable_plugins_admin', '1');

    testEnvironment.consumer = 'validLicense';
    testEnvironment.idSitesViewAccess = [1];
    testEnvironment.mockMarketplaceApiService = 1;
    testEnvironment.save();
  });

  it('should display a "request trial" button', async function () {
    await page.goto(pluginsUrl);
    await page.waitForNetworkIdle();

    const cta = await page.$(requestTrialSelector, { visible: true });
    const ctaText = await cta.getProperty('textContent');

    expect(ctaText).to.match(/Request Trial/i);
  });

  it('should require confirmation before the request is sent', async function () {
    await page.click(requestTrialSelector);
    await page.waitForSelector('.modal.open', { visible: true });

    const confirmHeadline = await page.$('.modal.open h2');
    const confirmHeadlineText = await confirmHeadline.getProperty('textContent');

    expect(confirmHeadlineText).to.match(/Request trial .+ PaidPlugin1/i);
  });

  it('should display a success notification after requesting a trial', async function () {
    await (await page.jQuery('.modal.open .modal-footer a:contains(Yes):visible')).click();
    await page.waitForSelector('.modal.open', { hidden: true });
    await page.waitForSelector('.notification-success', { visible: true });

    const notification = await page.$('.notification-success');
    const notificationText = await notification.getProperty('textContent');

    expect(notificationText).to.match(/Trial requested .+ PaidPlugin1/i);
  });
});
