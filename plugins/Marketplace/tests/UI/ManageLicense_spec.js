/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for Marketplace.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ManageLicense", function () {
  this.timeout(5*60*1000); // timeout of 5 minutes per test

  this.fixture = "Piwik\\Plugins\\Marketplace\\tests\\Fixtures\\SimpleFixtureTrackFewVisits";

  const urlBase = '?module=Marketplace&action=manageLicenseKey';

  async function captureSelector(screenshotName, selector)
  {
    await page.waitForNetworkIdle();
    expect(await page.screenshotSelector(selector)).to.matchImage(screenshotName);
  }

  it('should show manage license key page', async function () {
    await page.goto(urlBase);
    await page.mouse.move(-10, -10);

    await captureSelector('loaded', '.pageWrap,#notificationContainer');
  });

  it('should not accept invalid license key', async function () {
    await page.type('#license_key', 'invalid_key');
    await page.click('#submit_license_key input');

    await captureSelector('invalid', '.pageWrap,#notificationContainer');
  });

  it('should accept valid license key', async function () {
    await page.goto(urlBase);
    await page.type('#license_key', 'valid');
    await page.waitForTimeout(200);

    testEnvironment.consumer = 'validLicense';
    testEnvironment.mockMarketplaceApiService = 1;
    testEnvironment.save();

    await page.click('#submit_license_key input');

    await captureSelector('valid', '.pageWrap,#notificationContainer');
  });

  it('should show dialog before removing license', async function () {
    await page.click('#remove_license_key input');

    await captureSelector('remove_dialog', '.modal.open');
  });

  it('should remove license on confirm', async function () {
    delete testEnvironment.consumer;
    delete testEnvironment.mockMarketplaceApiService;
    testEnvironment.save();

    const button = await page.jQuery('.modal.open .modal-footer a:contains(Yes)');
    await button.click();

    await captureSelector('removed', '.pageWrap,#notificationContainer');
  });
});
