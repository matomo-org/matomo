/*!
 * Matomo - free/libre analytics platform
 *
 * UI tests for the visits of visitors who gave consent on a website whose compliance policy disables the visits log.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Live_ConsentedVisits", function () {
    this.fixture = "Piwik\\Plugins\\PrivacyManager\\tests\\Fixtures\\ConsentedVisitsFixture";

    const policySite = 5;
    const otherSite = 1;
    const notice = 'Showing consented visitors only — CNIL compliance mode is active.';

    function widgetUrl(action, idSite, extraParams = '')
    {
        return '?module=Widgetize&action=iframe&moduleToWidgetize=Live&actionToWidgetize=' + action
            + '&idSite=' + idSite + '&period=year&date=2017-01-02&disableLink=1&widget=1' + extraParams;
    }

    async function load(url)
    {
        await page.goto('about:blank');
        await page.goto(url);
        await page.waitForNetworkIdle();
    }

    async function noticeTexts()
    {
        return page.evaluate(() => $('.alert-info:visible').map(function () {
            return $(this).text().trim();
        }).get());
    }

    async function consentBadges(scope)
    {
        return page.evaluate((scope) => $(scope + ' .consentState').map(function () {
            return $(this).text().trim();
        }).get(), scope);
    }

    it('should list only consented visits with a notice in the visits log of the policy site', async function () {
        await load(widgetUrl('getLastVisitsDetails', policySite));

        expect(await noticeTexts()).to.deep.equal([notice]);

        const rows = await page.evaluate(() => $('.dataTableVizVisitorLog .card.row').length);
        const badges = await consentBadges('.dataTableVizVisitorLog');
        expect(rows).to.be.above(0);
        expect(badges).to.have.length(rows);
        expect(Array.from(new Set(badges))).to.deep.equal(['Consented']);
    });

    it('should show every visit with its consent badge and no notice on a site without policy', async function () {
        await load(widgetUrl('getLastVisitsDetails', otherSite));

        expect(await noticeTexts()).to.deep.equal([]);

        const rows = await page.evaluate(() => $('.dataTableVizVisitorLog .card.row').length);
        const badges = await consentBadges('.dataTableVizVisitorLog');
        // visits without a stored decision have no badge
        expect(badges.length).to.be.below(rows);
        expect(Array.from(new Set(badges)).sort()).to.deep.equal(['Consented', 'Refused']);
    });

    it('should show the consent badge in a visits log row', async function () {
        await load(widgetUrl('getLastVisitsDetails', otherSite));

        const row = await page.evaluateHandle(() => $('.dataTableVizVisitorLog .card.row:has(.consentState)').get(0));
        expect(await row.asElement().screenshot()).to.matchImage('visitor_log_row_consent_badge');
    });

    it('should show the visitor profile of a consented visitor with a notice on the policy site', async function () {
        await load(widgetUrl('getVisitorProfilePopup', policySite));

        expect(await page.evaluate(() => $('.visitor-profile').length)).to.equal(1);
        expect(await noticeTexts()).to.deep.equal([notice]);

        const badges = await consentBadges('.visitor-profile-visits');
        expect(Array.from(new Set(badges))).to.deep.equal(['Consented']);
    });

    it('should list visits with a notice in the real-time widget of the policy site', async function () {
        await load(widgetUrl('widget', policySite));
        await page.waitForSelector('#visitsLive', { visible: true });

        expect(await noticeTexts()).to.deep.equal([notice]);
        expect(await page.evaluate(() => $('.visitsLiveFooter').length)).to.equal(1);
    });

    it('should show no notice in the real-time widget of a site without policy', async function () {
        await load(widgetUrl('widget', otherSite));
        await page.waitForSelector('#visitsLive', { visible: true });

        expect(await noticeTexts()).to.deep.equal([]);
    });
});
