/*!
 * Matomo - free/libre analytics platform
 *
 * TrackingCodeGenerator screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("TrackingCodeGenerator", function () {
  this.timeout(0);

  var generalParams = 'idSite=1&period=year&date=2023-08-09';

  before(async function () {
    await testEnvironment.callApi("SitesManager.setSiteAliasUrls", {idSite: 7, urls: ['https://another.site.com']});
  });

  after(async function () {
    await testEnvironment.callApi("SitesManager.setSiteAliasUrls", {idSite: 7, urls: []});
  });

  it('should load the Tracking Code admin page correctly', async function () {
    await page.goto("?" + generalParams + "&module=CoreAdminHome&action=trackingCodeGenerator");

    // replace container id in tagmanager code, as it changes when updating omnifixture
    await page.evaluate(function () {
      $('.tagManagerTrackingCode pre').html($('.tagManagerTrackingCode pre').html().replace(/container_[A-z0-9]+\.js/, 'container_REPLACED.js'));
    });

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('initial');
  });

  it('should be possible to show advanced options', async function () {
    await page.click('.advance-option a');

    pageWrap = await page.$('[vue-entry="CoreAdminHome.JsTrackingCodeGenerator"]');
    expect(await pageWrap.screenshot()).to.matchImage('advanced');
  });

  it('should update tracking code when selecting some advanced options', async function () {
    await page.click('#javascript-tracking-all-subdomains');
    await page.click('#javascript-tracking-group-by-domain');
    await page.click('#javascript-tracking-all-aliases');
    await page.click('#javascript-tracking-noscript');
    await page.click('#javascript-tracking-visitor-cv-check');
    await page.click('#javascript-tracking-do-not-track');
    await page.click('#javascript-tracking-disable-cookies');
    await page.click('#custom-campaign-query-params-check');
    await page.click('#require-consent-for-campaign-tracking');

    await page.waitForNetworkIdle();
    await page.waitForTimeout(500); // wait till tracking code was updated

    pageWrap = await page.$('[vue-entry="CoreAdminHome.JsTrackingCodeGenerator"]');
    expect(await pageWrap.screenshot()).to.matchImage('advanced_selected_options');
  });

  it('should include custom variables when added', async function () {
    await (await page.jQuery('.custom-variable-name')).type('cvar1');
    await (await page.jQuery('.custom-variable-value')).type('value1');
    await page.waitForNetworkIdle();
    await page.click('.add-custom-variable');
    await (await page.jQuery('.custom-variable-name:eq(1)')).type('cvar2');
    await (await page.jQuery('.custom-variable-value:eq(1)')).type('Ysงv$美$ф');
    await page.click('.add-custom-variable');
    await page.waitForNetworkIdle();
    await page.waitForTimeout(500); // wait till tracking code was updated

    pageWrap = await page.$('[vue-entry="CoreAdminHome.JsTrackingCodeGenerator"]');
    expect(await pageWrap.screenshot()).to.matchImage('advanced_cvars');
  });

  it('should use custom campaign parameters when provided', async function () {
    await page.type('#custom-campaign-name-query-param', 'customname');
    await page.type('#custom-campaign-keyword-query-param', 'customkeyword');
    await page.waitForNetworkIdle();
    await page.waitForTimeout(500); // wait till tracking code was updated

    pageWrap = await page.$('[vue-entry="CoreAdminHome.JsTrackingCodeGenerator"]');
    expect(await pageWrap.screenshot()).to.matchImage('advanced_campaign');
  });

  it('should allow selecting cross domain when multiple site urls configured', async function () {
    await page.goto("?idSite=7&period=year&date=2023-08-09&module=CoreAdminHome&action=trackingCodeGenerator");
    await page.click('.advance-option a');

    pageWrap = await page.$('[vue-entry="CoreAdminHome.JsTrackingCodeGenerator"]');
    expect(await pageWrap.screenshot()).to.matchImage('cross_domain');
  });

  it('selecting cross domain will also check outlink option', async function () {
    await page.click('#javascript-tracking-cross-domain');
    await page.waitForNetworkIdle();
    await page.waitForTimeout(500); // wait till tracking code was updated

    pageWrap = await page.$('[vue-entry="CoreAdminHome.JsTrackingCodeGenerator"]');
    expect(await pageWrap.screenshot()).to.matchImage('cross_domain_checked');
  });

});
