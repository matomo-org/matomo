/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("MobileMessaging", function () {
  this.fixture = "Piwik\\Tests\\Fixtures\\EmptySite";

  // required to ensure no provider is set initially
  this.optionsOverride = {
    'persist-fixture-data': false
  };

  async function screenshotPageWrap() {
    const pageWrap = await page.$('.pageWrap');
    const screenshot = await pageWrap.screenshot();
    return screenshot;
  }

  it('should load the Settings > Mobile Messaging admin page correctly', async function () {
    await page.goto("?idSite=1&period=year&date=2022-08-09&module=MobileMessaging&action=index");
    await page.waitForNetworkIdle();

    expect(await screenshotPageWrap()).to.matchImage('admin');
  })

  it('should switch the SMS provider correctly', async function () {
    await page.evaluate(function () {
      $('[name=smsProviders]').val('string:Clockwork').trigger('change');
    });
    await page.waitForTimeout(200);
    await page.waitForNetworkIdle();
    await page.waitForTimeout(200);

    expect(await screenshotPageWrap()).to.matchImage('admin_provider');
  });

  it('should show phone number management when provider was selected', async function () {
    await page.evaluate(function () {
      $('[name=smsProviders]').val('string:StubbedProvider').trigger('change');
    });

    await page.waitForSelector('input#apiKey', {visible: true});
    await page.type('input#apiKey', '0123456789');
    await page.evaluate(() => $('#apiAccountSubmit input').click());

    await page.waitForNetworkIdle();

    expect(await screenshotPageWrap()).to.matchImage('admin_numbers_initial');
  });

  it('should add a phone number for validation', async function () {
    await page.type('input#countryCallingCode', '44');
    await page.type('input#newPhoneNumber', '112233445566');
    await page.click('.addNumber input');

    await page.waitForNetworkIdle();

    expect(await screenshotPageWrap()).to.matchImage('admin_numbers_added');
  });
});
