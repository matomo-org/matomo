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

    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySite";

    var generalParams = 'idSite=1&period=day&date=2017-01-02',
        urlBase = '?module=PrivacyManager&' + generalParams + '&action=';

    before(function () {
        testEnvironment.pluginsToLoad = ['PrivacyManager'];
        testEnvironment.detectedContentDetections = ['Osano'];
        testEnvironment.connectedConsentManagers = ['Osano'];
        testEnvironment.save();
    });

    after(function () {
        testEnvironment.detectedContentDetections = [];
        testEnvironment.connectedConsentManagers = [];
        testEnvironment.save();
    });

    it('should load privacy asking for consent page', async function() {
        await page.goto(urlBase + 'consent');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pageWrap,#notificationContainer,.modal.open')).to.matchImage('consent_default');
    });
});
