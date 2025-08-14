/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PrivacyManager_SiteSpecific", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\PrivacyManager\\tests\\Fixtures\\MultipleSitesMultipleVisitsFixture";

    var generalParams = 'idSite=1&period=day&date=2017-01-02',
        urlBase = '?module=SitesManager&' + generalParams + '&action=';

    before(function () {
        testEnvironment.pluginsToLoad = ['PrivacyManager'];
        testEnvironment.save();
    });

    async function loadBasePage()
    {
        await page.goto(urlBase);
        await page.waitForNetworkIdle();
    }

    async function typeUserPassword()
    {
        var elem = await page.jQuery('.modal.open #currentUserPassword');
        await elem.type(superUserPassword);
        await page.waitForTimeout(100);
    }

    async function capturePage(screenshotName) {
        await page.waitForNetworkIdle();
        const pageWrap = await page.$('.pageWrap,#notificationContainer,.modal.open');
        const screenshot = await pageWrap.screenshot();
        expect(screenshot).to.matchImage(screenshotName);
    }

    function sitePrefix(idSite, str) {
        return `div[idsite="${idSite}"] ${str}`;
    }

    function openSitePrivacySettingsSelector(idSite) {
        return sitePrefix(idSite, 'button[title="Manage privacy settings"]');
    }

    function cancelSitePrivacySettingsButton(idSite) {
        return sitePrefix(idSite, '.footer-buttons button.cancel');
    }

    function saveSitePrivacySettingsButton(idSite) {
        return sitePrefix(idSite, '.footer-buttons input[value="Save"]');
    }

    it('should show privacy settings for multiple sites at the same time on lock icon click', async function() {
        await loadBasePage();
        await page.click(openSitePrivacySettingsSelector(1));
        await page.waitForTimeout(200);
        await page.waitForNetworkIdle();

        await page.click(openSitePrivacySettingsSelector(3));
        await page.waitForTimeout(200);
        await page.waitForNetworkIdle();

        await capturePage('show_settings');
    });

    it('should close privacy settings for a given site', async function() {
        await page.click(cancelSitePrivacySettingsButton(3));
        await page.waitForTimeout(200);

        await capturePage('close_one_site_settings');
    });

    it('should show site-specific settings when option selected', async function() {
        await page.click('#useSiteSpecificSettings-1site-specific');
        await page.waitForTimeout(200);

        await capturePage('site_specific_settings_site1');
    });

    it('should save site-specific', async function() {
        await page.click(sitePrefix(1, '#anonymizeIpSettings'));
        await page.waitForTimeout(100);

        await page.click(sitePrefix(1, '#maskLength4'));
        await page.waitForTimeout(100);

        await page.click(sitePrefix(1, '#useAnonymizedIpForVisitEnrichment1'));
        await page.waitForTimeout(100);

        await page.click(sitePrefix(1, '#anonymizeUserId'));
        await page.waitForTimeout(100);

        await page.click(sitePrefix(1, '#anonymizeOrderId'));
        await page.waitForTimeout(100);

        await page.evaluate(() => $('div[idsite="1"] div.anonymizeReferrerField div.matomo-field-select div.select-wrapper input.dropdown-trigger')[0].click());
        await page.waitForTimeout(100);
        await page.evaluate(() => $('div[idsite="1"] div.anonymizeReferrerField div.matomo-field-select ul li:nth-child(3)').click());
        await page.waitForTimeout(100);

        await page.click(saveSitePrivacySettingsButton(1));
        await page.waitForTimeout(300);
        await page.waitForNetworkIdle();

        await capturePage('save_site_specific_settings_site1');
    });

    it('should load previously saved site-specific settings for site 1', async function() {
        await loadBasePage();
        await page.click(openSitePrivacySettingsSelector(1));
        await page.waitForTimeout(300);
        await page.waitForNetworkIdle();

        await capturePage('load_site_specific_settings_site1');
    });

    it('should show site-specific settings defaulting to instance-level set anonymisation settings', async function() {
        testEnvironment.optionsOverride = {
            'PrivacyManager.useAnonymizedIpForVisitEnrichment': '1',
            'PrivacyManager.ipAddressMaskLength': '1',
            'PrivacyManager.doNotTrackEnabled': '1',
            'PrivacyManager.ipAnonymizerEnabled': '1',
            'PrivacyManager.anonymizeUserId': '1',
            'PrivacyManager.anonymizeOrderId': '1',
            'PrivacyManager.anonymizeReferrer': 'exclude_path',
        };
        testEnvironment.save();

        await loadBasePage();
        await page.click(openSitePrivacySettingsSelector(3));
        await page.waitForTimeout(300);
        await page.waitForNetworkIdle();

        await page.click('#useSiteSpecificSettings-3site-specific');
        await page.waitForTimeout(200);

        testEnvironment.optionsOverride = {};
        testEnvironment.save();

        await capturePage('load_site_specific_settings_from_instance_for_site3');
    });

    it('should show site-specific settings defaulting to different instance-level set anonymisation settings', async function() {
        testEnvironment.optionsOverride = {
            'PrivacyManager.useAnonymizedIpForVisitEnrichment': '0',
            'PrivacyManager.ipAddressMaskLength': '1',
            'PrivacyManager.doNotTrackEnabled': '1',
            'PrivacyManager.ipAnonymizerEnabled': '0',
            'PrivacyManager.anonymizeUserId': '0',
            'PrivacyManager.anonymizeOrderId': '1',
            'PrivacyManager.anonymizeReferrer': 'exclude_query',
        };
        testEnvironment.save();

        await loadBasePage();
        await page.click(openSitePrivacySettingsSelector(2));
        await page.waitForTimeout(300);
        await page.waitForNetworkIdle();

        await page.click('#useSiteSpecificSettings-2site-specific');
        await page.waitForTimeout(200);

        testEnvironment.optionsOverride = {};
        testEnvironment.save();

        await capturePage('load_site_specific_settings_from_instance_for_site2');
    });

    it('should save site-specific settings for site 2', async function() {
        await page.click(saveSitePrivacySettingsButton(2));
        await page.waitForTimeout(300);
        await page.waitForNetworkIdle();

        await capturePage('save_site_specific_settings_site2');
    });

    it('should load previously saved site-specific settings for site 2', async function() {
        await loadBasePage();
        await page.click(openSitePrivacySettingsSelector(2));
        await page.waitForTimeout(300);
        await page.waitForNetworkIdle();

        await capturePage('load_site_specific_settings_site2');
    });

    it('should not display the privacy settings lock icons when privacy manager plugin is disabled', async function() {
        testEnvironment.pluginsToUnload = ['PrivacyManager'];
        await testEnvironment.save();

        await loadBasePage();
        await page.waitForTimeout(100);
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        await capturePage('no_lock_icons_when_plugin_disabled');

        delete testEnvironment.pluginsToUnload;
        await testEnvironment.save();
    });
});
