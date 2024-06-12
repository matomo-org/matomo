/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests for MultiSites.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('AllWebsitesDashboard', function () {
    this.fixture = 'Piwik\\Plugins\\MultiSites\\tests\\Fixtures\\ManySitesWithVisits';

    const parentSuite = this;

    const generalParams = 'idSite=1&period=day&date=2013-01-23';
    const dashboardUrl = '?module=MultiSites&action=index&' + generalParams;
    const widgetUrl = '?module=Widgetize&action=iframe&moduleToWidgetize=MultiSites&actionToWidgetize=standalone&' + generalParams;

    before(function() {
        testEnvironment.overrideConfig('FeatureFlags', {
            ImprovedAllWebsitesDashboard_feature: 'enabled',
        });

        testEnvironment.save();
    });

    beforeEach(async function() {
        // set in beforeEach() to have it set in each describe()
        await page.webpage.setViewport({
            width: 1440,
            height: 900,
        });
    });

    after(function () {
        delete testEnvironment.configOverride.FeatureFlags;

        testEnvironment.save();
    });

    describe('Rendering', function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        it('should load the all websites dashboard correctly', async function () {
            await page.goto(dashboardUrl);
            await page.waitForNetworkIdle();

            expect(await page.screenshotSelector('#main')).to.matchImage('dashboard');
        });

        it('should render properly when widgetized', async function () {
            await page.goto(widgetUrl);
            await page.waitForNetworkIdle();

            expect(await page.screenshotSelector('#main')).to.matchImage('widgetized');
        });
    });

    describe('Dashboard Controls', function () {
        it('should link to the SitesManager', async function () {
            await page.goto(dashboardUrl);
            await page.waitForNetworkIdle();
            await page.click('.dashboardControls .btn');
            await page.waitForNetworkIdle();

            await page.waitForSelector('.modal .add-site-dialog', { visible: true });
        });
    });
});
