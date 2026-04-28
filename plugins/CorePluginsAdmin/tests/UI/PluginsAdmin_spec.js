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

  const defaultViewport = { width: 1350, height: 768 };
  const mobileViewport = { width: 480, height: 900 };
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

  it('should keep the plugins table contained on mobile', async function () {
    await page.webpage.setViewport(mobileViewport);

    try {
      await page.goto("?" + generalParams + "&module=CorePluginsAdmin&action=plugins");
      await page.waitForNetworkIdle();

      expect(await page.screenshotSelector('.pageWrap')).to.matchImage('plugins_mobile_table_contained');
    } finally {
      await page.webpage.setViewport(defaultViewport);
    }
  });

  it('should should show plugin update count in the menu', async function () {
    await page.goto("?" + generalParams + "&module=CorePluginsAdmin&action=plugins");

    await page.waitForNetworkIdle();
    await page.waitForTimeout(200);
    const text = await page.$eval(
      '#secondNavBar .navbar .menuTab.active .item.manage-plugins',
      (el) => el.textContent.trim(),
    );

    expect(text).to.contain('2')
  });

  it('should load the plugins admin page correctly when internet disabled', async function () {
    await testEnvironment.overrideConfig('General', {
      enable_internet_features: 0
    });
    await testEnvironment.save();

    await page.goto("?" + generalParams + "&module=CorePluginsAdmin&action=plugins");

    expect(await screenshotPageWrap()).to.matchImage('plugins_no_internet');
  });

  it('should load the plugins admin page correctly when admin disabled', async function () {
    await testEnvironment.overrideConfig('General', {
      enable_plugins_admin: 0
    });
    await testEnvironment.save();

    await page.goto("?" + generalParams + "&module=CorePluginsAdmin&action=plugins");

    expect(await screenshotPageWrap()).to.matchImage('plugins_admin_disabled');
  });

});
