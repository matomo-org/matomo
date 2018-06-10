/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    function setAnonymizeStartEndDate(page)
    {
        // make sure tests do not fail every day
        page.evaluate(function () {
            $('input.anonymizeStartDate').val('2018-03-02').change();
            $('input.anonymizeEndDate').val('2018-03-02').change();
        }, 50);
    }
    
    function loadActionPage(page, action)
    {
        page.goto(urlBase + action);

        if (action === 'privacySettings') {
            setAnonymizeStartEndDate(page);
        }
    }

    function selectModalButton(page, button)
    {
        page.click('.modal.open .modal-footer a:contains('+button+')');
    }

    function findDataSubjects(page)
    {
        page.click('.findDataSubjects .btn');
    }

    function anonymizePastData(page)
    {
        page.click('.anonymizePastData .btn');
    }

    function deleteDataSubjects(page)
    {
        page.click('.deleteDataSubjects input');
    }

    function enterSegmentMatchValue(page, value) {
        page.execCallback(function () {
            page.webpage.evaluate(function (theVal) {
                $('.metricValueBlock input').each(function (index) {
                    $(this).val(theVal).change();
                });
            }, value);
        });
    }

    function selectVisitColumn(page, title)
    {
        page.evaluate(function () {
            $('.selectedVisitColumns:last input.select-dropdown').click();
        });
        page.click('.selectedVisitColumns:last .dropdown-content li:contains(' + title + ')');
    }

    function selectActionColumn(page, title)
    {
        page.evaluate(function () {
            $('.selectedActionColumns:last input.select-dropdown').click();
        });
        page.execCallback(function () {
            page.webpage.evaluate(function (theTitle) {
                $('.selectedActionColumns:last .dropdown-content li:contains(' + theTitle + ')').click();
            }, title);
        });
    }

    function capturePage(screenshotName, test, done) {
        expect.screenshot(screenshotName).to.be.captureSelector('.pageWrap,#notificationContainer,.modal.open', test, done);
    }

    function captureAnonymizeLogData(screenshotName, test, done) {
        expect.screenshot(screenshotName).to.be.captureSelector('.logDataAnonymizer,#notificationContainer,.modal.open,.logDataAnonymizer table', test, done);
    }

    function captureModal(screenshotName, test, done) {
        expect.screenshot(screenshotName).to.be.captureSelector('.modal.open', test, done);
    }

    it('should load privacy opt out page', async function() {
        capturePage('users_opt_out_default', function (page) {
            loadActionPage(page, 'usersOptOut');
        }, done);
    });

    it('should load privacy asking for consent page', async function() {
        capturePage('consent_default', function (page) {
            loadActionPage(page, 'consent');
        }, done);
    });

    it('should load GDPR overview page', async function() {
        capturePage('gdpr_overview', function (page) {
            testEnvironment.overrideConfig('Deletelogs', 'delete_logs_enable', '1');
            testEnvironment.overrideConfig('Deletelogs', 'delete_logs_older_than', '95');
            testEnvironment.overrideConfig('Deletereports', 'delete_reports_enable', '1');
            testEnvironment.overrideConfig('Deletereports', 'delete_reports_older_than', '131');
            testEnvironment.save();
            loadActionPage(page, 'gdprOverview');
        }, done);
    });

    it('should load GDPR overview page', async function() {
        capturePage('gdpr_overview_no_retention', function (page) {
            testEnvironment.overrideConfig('Deletelogs', 'delete_logs_enable', '0');
            testEnvironment.overrideConfig('Deletereports', 'delete_reports_enable', '0');
            testEnvironment.save();
            loadActionPage(page, 'gdprOverview');
        }, done);
    });

    it('should load privacy settings page', async function() {
        capturePage('privacy_settings_default', function (page) {
            loadActionPage(page, 'privacySettings');
        }, done);
    });

    it('should anonymize ip and visit column', async function() {
        captureAnonymizeLogData('anonymizelogdata_anonymizeip_and_visit_column_prefilled', function (page) {
            page.click('[name=anonymizeIp] label');
            page.wait(500);
            selectVisitColumn(page, 'config_browser_name');
            selectVisitColumn(page, 'config_cookie');
        }, done);
    });

    it('should show a confirmation message before executing any anonymization', async function() {
        captureModal('anonymizelogdata_anonymizeip_and_visit_column_confirmation_message', function (page) {
            anonymizePastData(page);
        }, done);
    });

    it('should be able to cancel anonymization of past data', async function() {
        captureAnonymizeLogData('anonymizelogdata_anonymizeip_and_visit_column_cancelled', function (page) {
            selectModalButton(page, 'No');
        }, done);
    });

    it('should be able to confirm anonymization of past data', async function() {
        captureAnonymizeLogData('anonymizelogdata_anonymizeip_and_visit_column_confirmed', function (page) {
            anonymizePastData(page);
            selectModalButton(page, 'Yes');
            setAnonymizeStartEndDate(page);
        }, done);
    });

    it('should prefill anonymize location and action column', async function() {
        captureAnonymizeLogData('anonymizelogdata_anonymizelocation_anduserid_and_action_column_prefilled', function (page) {
            loadActionPage(page, 'privacySettings');
            page.click('[name=anonymizeLocation] label');
            page.click('[name=anonymizeTheUserId] label');
            page.wait(500);
            selectActionColumn(page, 'time_spent_ref_action');
            selectActionColumn(page, 'idaction_content_name');
        }, done);
    });

    it('should confirm anonymize location and action column', async function() {
        captureAnonymizeLogData('anonymizelogdata_anonymizelocation_anduserid_and_action_column_confirmed', function (page) {
            anonymizePastData(page);
            selectModalButton(page, 'Yes');
            page.wait(1000);
            setAnonymizeStartEndDate(page);
        }, done);
    });

    it('should anonymize only one site and different date pre filled', async function() {
        captureAnonymizeLogData('anonymizelogdata_one_site_and_custom_date_prefilled', function (page) {
            page.click('.form-group #anonymizeSite .title');
            page.wait(1000);
            page.click(".form-group #anonymizeSite [title='Site 1']");
            page.click('[name=anonymizeIp] label');
            page.evaluate(function () {
                $('input.anonymizeStartDate').val('2017-01-01').change();
                $('input.anonymizeEndDate').val('2017-02-14').change();
            });
        }, done);
    });

    it('should anonymize only one site and different date confirmed', async function() {
        captureAnonymizeLogData('anonymizelogdata_one_site_and_custom_date_confirmed', function (page) {
            anonymizePastData(page);
            selectModalButton(page, 'Yes');
            page.wait(1000);
            setAnonymizeStartEndDate(page);
        }, done);
    });

    it('should load GDPR tools page', async function() {
        capturePage('gdpr_tools_default', function (page) {
            loadActionPage(page, 'gdprTools');
        }, done);
    });

    it('should show no visitor found message', async function() {
        capturePage('gdpr_tools_no_visits_found', function (page) {
            enterSegmentMatchValue(page, 'userfoobar')
            findDataSubjects(page);
        }, done);
    });

    it('should find visits', async function() {
        capturePage('gdpr_tools_visits_found', function (page) {
            enterSegmentMatchValue(page, 'userId203');

            findDataSubjects(page);
        }, done);
    });

    it('should be able to show visitor profile', async function() {
        capturePage('gdpr_tools_visits_showprofile', function (page) {
            page.click('.visitorLogTooltip:first');
        }, done);
    });

    it('should be able to add IP to segment search with one click', async function() {
        capturePage('gdpr_tools_enrich_segment_by_ip', function (page) {
            page.click('#Piwik_Popover .visitor-profile-close');
            page.click('.visitorIp:first a');
        }, done);
    });

    it('should be able to uncheck a visit', async function() {
        capturePage('gdpr_tools_uncheck_one_visit', function (page) {
            page.click('.entityTable tbody tr:nth-child(2) .checkInclude label');
        }, done);
    });

    it('should ask for confirmation before deleting any visit', async function() {
        capturePage('gdpr_tools_delete_visit_unconfirmed', function (page) {
            deleteDataSubjects(page);
        }, done);
    });

    it('should be able to cancel deletion and not delete any data', async function() {
        capturePage('gdpr_tools_delete_visit_cancelled', function (page) {
            selectModalButton(page, 'No');
        }, done);
    });

    it('should verify really no data deleted', async function() {
        capturePage('gdpr_tools_delete_visit_cancelled_verified_no_data_deleted', function (page) {
            loadActionPage(page, 'gdprTools');
            page.wait(1000);
            enterSegmentMatchValue(page, 'userId203');
            findDataSubjects(page);
            page.click('.entityTable tbody tr:nth-child(2) .checkInclude label');
        }, done);
    });

    it('should be able to confirm deletion and then actually delete data', async function() {
        capturePage('gdpr_tools_delete_visit_confirmed', function (page) {
            deleteDataSubjects(page);
            selectModalButton(page, 'Yes');
        }, done);
    });

});