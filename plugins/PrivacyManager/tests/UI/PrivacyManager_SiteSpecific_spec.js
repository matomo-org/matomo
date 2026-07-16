/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PrivacyManager_SiteSpecific", function () {
    this.fixture = "Piwik\\Plugins\\PrivacyManager\\tests\\Fixtures\\MultipleSitesMultipleVisitsFixture";

    var generalParams = 'idSite=1&period=day&date=2017-01-02',
        urlBase = '?module=SitesManager&' + generalParams + '&action=';

    async function resetPrivacySettings()
    {
        await testEnvironment.callApi('PrivacyManager.setAnonymizeIpSettings', {
            anonymizeIPEnable: false,
            ipAddressMaskLength: 0,
            useAnonymizedIpForVisitEnrichment: false,
            anonymizeUserId: false,
            anonymizeOrderId: false,
            anonymizeReferrer: '',
            forceCookielessTracking: false,
            randomizeConfigId: false,
        });

        // Persisted UI test runs can reuse a dirty fixture, so make sure the site-specific
        // privacy settings for the sites we touch always start from the baseline fixture state.
        for (const idSiteSpecific of [1, 2]) {
            await testEnvironment.callApi('PrivacyManager.setAnonymizeIpSettings', {
                anonymizeIPEnable: false,
                ipAddressMaskLength: 0,
                useAnonymizedIpForVisitEnrichment: false,
                anonymizeUserId: false,
                anonymizeOrderId: false,
                anonymizeReferrer: '',
                forceCookielessTracking: false,
                randomizeConfigId: false,
                idSiteSpecific,
                useSiteSpecificSettings: false,
            });
        }
    }

    before(async function () {
        testEnvironment.pluginsToLoad = ['PrivacyManager'];
        await testEnvironment.save();
        await resetPrivacySettings();
    });

    after(async function () {
        await resetPrivacySettings();
    });

    after(async function () {
        // The "should show site-specific settings defaulting to instance-level..."
        // tests use testEnvironment.optionsOverride to write PrivacyManager.* rows
        // to the option table, then reset optionsOverride to {}. That stops future
        // bootstraps from re-setting them, but the rows already written stay. Under
        // --plugin=PrivacyManager --persist-fixture-data the next spec sharing this
        // fixture DB (PrivacyManager_spec) then renders privacy settings with leaked
        // mask length, anonymize order id and anonymize referrer values. Re-apply
        // fixture-matching values so the following bootstraps overwrite the leaks.
        // randomizeConfigId is intentionally omitted because PrivacyManager_spec
        // saves it via the form and would be reset on every reload otherwise.
        testEnvironment.optionsOverride = testEnvironment.optionsOverride || {};
        testEnvironment.optionsOverride['PrivacyManager.ipAnonymizerEnabled'] = '0';
        testEnvironment.optionsOverride['PrivacyManager.ipAddressMaskLength'] = '0';
        testEnvironment.optionsOverride['PrivacyManager.useAnonymizedIpForVisitEnrichment'] = '0';
        testEnvironment.optionsOverride['PrivacyManager.doNotTrackEnabled'] = '0';
        testEnvironment.optionsOverride['PrivacyManager.anonymizeUserId'] = '0';
        testEnvironment.optionsOverride['PrivacyManager.anonymizeOrderId'] = '0';
        testEnvironment.optionsOverride['PrivacyManager.anonymizeReferrer'] = '';
        testEnvironment.save();

        // The "should save site-specific" / "...for site 2" tests write per-site
        // rows like PrivacyManager.idSite(N).%. Config::useSiteSpecificSettings()
        // returns true while any such row exists, so even values that match the
        // defaults still flip the compliance page rendering. optionsOverride can
        // only Option::set, so call setAnonymizeIpSettings with
        // useSiteSpecificSettings=false to invoke Config::removeForSite() and
        // delete every row under that prefix. Each call also bootstraps the
        // proxy, applying the instance-level overrides above.
        for (const idSiteSpecific of [1, 2]) {
            await testEnvironment.callApi('PrivacyManager.setAnonymizeIpSettings', {
                anonymizeIPEnable: false,
                ipAddressMaskLength: 0,
                useAnonymizedIpForVisitEnrichment: false,
                idSiteSpecific,
                useSiteSpecificSettings: false,
            });
        }
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

    async function hideUTCTimeInfo() {
        await page.evaluate(function () {
            $('.form-help:contains(UTC time is)').hide();
        });
        await page.waitForTimeout(200);
    }

    async function setCnilPolicyEnforced(enforced) {
        if (enforced) {
            testEnvironment.overrideConfig('CnilPolicy', 'cnil_v1_policy_enabled', 1);
        } else {
            delete testEnvironment.configOverride.CnilPolicy;
        }
        await testEnvironment.save();
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
        return sitePrefix(idSite, 'button[title="Edit"]');
    }

    function cancelSitePrivacySettingsButton(idSite) {
        return sitePrefix(idSite, '.editingSiteFooter button');
    }

    function saveSitePrivacySettingsButton(idSite) {
        return sitePrefix(idSite, '.editingSiteFooter input[value="Save"]');
    }

    it('should show privacy settings for multiple sites at the same time', async function() {
        await loadBasePage();
        await page.click(openSitePrivacySettingsSelector(1));
        await page.waitForTimeout(200);
        await page.waitForNetworkIdle();

        await page.click(openSitePrivacySettingsSelector(3));
        await page.waitForTimeout(200);
        await page.waitForNetworkIdle();
        await hideUTCTimeInfo();

        await capturePage('show_settings');
    });

    it('should close privacy settings for a given site', async function() {
        await page.click(cancelSitePrivacySettingsButton(3));
        await page.waitForTimeout(200);
        await hideUTCTimeInfo();

        await capturePage('close_one_site_settings');
    });

    it('should show site-specific settings when option selected', async function() {
        await page.click('#useSiteSpecificSettings1site-specific');
        await page.waitForTimeout(200);
        await hideUTCTimeInfo();

        await capturePage('site_specific_settings_site1');
    });

    it('should save site-specific', async function() {
        await page.click(sitePrefix(1, '#anonymizeIpSettings1'));
        await page.waitForTimeout(100);

        await page.click(sitePrefix(1, '#maskLength14'));
        await page.waitForTimeout(100);

        await page.click(sitePrefix(1, '#useAnonymizedIpForVisitEnrichment11'));
        await page.waitForTimeout(100);

        await page.click(sitePrefix(1, '#anonymizeUserId1'));
        await page.waitForTimeout(100);

        await page.click(sitePrefix(1, '#anonymizeOrderId1'));
        await page.waitForTimeout(100);

        await page.evaluate(() => $('div[idsite="1"] div.anonymizeReferrerField div.matomo-field-select div.select-wrapper input.dropdown-trigger')[0].click());
        await page.waitForTimeout(100);
        await page.evaluate(() => $('div[idsite="1"] div.anonymizeReferrerField div.matomo-field-select ul li:nth-child(3)').click());
        await page.waitForTimeout(100);

        await page.click(saveSitePrivacySettingsButton(1));
        await page.waitForTimeout(300);
        await page.waitForNetworkIdle();
        await hideUTCTimeInfo();

        await capturePage('save_site_specific_settings_site1');
    });

    it('should load previously saved site-specific settings for site 1', async function() {
        await loadBasePage();
        await page.click(openSitePrivacySettingsSelector(1));
        await page.waitForTimeout(300);
        await page.waitForNetworkIdle();
        await hideUTCTimeInfo();

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

        await page.click('#useSiteSpecificSettings3site-specific');
        await page.waitForTimeout(200);
        await hideUTCTimeInfo();

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

        await page.click('#useSiteSpecificSettings2site-specific');
        await page.waitForTimeout(200);
        await hideUTCTimeInfo();

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
        await hideUTCTimeInfo();

        await capturePage('load_site_specific_settings_site2');
    });

    it('should display compliance info for policy controlled settings for site 2', async function() {
        await setCnilPolicyEnforced(true);

        await loadBasePage();
        await page.click(openSitePrivacySettingsSelector(2));
        await page.waitForTimeout(300);
        await page.waitForNetworkIdle();
        await hideUTCTimeInfo();

        await setCnilPolicyEnforced(false);

        await capturePage('load_site_specific_settings_site2_compliance_info');
    });

    it('should not display the privacy settings when privacy manager plugin is disabled', async function() {
        testEnvironment.pluginsToUnload = ['PrivacyManager'];
        await testEnvironment.save();

        await loadBasePage();
        await page.click(openSitePrivacySettingsSelector(1));
        await page.waitForTimeout(300);
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);
        await hideUTCTimeInfo();

        await capturePage('no_privacy_settings_when_plugin_disabled');

        delete testEnvironment.pluginsToUnload;
        await testEnvironment.save();
    });
});
