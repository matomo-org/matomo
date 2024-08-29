/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PluginsAdmin", function () {

  this.fixture = "Piwik\\Plugins\\CorePluginsAdmin\\tests\\Fixtures\\PluginUpdatesFixture"

  var generalParams = 'idSite=1&period=year&date=2024-08-09';

  async function screenshotPageWrap() {
    const pageWrap = await page.$('.pageWrap');
    const screenshot = await pageWrap.screenshot();
    return screenshot;
  }

  it('should load the themes admin page correctly', async function () {
    await page.goto("?" + generalParams + "&module=CorePluginsAdmin&action=themes");

    expect(await screenshotPageWrap()).to.matchImage('themes');
  });

  it('should load the plugins admin page correctly', async function () {
    await page.goto("?" + generalParams + "&module=CorePluginsAdmin&action=plugins");

    expect(await screenshotPageWrap()).to.matchImage('plugins');
  });

  it('should load the plugins admin page correctly when internet disabled', async function () {
    testEnvironment.overrideConfig('General', {
      enable_internet_features: 0
    });
    testEnvironment.save();

    await page.goto("?" + generalParams + "&module=CorePluginsAdmin&action=plugins");

    expect(await screenshotPageWrap()).to.matchImage('plugins_no_internet');
  });

  it('should load the plugins admin page correctly when admin disabled', async function () {
    testEnvironment.overrideConfig('General', {
      enable_plugins_admin: 0
    });
    testEnvironment.save();

    await page.goto("?" + generalParams + "&module=CorePluginsAdmin&action=plugins");

    expect(await screenshotPageWrap()).to.matchImage('plugins_admin_disabled');
  });

});
