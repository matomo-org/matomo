/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("TrackingFailures", function () {
    this.fixture = 'Piwik\\Tests\\Fixtures\\InvalidVisits';

    var manageUrl = '?module=CoreAdminHome&action=trackingFailures&idSite=1&period=day&date=today';
    var widgetUrl = '?module=Widgetize&action=iframe&moduleToWidgetize=CoreAdminHome&actionToWidgetize=getTrackingFailures&idSite=1&period=day&date=today&widget=1';

    function generateTrackingFailures()
    {
        testEnvironment.generateTrackingFailures = 1;
        testEnvironment.save();
    }

    async function confirmModal()
    {
        await (await page.jQuery('.modal.open .modal-footer a:contains(Yes):visible')).click();
    }

    afterEach(function () {
        delete testEnvironment.generateTrackingFailures;
        testEnvironment.save();
    });

    it('should show widget with no failures', async function () {
        await page.goto(widgetUrl);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('widget_no_failures');
    });

    it('should show manage page with no failures', async function () {
        await page.goto(manageUrl);
        const frame = await page.waitForSelector('.matomoTrackingFailures');
        expect(await frame.screenshot()).to.matchImage('manage_no_failures');
    });

    it('should show widget with failures', async function () {
        generateTrackingFailures();
        await page.waitForTimeout(500);
        await page.goto(widgetUrl);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('widget_with_failures');
    });

    it('should show manage page with failures', async function () {
        await page.goto(manageUrl);
        await page.waitForSelector('.matomoTrackingFailures td');
        await page.waitForTimeout(250);

        const elem = await page.$('.matomoTrackingFailures');
        expect(await elem.screenshot()).to.matchImage('manage_with_failures');
    });

    it('should show ask to confirm delete one', async function () {
        await page.evaluate(function () {
            $('.matomoTrackingFailures table tbody tr:nth-child(2) .icon-delete').click();
        });

        const elem = await page.waitForSelector('.modal.open');
        await page.waitForTimeout(500);
        expect(await elem.screenshot()).to.matchImage('manage_with_failures_delete_one_ask_confirmation');
    });

    it('should show delete when confirmed', async function () {
        await confirmModal();
        await page.waitForNetworkIdle();

        await page.waitForSelector('.matomoTrackingFailures td .icon-delete');

        await page.waitForTimeout(500); // animation

        const elem = await page.$('.matomoTrackingFailures');
        expect(await elem.screenshot()).to.matchImage('manage_with_failures_delete_one_confirmed');
    });

    it('should mark the failures of a website that is excluded from the email', async function () {
        const settings = '.trackingFailuresNotificationSettings';

        // the test before deleted the only failure of an existing website
        generateTrackingFailures();
        await page.waitForTimeout(500);
        await page.goto(manageUrl);
        await page.waitForSelector(settings + '__siteAdd');

        await page.click(settings + '__siteAdd');
        await page.click(settings + ' .matomo-save-button .btn');
        await page.waitForNetworkIdle();
        await page.waitForSelector(settings + ' .matomo-save-button .btn:not([disabled])');

        const excluded = await page.$$eval('.failureRow__excluded', (elements) => {
            return elements.map((e) => e.closest('td').textContent.replace(/\s+/g, ' ').trim());
        });
        expect(excluded).to.deep.equal(['new site name (Id 1) Excluded from email notifications']);

        expect(await (await page.$(settings)).screenshot()).to.matchImage('notification_settings');
    });

    it('should keep the excluded website after a reload and allow removing it', async function () {
        const settings = '.trackingFailuresNotificationSettings';

        await page.goto(manageUrl);
        await page.waitForSelector('.failureRow__excluded');
        expect(await page.$$eval(settings + '__siteItem', (e) => e.length)).to.equal(1);

        await page.click(settings + '__siteRemove');
        await page.click(settings + ' .matomo-save-button .btn');
        await page.waitForNetworkIdle();
        await page.waitForSelector(settings + ' .matomo-save-button .btn:not([disabled])');

        expect(await page.$$eval('.failureRow__excluded', (e) => e.length)).to.equal(0);
        expect(await page.$$eval(settings + '__siteItem', (e) => e.length)).to.equal(0);
    });

    it('should show ask to confirm delete all', async function () {
        await page.click('.matomoTrackingFailures .deleteAllFailures');
        await page.waitForSelector('.modal.open');
        await page.waitForTimeout(500);
        expect(await (await page.$('.modal.open')).screenshot()).to.matchImage('manage_with_failures_delete_all_ask_confirmation');
    });

    it('should show nothing when confirmed', async function () {
        await confirmModal();
        await page.waitForSelector('.matomoTrackingFailures td .icon-ok');
        await page.waitForTimeout(500);
        const frame = await page.waitForSelector('.matomoTrackingFailures');
        expect(await frame.screenshot()).to.matchImage('manage_with_failures_delete_all_confirmed');
    });

});
