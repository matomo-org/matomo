/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PrivacyManager_ConsentManager", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySiteWithSiteContentDetection";

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

    async function capturePage(screenshotName) {
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pageWrap,#notificationContainer,.modal.open')).to.matchImage(screenshotName);
    }


    async function captureModal(screenshotName) {
        await page.waitForNetworkIdle();
        const modal = await page.$('.modal.open');
        expect(await modal.screenshot()).to.matchImage(screenshotName);
    }

    it('should load privacy asking for consent page', async function() {
        await loadActionPage('consent');
        await capturePage('consent_default');
    });

});
