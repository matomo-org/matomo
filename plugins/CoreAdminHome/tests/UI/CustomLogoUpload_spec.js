/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    path = require('../../../../tests/lib/screenshot-testing/support/path');

describe("CustomLogoUpload", function () {
    const contentSelector = '.pageWrap';

    const logoToUpload = path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/logo.png");
    const faviconToUpload = path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/favicon.png");

    const logoPublicPath = path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.png");
    const faviconPublicPath = path.join(PIWIK_INCLUDE_PATH, "/misc/user/favicon.png");

    // ba16a2cfb817c43df28fde559b8ee4774f422602 is sha1 of superUserLogin login
    const logoTmpPath = path.join(PIWIK_INCLUDE_PATH, "/tmp/logos/ba16a2cfb817c43df28fde559b8ee4774f422602/logo.png");
    const faviconTmpPath = path.join(PIWIK_INCLUDE_PATH, "/tmp/logos/ba16a2cfb817c43df28fde559b8ee4774f422602/favicon.png");

    this.timeout(0);

    before(function () {
        testEnvironment.optionsOverride = {
            branding_use_custom_logo: '1'
        };
        testEnvironment.save();
    });

    it('should enable logo upload', async function() {
        await page.goto('?module=CoreAdminHome&action=generalSettings');
        await page.waitForNetworkIdle();

        await page.evaluate(function(){
            $('input[name="useCustomLogo"]').trigger('change');
        });
        await page.waitForTimeout(200);

        expect(await page.screenshotSelector(contentSelector)).to.matchImage('enable_logo_upload');
    });

    it('should upload a custom logo', async function() {
        const fileInput = await page.$('input[name=customLogo]');
        await fileInput.uploadFile(logoToUpload);

        await page.waitForTimeout(2000);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(contentSelector)).to.matchImage('custom_logo_uploaded');

        expect(fs.existsSync(logoTmpPath)).to.be.true; // custom file uploaded into tmp folder
        expect(fs.existsSync(logoPublicPath)).to.be.false; // custom file not published as not saved
    });

    it('should publish custom logo when settings are saved', async function() {
        await page.click('[vue-entry="CoreAdminHome.BrandingSettings"] .matomo-save-button');
        await page.waitForTimeout(1000);
        await page.waitForNetworkIdle();

        expect(fs.existsSync(logoPublicPath)).to.be.true;
        expect(fs.existsSync(logoTmpPath)).to.be.false;
        expect(fs.existsSync(faviconPublicPath)).to.be.false;
        expect(fs.existsSync(faviconTmpPath)).to.be.false;
    });

    it('should upload a custom favicon without a page reload', async function() {
        const fileInput = await page.$('input[name=customFavicon]');
        await fileInput.uploadFile(faviconToUpload);

        await page.waitForTimeout(2000);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(contentSelector)).to.matchImage('custom_favicon_uploaded');

        expect(fs.existsSync(faviconTmpPath)).to.be.true; // custom file uploaded into tmp folder
        expect(fs.existsSync(faviconPublicPath)).to.be.false;
    });

    it('should publish custom favicon and not alter custom logo when settings are saved again', async function() {
        await page.click('[vue-entry="CoreAdminHome.BrandingSettings"] .matomo-save-button');
        await page.waitForTimeout(1000);
        await page.waitForNetworkIdle();

        expect(fs.existsSync(logoPublicPath)).to.be.true;
        expect(fs.existsSync(logoTmpPath)).to.be.false;
        expect(fs.existsSync(faviconPublicPath)).to.be.true;
        expect(fs.existsSync(faviconTmpPath)).to.be.false;
    });

    it('should display custom logo and favicon when settings page is reloaded', async function() {
        await page.reload();
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(contentSelector)).to.matchImage('branding_settings_reloaded_first');
    });

    it('should not change anything when settings are saved with custom logos present', async function() {
        await page.click('[vue-entry="CoreAdminHome.BrandingSettings"] .matomo-save-button');
        await page.waitForTimeout(1000);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(contentSelector)).to.matchImage('branding_settings_reloaded_second');

        expect(fs.existsSync(logoPublicPath)).to.be.true;
        expect(fs.existsSync(faviconPublicPath)).to.be.true;
        expect(fs.existsSync(logoTmpPath)).to.be.false;
        expect(fs.existsSync(faviconTmpPath)).to.be.false;
    });

    it('should disable custom logo feature and save settings', async function() {
        const cb = await page.waitForSelector('input[name="useCustomLogo"]');
        await cb.click();
        await page.waitForTimeout(200);

        await page.click('[vue-entry="CoreAdminHome.BrandingSettings"] .matomo-save-button');
        await page.waitForTimeout(1000);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(contentSelector)).to.matchImage('disable_logo_upload');

        expect(fs.existsSync(logoPublicPath)).to.be.false;
        expect(fs.existsSync(logoTmpPath)).to.be.false;
        expect(fs.existsSync(faviconPublicPath)).to.be.false;
        expect(fs.existsSync(faviconTmpPath)).to.be.false;
    });
});
