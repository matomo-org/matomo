/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    path = require('../../../../tests/lib/screenshot-testing/support/path');

describe("CustomLogo_Display", function () {
    this.timeout(0);

    before(function () {
        testEnvironment.optionsOverride = {
            branding_use_custom_logo: '1'
        };
        testEnvironment.save();
    });

    afterEach(function () {
        [
            path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.png"),
            path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo-header.png"),
            path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.svg")
        ].forEach(function(file) {
            if (fs.existsSync(file)) {
                fs.unlinkSync(file);
            }
        });
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    var copyLogo = function(svg) {
        fs.copyFileSync(path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/logo.png"), path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.png"));
        fs.copyFileSync(path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/logo-header.png"), path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo-header.png"));

        if (svg) {
            fs.copyFileSync(path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/logo.svg"), path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.svg"));
        }
    };

    [true, false].forEach(function (useSvg) {

        var appendName = useSvg ? '_svg' : '';
        var appendTitle = useSvg ? ' SVG' : '';

        it('should show the custom'+appendTitle+' logo in admin header', async function () {
            copyLogo(useSvg);
            await page.goto("?idSite=1&period=year&date=2012-08-09&module=CoreAdminHome&action=index");

            var navWrap = await page.$('.nav-wrapper');
            expect(await navWrap.screenshot()).to.matchImage('admin'+appendName);
        });

        it('should show the custom'+appendTitle+' logo in login header', async function () {
            testEnvironment.testUseMockAuth = 0;
            testEnvironment.save();

            copyLogo(useSvg);
            await page.goto("");
            var navWrap = await page.$('.nav-wrapper');
            expect(await navWrap.screenshot()).to.matchImage('login'+appendName);
        });

        it('should show the custom'+appendTitle+' logo in unsubscribe email header', async function () {
            copyLogo(useSvg);
            await page.goto("?module=ScheduledReports&action=unsubscribe&token=");
            var navWrap = await page.$('.nav-wrapper');
            expect(await navWrap.screenshot()).to.matchImage('unsubscribe'+appendName);
        });
    });

    // dummy test to ensure custom logo usage is reset
    it('should remove the custom logo usage', async function () {
        testEnvironment.optionsOverride = {
            branding_use_custom_logo: '0'
        };
        testEnvironment.save();
        await page.goto("");
    });
});

describe("CustomLogo_Upload", function () {
    const contentSelector = '.pageWrap';

    const logoToUpload = path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/logo.png");
    const faviconToUpload = path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/favicon.png");

    const logoPublicPath = path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.png");
    const faviconPublicPath = path.join(PIWIK_INCLUDE_PATH, "/misc/user/favicon.png");

    const logoTmpPath = path.join(PIWIK_INCLUDE_PATH, "/tmp/logos/superUserLogin/logo.png");
    const faviconTmpPath = path.join(PIWIK_INCLUDE_PATH, "/tmp/logos/superUserLogin/favicon.png");

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

    it('should upload a custom logo but keep it unpublished until saved', async function() {
        const fileInput = await page.$('input[name=customLogo]');
        await fileInput.uploadFile(logoToUpload);

        await page.waitForTimeout(2000);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(contentSelector)).to.matchImage('custom_logo_uploaded');

        expect(fs.existsSync(logoTmpPath)).to.be.true; // custom file uploaded into tmp folder
        expect(fs.existsSync(logoPublicPath)).to.be.false; // custom file not published as not saved
    });

    it('should upload a custom favicon but keep it unpublished until saved', async function() {
        const fileInput = await page.$('input[name=customFavicon]');
        await fileInput.uploadFile(faviconToUpload);

        await page.waitForTimeout(2000);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(contentSelector)).to.matchImage('custom_favicon_uploaded');

        expect(fs.existsSync(faviconTmpPath)).to.be.true; // custom file uploaded into tmp folder
        expect(fs.existsSync(faviconPublicPath)).to.be.false; // custom file not published as not saved
    });

    it('should save the settings and files should be published', async function() {
        await page.click('.matomo-save-button');
        await page.waitForTimeout(1000);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(contentSelector)).to.matchImage('branding_settings_saved');

        expect(fs.existsSync(logoPublicPath)).to.be.true;
        expect(fs.existsSync(faviconPublicPath)).to.be.true;
    });

    it('should delete local files when disabling the custom logo feature and saving the settings', async function() {
        const cb = await page.waitForSelector('input[name="useCustomLogo"]');
        await cb.click();
        await page.waitForTimeout(200);

        await page.click('.matomo-save-button');
        await page.waitForTimeout(1000);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(contentSelector)).to.matchImage('disable_logo_upload');

        expect(fs.existsSync(logoPublicPath)).to.be.false;
        expect(fs.existsSync(logoTmpPath)).to.be.false;
        expect(fs.existsSync(faviconPublicPath)).to.be.false;
        expect(fs.existsSync(faviconTmpPath)).to.be.false;
      });
});
