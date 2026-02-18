/*!
 * Matomo - free/libre analytics platform
 *
 * SitesManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('BotTrackingSiteWithoutData', function () {
    this.fixture = 'Piwik\\Tests\\Fixtures\\OneVisit';

    const generalParams = 'idSite=1&period=day&date=today';
    const urlBase = `module=CoreHome&action=index&${generalParams}`;
    const urlOverview = `?${urlBase}#?${generalParams}&category=General_AIAssistants&subcategory=BotTracking_AIChatbotsOverview`;

    before(function () {
        testEnvironment.detectedContentDetections = [];
        testEnvironment.connectedConsentManagers = [];
        testEnvironment.save();
    });

    after(function () {
        // unset all detections so fake class is no longer used
        delete testEnvironment.detectedContentDetections;
        delete testEnvironment.connectedConsentManagers;
        testEnvironment.save();
    });

    it('should show a message if no bot data has been recently tracked', async function () {
        await page.goto(urlOverview);
        await page.waitForSelector('.bot-tracking-no-recent-requests-message');

        const notification = await page.$('.bot-tracking-no-recent-requests-message');

        expect(await notification.getProperty('textContent')).to.match(/No data collected/i);
    });

    it('should show the no data page', async function () {
        await page.click('.bot-tracking-no-recent-requests-message a');
        await page.waitForSelector('.tracking-method-list');

        const pageElement = await page.$('.page');
        expect(await pageElement.screenshot()).to.matchImage('siteWithoutData');
    });

    it('should show details for the recommended tracking method', async function () {
        await page.click('.tracking-method-detection .btn');
        await page.waitForSelector('.tracking-method-details');

        expect(await page.$('.tracking-method-details p')).to.be.ok;
    });

    it('should show details for all other available tracking methods', async function () {
        await page.click('.tracking-method-back');
        await page.waitForSelector('.tracking-method-list');

        const methodCount = (await page.$$('.tracking-method-list .list-entry a')).length;

        // only support link should be skipped
        const methodChecksExpected = methodCount - 1;
        let methodsChecked = 0;

        for (let methodNum = 1; methodNum <= methodCount; methodNum++) {
            const method = await page.$(`.tracking-method-list .list-entry:nth-of-type(${methodNum}) a`);
            const hrefProp = await method.getProperty('href');
            const href = await hrefProp.jsonValue();

            if (!href.includes('#')) {
                // skip click on external links (detections without details)
                continue;
            }

            await method.click();
            await page.waitForSelector('.tracking-method-details');

            expect(await page.$('.tracking-method-details p')).to.be.ok;
            methodsChecked++;

            await page.click('.tracking-method-back');
            await page.waitForSelector('.tracking-method-list');
        }

        expect(methodsChecked).to.equal(
          methodChecksExpected,
          `Expected ${methodChecksExpected} tracking methods with details, found ${methodsChecked}`
        )
    });

    it('should link back to the overview', async function () {
        await page.click('.tracking-method-skip a');
        await page.waitForSelector('.matomo-widget');
        await page.waitForNetworkIdle();

        const widgets = await page.$$('.matomo-widget');
        expect(widgets.length).to.be.greaterThan(0);
    });
});
