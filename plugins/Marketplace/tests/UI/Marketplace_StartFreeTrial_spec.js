/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for Marketplace.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Marketplace_StartFreeTrial', function () {
  this.fixture = "Piwik\\Plugins\\Marketplace\\tests\\Fixtures\\SimpleFixtureTrackFewVisits";

  const parentSuite = this;

  const errorModalSelector = '#startFreeTrial .trial-start-error';
  const inProgressModalSelector = '#startFreeTrial .trial-start-in-progress';
  const noLicenseModalSelector = '#startFreeTrial .trial-start-no-license';
  const pluginsUrl = '?module=Marketplace&action=overview';
  const startFreeTrialSelector = '.card-content .cta-container .btn.purchaseable';

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

  describe('create new account and start free trial', function() {
    this.title = parentSuite.title; // to make sure the screenshot prefix is the same

    function setEnvironment(createAccountResponseCode) {
      testEnvironment.overrideConfig('General', 'enable_plugins_admin', '1');

      testEnvironment.idSitesViewAccess = [];
      testEnvironment.mockMarketplaceApiService = 1;
      testEnvironment.createAccountResponseCode = createAccountResponseCode;
      testEnvironment.startFreeTrialSuccess = true;
      testEnvironment.save();
    }

    async function typeEmail(email) {
      const emailInputSelector = `${noLicenseModalSelector} input[name="email"]`;

      await page.evaluate(function(emailInputSelector) {
        $(emailInputSelector).val('');
      }, (emailInputSelector));

      await page.type(emailInputSelector, email);
    }

    it('should display a modal to create a new account', async function () {
      setEnvironment(null);

      await goToPluginsPage();
      await page.click(startFreeTrialSelector);
      await page.waitForSelector(noLicenseModalSelector, { visible: true });
      await page.waitFor(100); // wait for correct placement

      await screenshotModalSelector(noLicenseModalSelector, 'no_license_modal');
    });

    it('should display an error message if email is rejected by validation', async function () {
      await typeEmail('<this&does;not"validate');

      await page.click(`${noLicenseModalSelector} .btn`);
      await page.waitForSelector(noLicenseModalSelector, { hidden: true });
      await page.waitForSelector(inProgressModalSelector, { visible: true });
      await page.waitForSelector(inProgressModalSelector, { hidden: true });
      await page.waitForSelector(noLicenseModalSelector, { visible: true });

      await screenshotModalSelector(noLicenseModalSelector, 'no_license_modal_invalid');
    });

    it('should display an error message if marketplace rejects the email', async function () {
      setEnvironment(400);

      await typeEmail('reject@example.org');

      await page.click(`${noLicenseModalSelector} .btn`);
      await page.waitForSelector(noLicenseModalSelector, { hidden: true });
      await page.waitForSelector(inProgressModalSelector, { visible: true });
      await page.waitForSelector(inProgressModalSelector, { hidden: true });
      await page.waitForSelector(noLicenseModalSelector, { visible: true });

      await screenshotModalSelector(noLicenseModalSelector, 'no_license_modal_rejected');
    });

    it('should display an error message if marketplace flags email as duplicate', async function () {
      setEnvironment(409);

      await typeEmail('duplicate@example.org');

      await page.click(`${noLicenseModalSelector} .btn`);
      await page.waitForSelector(noLicenseModalSelector, { hidden: true });
      await page.waitForSelector(inProgressModalSelector, { visible: true });
      await page.waitForSelector(inProgressModalSelector, { hidden: true });
      await page.waitForSelector(noLicenseModalSelector, { visible: true });

      await screenshotModalSelector(noLicenseModalSelector, 'no_license_modal_duplicate');
    });

    it('should display a success message if the process succeeds', async function () {
      setEnvironment(200);

      await typeEmail('hello@example.org');

      await page.click(`${noLicenseModalSelector} .btn`);
      await page.waitForSelector(inProgressModalSelector, { visible: true });
      await page.waitForSelector(inProgressModalSelector, { hidden: true });
      await page.waitForSelector('.notification-success', { visible: true });

      const notification = await page.$('.notification-success');
      const notificationText = await notification.getProperty('textContent');

      expect(notificationText).to.match(/added your License key, .* free trial for Paid Plugin 1/i);
    });
  });

  describe('with existing license', function () {
    this.title = parentSuite.title; // to make sure the screenshot prefix is the same

    function setEnvironment(startFreeTrialSuccess) {
      testEnvironment.overrideConfig('General', 'enable_plugins_admin', '1');

      testEnvironment.consumer = 'validLicense';
      testEnvironment.mockMarketplaceApiService = 1;
      testEnvironment.startFreeTrialSuccess = startFreeTrialSuccess;
      testEnvironment.save();
    }

    it('should display an error if the start trial process fails', async function () {
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

    it('should display a success message if the start trial process succeeds', async function () {
      setEnvironment(true);

      await goToPluginsPage();

      await page.click(startFreeTrialSelector);
      await page.waitForSelector(inProgressModalSelector, { visible: true });
      await page.waitForSelector(inProgressModalSelector, { hidden: true });
      await page.waitForSelector('.notification-success', { visible: true });

      const notification = await page.$('.notification-success');
      const notificationText = await notification.getProperty('textContent');

      expect(notificationText).to.match(/free trial has started .+ Paid Plugin 1/i);
    });

    it('should display the Install all paid plugins button in a loading state', async function () {
      setEnvironment(true);

      await goToPluginsPage();

      await page.click(startFreeTrialSelector);
      await page.waitForSelector(inProgressModalSelector, { visible: true });
      await page.waitForSelector(inProgressModalSelector, { hidden: true });
      await page.waitForSelector('.notification-success', { visible: true });

      await page.waitForSelector('.installAllPaidPlugins .matomo-loader', { visible: true });

      expect(await page.screenshotSelector('.marketplace .installAllPaidPlugins button'))
        .to.matchImage('installAllPaidPlugins_loading');
    });

    it('should display the Install all paid plugins button in an active state', async function () {
      setEnvironment(true);

      await goToPluginsPage();

      await page.click(startFreeTrialSelector);
      await page.waitForSelector(inProgressModalSelector, { visible: true });
      await page.waitForSelector(inProgressModalSelector, { hidden: true });
      await page.waitForSelector('.notification-success', { visible: true });

      await page.waitForSelector('.installAllPaidPlugins .matomo-loader', { visible: true });
      await page.waitForNetworkIdle();
      await page.waitForTimeout(250);
      await page.waitForSelector('.installAllPaidPlugins .matomo-loader', { hidden: true });

      expect(await page.screenshotSelector('.marketplace .installAllPaidPlugins button'))
        .to.matchImage('installAllPaidPlugins_active');
    });
  });

  describe('install all paid plugins', function() {
    it('should show a dialog showing a list of all possible plugins to install', async function() {
      setEnvironment(true);

      await goToPluginsPage();
      await page.click('.installAllPaidPlugins button');
      await page.mouse.move(-10, -10);

      await screenshotModalSelector('_install_all_paid_plugins_at_once', '.modal.open');
    });

  })
});
