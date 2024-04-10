/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for Marketplace.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Marketplace_StartFreeTrial', function () {
  this.fixture = "Piwik\\Plugins\\Marketplace\\tests\\Fixtures\\SimpleFixtureTrackFewVisits";

  const errorModalSelector = '#startFreeTrial .trial-start-error';
  const inProgressModalSelector = '#startFreeTrial .trial-start-in-progress';
  const pluginsUrl = '?module=Marketplace&action=overview';
  const startFreeTrialSelector = '.card-content .cta-container .btn.purchaseable';

  function setEnvironment(startFreeTrialSuccess) {
    testEnvironment.overrideConfig('General', 'enable_plugins_admin', '1');

    testEnvironment.consumer = 'validLicense';
    testEnvironment.mockMarketplaceApiService = 1;
    testEnvironment.startFreeTrialSuccess = startFreeTrialSuccess;
    testEnvironment.save();
  }

  async function goToPluginsPage(){
    await page.goto(pluginsUrl);
    await page.waitForNetworkIdle();

    const cta = await page.$(startFreeTrialSelector, { visible: true });
    const ctaText = await cta.getProperty('textContent');

    expect(ctaText).to.match(/Start Free Trial/i);
  }

  async function screenshotModalSelector(selector, name) {
    // screenshotting the Materialize modal consistently
    // clips wrong and captures nothing,
    // unless the screenshot  is attempted twice
    await page.screenshotSelector(selector);

    expect(await page.screenshotSelector(selector)).to.matchImage(name);
  }

  it ('should display an error if the start trial process fails', async function() {
    setEnvironment(false);

    await goToPluginsPage();

    await page.click(startFreeTrialSelector);
    await page.waitForSelector(inProgressModalSelector, { visible: true });
    await page.waitForSelector(inProgressModalSelector, { hidden: true });
    await page.waitForSelector(errorModalSelector, { visible: true });

    const error = await page.$(`${errorModalSelector} p:first-of-type`);
    const errorMessage = await error.getProperty('textContent');

    expect(errorMessage).to.match(/There was an error starting your free trial/i);

    await screenshotModalSelector(errorModalSelector, 'start_free_trial_error_modal');
  });

  it ('should display a success message if the start trial process succeeds', async function() {
    setEnvironment(true);

    await goToPluginsPage();

    await page.click(startFreeTrialSelector);
    await page.waitForSelector(inProgressModalSelector, { visible: true });
    await page.waitForSelector(inProgressModalSelector, { hidden: true });
    await page.waitForSelector('.notification-success', { visible: true });

    const notification = await page.$('.notification-success');
    const notificationText = await notification.getProperty('textContent');

    expect(notificationText).to.match(/free trial has started .+ PaidPlugin1/i);
  });
});
