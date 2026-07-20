/*!
 * Matomo - free/libre analytics platform
 *
 * segmented visitor log row action security regression tests
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SegmentedVisitorLog", function () {
    this.fixture = "Piwik\\Plugins\\Live\\tests\\Fixtures\\VisitsWithAllActionsAndDevices";

    it('refuses to steer the popover request to a different module/action', async function () {
        const testJson = JSON.stringify({
            module: 'CoreAdminHome',
            action: 'testfakeAction',
            mailHost: 'test.example',
            force_api_session: 0,
            format: 'json'
        });
        const popoverPayload = 'RowAction:SegmentVisitorLog:Actions.getPageUrls:'
            + encodeURIComponent('country==us') + ':'
            + encodeURIComponent(testJson);
        const hashValue = encodeURIComponent(popoverPayload).replace(/%/g, '$');
        const theUrl = '?module=CoreHome&action=index&idSite=1&period=day&date=yesterday'
            + '#?popover=' + hashValue;

        const popoverRequest = page.webpage.waitForRequest(
            (req) => req.url().includes('segment=country') && req.url().includes('disableLink=1')
        );

        await page.goto('about:blank');
        await page.goto(theUrl);

        const url = (await popoverRequest).url();

        expect(url).to.contain('module=Live');
        expect(url).to.contain('action=indexVisitorLog');
        expect(url).to.not.contain('module=CoreAdminHome');
        expect(url).to.not.contain('action=testfakeAction');
        expect(url).to.not.contain('force_api_session=0');
        expect(url).to.not.match(/[?&]format=json(&|$)/);
        expect(url).to.not.contain('mailHost=');
    });
});
