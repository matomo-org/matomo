/**!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
describe("Tour_ConsentManager", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySite";

    var generalParams = 'idSite=1&period=day&date=2010-01-03',
        widgetizeParams = "module=Widgetize&action=iframe";

    var widgetUrl = "?" + widgetizeParams + "&" + generalParams + "&moduleToWidgetize=Tour&actionToWidgetize=getEngagement";

    before(async function () {
        testEnvironment.pluginsToLoad = ['Tour'];
        testEnvironment.detectedContentDetections = ['Osano'];
        testEnvironment.connectedConsentManagers = ['Osano'];
        testEnvironment.save();
    });

    after(async function () {
        testEnvironment.detectedContentDetections = [];
        testEnvironment.connectedConsentManagers = [];
        testEnvironment.save();
    });

    it('should show consent manager challenge in widget', async function () {
        await page.goto(widgetUrl);
        expect(await page.screenshot()).to.matchImage('widget_initial');
    });

});
