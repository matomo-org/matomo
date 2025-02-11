/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PrivacyManager", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\PrivacyManager\\tests\\Fixtures\\MultipleSitesMultipleVisitsFixture";

    var generalParams = 'idSite=1&period=day&date=2017-01-02',
        urlBase = '?module=PrivacyManager&' + generalParams + '&action=';

    before(function () {
        testEnvironment.pluginsToLoad = ['PrivacyManager'];
        testEnvironment.save();
    });

    async function setAnonymizeStartEndDate()
    {
        // make sure tests do not fail every day
        await page.waitForSelector('input.anonymizeStartDate');
        await page.waitForSelector('input.anonymizeEndDate');
        await page.waitForTimeout(100);
        await page.evaluate(function () {
            $('input.anonymizeStartDate').val('2018-03-02').change();
        });
        await page.waitForTimeout(100);
        await page.evaluate(function () {
            $('input.anonymizeEndDate').val('2018-03-02').change();
        });
        await page.waitForTimeout(100);
    }

    async function loadActionPage(action)
    {
        await page.goto('about:blank');
        await page.goto(urlBase + action);
        await page.waitForNetworkIdle();

        if (action === 'privacySettings') {
            await setAnonymizeStartEndDate();
        }
    }

    async function selectModalButton(button)
    {
        var elem = await page.jQuery('.modal.open .modal-footer a:contains('+button+')');
        await elem.click();
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();
    }

    async function typeUserPassword()
    {
        var elem = await page.jQuery('.modal.open #currentUserPassword');
        await elem.type(superUserPassword);
        await page.waitForTimeout(100);
    }

    async function findDataSubjects()
    {
        await page.click('.findDataSubjects .btn');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);
    }

    async function anonymizePastData()
    {
        await page.click('.anonymizePastData .btn');
        await page.waitForTimeout(1000); // wait for animation
    }

    async function deleteDataSubjects()
    {
        await page.evaluate(() => $('.deleteDataSubjects input').click());
        await page.waitForTimeout(500); // wait for animation
    }

    async function enterSegmentMatchValue(value) {
        await page.evaluate(theVal => {
            $('.metricValueBlock input').each(function (index) {
                $(this).val(theVal).change();
            });
        }, value);
        await page.waitForTimeout(200);
    }

    async function selectVisitColumn(title)
    {
        await page.waitForTimeout(100);
        await page.evaluate(function () {
            $('.selectedVisitColumns:last input.select-dropdown').click();
        });
        await page.waitForTimeout(100);
        await page.evaluate(title => {
            $('.selectedVisitColumns:last .dropdown-content li:contains(' + title + ')').click();
        }, title);
        await page.waitForTimeout(100);
    }

    async function selectActionColumn(title)
    {
        await page.waitForTimeout(100);
        await page.evaluate(function () {
            $('.selectedActionColumns:last input.select-dropdown').click();
        });
        await page.waitForTimeout(100);
        await page.evaluate(theTitle => {
            $('.selectedActionColumns:last .dropdown-content li:contains(' + theTitle + ')').click();
        }, title);
        await page.waitForTimeout(100);
    }

    async function capturePage(screenshotName) {
        await page.waitForNetworkIdle();
        const pageWrap = await page.$('.pageWrap,#notificationContainer,.modal.open');
        const screenshot = await pageWrap.screenshot();
        expect(screenshot).to.matchImage(screenshotName);
    }

    async function captureAnonymizeLogData(screenshotName) {
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.logDataAnonymizer,#notificationContainer,.modal.open,.logDataAnonymizer table')).to.matchImage(screenshotName);
    }

    async function captureModal(screenshotName) {
        await page.waitForNetworkIdle();
        const modal = await page.$('.modal.open');
        expect(await modal.screenshot()).to.matchImage(screenshotName);
    }

    it('should load privacy opt out page', async function() {
        await loadActionPage('usersOptOut');
        await capturePage('users_opt_out_default');
    });

    it('should load privacy asking for consent page', async function() {
        await loadActionPage('consent');
        await capturePage('consent_default');
    });

    it('should load GDPR overview page', async function() {
        testEnvironment.overrideConfig('Deletelogs', 'delete_logs_enable', '1');
        testEnvironment.overrideConfig('Deletelogs', 'delete_logs_older_than', '95');
        testEnvironment.overrideConfig('Deletereports', 'delete_reports_enable', '1');
        testEnvironment.overrideConfig('Deletereports', 'delete_reports_older_than', '131');
        testEnvironment.save();
        await loadActionPage('gdprOverview');

        await capturePage('gdpr_overview');
    });

    it('should load GDPR overview page', async function() {
        testEnvironment.overrideConfig('Deletelogs', 'delete_logs_enable', '0');
        testEnvironment.overrideConfig('Deletereports', 'delete_reports_enable', '0');
        testEnvironment.save();
        await loadActionPage('gdprOverview');

        await capturePage('gdpr_overview_no_retention');
    });

    it('should load privacy settings page with config ID randomisation setting visible', async function() {
        testEnvironment.overrideConfig('FeatureFlags', {
          ConfigIdRandomisation_feature: 'enabled',
        });
        testEnvironment.save();

        await loadActionPage('privacySettings');
        await page.waitForNetworkIdle();

        delete testEnvironment.configOverride.FeatureFlags.ConfigIdRandomisation_feature;
        testEnvironment.save();

        await capturePage('privacy_settings_default_with_randomisation');
    });

    it('should load privacy settings page', async function() {
        await loadActionPage('privacySettings');
        await page.waitForNetworkIdle();
        await capturePage('privacy_settings_default');
    });

    it('should anonymize ip and visit column', async function() {
        await page.waitForSelector('[name="anonymizeIp"] label');
        await page.click('[name="anonymizeIp"] label');
        await selectVisitColumn('config_browser_name');
        await selectVisitColumn('config_cookie');

        await captureAnonymizeLogData('anonymizelogdata_anonymizeip_and_visit_column_prefilled');
    });

    it('should show a confirmation message before executing any anonymization', async function() {
        await anonymizePastData();

        await captureModal('anonymizelogdata_anonymizeip_and_visit_column_confirmation_message');
    });

    it('should be able to cancel anonymization of past data', async function() {
        await selectModalButton('Cancel');

        await captureAnonymizeLogData('anonymizelogdata_anonymizeip_and_visit_column_cancelled');
    });

    it('should be able to confirm anonymization of past data', async function() {
        await anonymizePastData();
        await typeUserPassword();
        await selectModalButton('Confirm');
        await setAnonymizeStartEndDate();

        await captureAnonymizeLogData('anonymizelogdata_anonymizeip_and_visit_column_confirmed');
    });

    it('should prefill anonymize location and action column', async function() {
        await loadActionPage('privacySettings');
        await page.click('[name="anonymizeLocation"] label');
        await page.click('[name="anonymizeTheUserId"] label');
        await page.waitForTimeout(500);
        await selectActionColumn('time_spent_ref_action');
        await selectActionColumn('idaction_content_name');

        await captureAnonymizeLogData('anonymizelogdata_anonymizelocation_anduserid_and_action_column_prefilled');
    });

    it('should confirm anonymize location and action column', async function() {
        await anonymizePastData();
        await typeUserPassword();
        await selectModalButton('Confirm');
        await page.waitForTimeout(1000);
        await setAnonymizeStartEndDate();

        await captureAnonymizeLogData('anonymizelogdata_anonymizelocation_anduserid_and_action_column_confirmed');
    });

    it('should anonymize only one site and different date pre filled', async function() {
        await page.click('.form-group #anonymizeSite .title');
        await page.waitForTimeout(1000);
        await page.click(".form-group #anonymizeSite [title='Site 1']");
        await page.click('[name="anonymizeIp"] label');
        await page.waitForTimeout(100);
        await page.evaluate(function () {
            $('input.anonymizeStartDate').val('2017-01-01').change();
        });
        await page.waitForTimeout(100);
        await page.evaluate(function () {
           $('input.anonymizeEndDate').val('2017-02-14').change();
        });
        await page.waitForTimeout(100);

        await captureAnonymizeLogData('anonymizelogdata_one_site_and_custom_date_prefilled');
    });

    it('should anonymize only one site and different date confirmed', async function() {
        await anonymizePastData();
        await typeUserPassword();
        await selectModalButton('Confirm');
        await page.waitForTimeout(1000);
        await setAnonymizeStartEndDate();

        await captureAnonymizeLogData('anonymizelogdata_one_site_and_custom_date_confirmed');
    });

    it('should load GDPR tools page', async function() {
        await loadActionPage('gdprTools');

        await capturePage('gdpr_tools_default');
    });

    it('should show no visitor found message', async function() {
        await enterSegmentMatchValue('userfoobar');
        await findDataSubjects();
        await page.waitForSelector('.manageGdpr tr');
        await page.mouse.move(-10, -10);

        await capturePage('gdpr_tools_no_visits_found');
    });

    it('should find visits', async function() {
        await enterSegmentMatchValue('userId203');
        await findDataSubjects();

        await capturePage('gdpr_tools_visits_found');
    });

    it('should be able to show visitor profile', async function() {
        var elem = await page.jQuery('.visitorLogTooltip:first');
        await elem.click();
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.ui-dialog')).to.matchImage('gdpr_tools_visits_showprofile');
    });

    it('should be able to add IP to segment search with one click', async function() {
        await page.click('#Piwik_Popover .visitor-profile-close');
        var elem = await page.jQuery('.visitorIp:first a');
        await elem.click();
        await page.waitForNetworkIdle();

        await capturePage('gdpr_tools_enrich_segment_by_ip');
    });

    it('should be able to uncheck a visit', async function() {
        await page.click('.entityTable tbody tr:nth-child(2) .checkInclude label');
        await page.mouse.move(-10, -10);
        await capturePage('gdpr_tools_uncheck_one_visit');
    });

    it('should ask for confirmation before deleting any visit', async function() {
        await deleteDataSubjects();
        const modal = await page.waitForSelector('.modal.open', { visible: true });
        expect(await modal.screenshot()).to.matchImage('gdpr_tools_delete_visit_unconfirmed');
    });

    it('should be able to cancel deletion and not delete any data', async function() {
        await selectModalButton('No');
        await page.waitForTimeout(500);
        await capturePage('gdpr_tools_delete_visit_cancelled');
    });

    it('should verify really no data deleted', async function() {
        await loadActionPage('gdprTools');
        await page.waitForTimeout(1000);
        await enterSegmentMatchValue('userId203');
        await findDataSubjects();
        await page.click('.entityTable tbody tr:nth-child(2) .checkInclude label');

        await capturePage('gdpr_tools_delete_visit_cancelled_verified_no_data_deleted');
    });

    it('should be able to confirm deletion and then actually delete data', async function() {
        await deleteDataSubjects();
        await selectModalButton('Yes');

        await capturePage('gdpr_tools_delete_visit_confirmed');
    });

});
