/*!
 * Matomo - free/libre analytics platform
 *
 * UI tests for finding the visits of visitors who gave consent in the GDPR tools.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PrivacyManager_GdprConsent", function () {
    this.fixture = "Piwik\\Plugins\\PrivacyManager\\tests\\Fixtures\\ConsentedVisitsFixture";

    const policySite = '5';
    const url = '?module=PrivacyManager&idSite=1&period=day&date=2017-01-02&action=gdprTools';

    async function loadGdprTools()
    {
        await page.goto('about:blank');
        await page.goto(url);
        await page.waitForNetworkIdle();
    }

    async function selectSite(id)
    {
        await page.click('.siteSelector a.title');
        await page.click('.siteSelector .mtm-dropdownPanel__menu a[href*="idSite=' + id + '"]');
        await page.waitForNetworkIdle();
    }

    async function findVisitsOfUserIdsStartingWith(value)
    {
        await page.waitForSelector('.metricMatchBlock input');
        await page.click('.metricMatchBlock input');
        await page.click('.metricMatchBlock ul.select-dropdown li:nth-child(5)');
        await page.evaluate(theVal => {
            $('.metricValueBlock input').val(theVal).change();
        }, value);
        // as enterSegmentMatchValue() in PrivacyManager_spec.js, the segment editor takes the value over in a moment
        await page.waitForTimeout(200);

        await page.click('.findDataSubjects .btn');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.manageGdpr tbody td.consent');
    }

    async function segmentDefinition()
    {
        return page.evaluate(() => $('.segmentFilterGroup .metricListBlock input').first().val());
    }

    async function consentCells()
    {
        return page.evaluate(() => $('.manageGdpr tbody td.consent').map(function () {
            return $(this).text().trim();
        }).get());
    }

    async function isConsentedVisitsOnlyNoteShown()
    {
        return page.evaluate(() => $('.consentedVisitsOnlyNote:visible').length > 0);
    }

    it('should let a site under the CNIL policy be searched by user id', async function () {
        await loadGdprTools();
        await selectSite(policySite);

        expect(await page.$('.dataUnavailable')).to.equal(null);
        expect(await page.$('.findDataSubjects')).to.not.equal(null);
        expect(await segmentDefinition()).to.contain('User ID');
    });

    it('should only find consented visits on a site under the CNIL policy and say why', async function () {
        await loadGdprTools();
        await selectSite(policySite);
        await findVisitsOfUserIdsStartingWith('userId');

        const cells = await consentCells();
        expect(cells.length).to.be.greaterThan(0);
        cells.forEach((cell) => expect(cell).to.equal('Consented'));

        expect(await page.evaluate(() => $('.manageGdpr thead th.consent').text().trim())).to.equal('Consent state');
        // the visitor profile of the site is not available, so no row links to it
        expect(await page.evaluate(() => $('.manageGdpr tbody .visitorLogTooltip').length)).to.equal(0);
        await page.waitForSelector('.consentedVisitsOnlyNote', { visible: true });
        expect(await isConsentedVisitsOnlyNoteShown()).to.equal(true);
        expect(await page.evaluate(() => $('.consentedVisitsOnlyNote').text().trim())).to.equal(
            'Only consented visits can be found on this website. Visits tracked without consent hold no identifier'
            + ' that could link them to a person, so they fall outside access and erasure requests.'
        );
    });

    it('should find every visit with its consent state on a site without policy', async function () {
        await loadGdprTools();
        await selectSite('1');
        await findVisitsOfUserIdsStartingWith('userId');

        const cells = await consentCells();
        expect(cells).to.include('Consented');
        expect(cells).to.include('Refused');
        expect(cells).to.include('');
        expect(await isConsentedVisitsOnlyNoteShown()).to.equal(false);
        expect(await page.evaluate(() => $('.manageGdpr tbody .visitorLogTooltip').length)).to.be.greaterThan(0);
    });
});
