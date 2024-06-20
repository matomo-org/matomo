/*!
 * Matomo - free/libre analytics platform
 *
 * Opt-out form tests
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('OptOutJS', function () {
    const parentSuite = this;

    const siteUrl = '/tests/resources/overlay-test-site-real/opt-out.php?implementation=js';

    async function expectHasConsentToBe(useTracker, expectedState) {
        let hasConsent;

        if (useTracker) {
            hasConsent = await page.evaluate(() => window.Matomo.getTracker().hasConsent());
        } else {
            hasConsent = await page.evaluate(() => !!window.MatomoConsent.hasConsent());
        }

        expect(hasConsent).to.equal(expectedState);
    }

    async function hideHTTPSWarning() {
        await page.evaluate(() => $('#matomo-opt-out p:contains("not loaded over HTTPS")').hide());
    }

    async function screenshotOptOut(screenshotName) {
        expect(await page.screenshotSelector('#matomo-opt-out')).to.matchImage(screenshotName);
    }

    [true, false].forEach(function (loadTracker) {
        const optOutUrl = siteUrl + '&loadTracker=' + (loadTracker ? '1' : '0');
        const testSuffix = loadTracker ? ' (with tracker)' : ' (without tracker)';

        it('should display correctly when integrated' + testSuffix, async function () {
            await page.clearCookies();
            await page.goto(optOutUrl);
            await page.waitForSelector('#trackVisits');
            await hideHTTPSWarning();

            await expectHasConsentToBe(loadTracker, true);

            if (loadTracker) {
                await screenshotOptOut('loaded');
            }
        });

        it('should register consent withdrawal and display an empty checkbox' + testSuffix, async function () {
            await page.click('#trackVisits');
            await hideHTTPSWarning();

            await expectHasConsentToBe(loadTracker, false);

            if (loadTracker) {
                await screenshotOptOut('opted-out');
            }
        });

        it('should correctly show the checkbox unchecked after reloading after opting-out' + testSuffix, async function () {
            await page.goto(optOutUrl);
            await page.waitForSelector('#trackVisits');
            await hideHTTPSWarning();

            await expectHasConsentToBe(loadTracker, false);
        });

        it('should allow granting consent again and display a filled checkbox' + testSuffix, async function () {
            await page.click('#trackVisits');
            await hideHTTPSWarning();

            await expectHasConsentToBe(loadTracker, true);

            if (loadTracker) {
                await screenshotOptOut('opted-in');
            }
        });

        it('should work correctly when using opt out twice' + testSuffix, async function () {
            await page.clearCookies();
            await page.goto(optOutUrl);
            await page.waitForSelector('#trackVisits');
            await hideHTTPSWarning();

            await expectHasConsentToBe(loadTracker, true);

            await page.click('#trackVisits');
            await hideHTTPSWarning();
            await expectHasConsentToBe(loadTracker, false);

            await page.click('#trackVisits');
            await hideHTTPSWarning();
            await expectHasConsentToBe(loadTracker, true);

            if (loadTracker) {
                await screenshotOptOut('clicked_twice');
            }
        });
    });

    it('should show a warning if the divId is missing', async function () {
        await page.goto(siteUrl + '&divId=missingDivId');
        await page.waitForSelector('#missingDivId-warning');
        await page.evaluate(() => $('#missingDivId-warning').width(640));

        expect(await page.screenshotSelector('#missingDivId-warning')).to.matchImage('missing-divId');
    });

    describe('with disabled browser cookies', function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        before(async function (){
            await page.webpage._client.send('Emulation.setDocumentCookieDisabled', {'disabled': true});
        });

        after(async function () {
            await page.webpage._client.send('Emulation.setDocumentCookieDisabled', {'disabled': false});
        });

        it('should show a warning', async function () {
            await page.goto(siteUrl);
            await page.waitForSelector('#matomo-opt-out p');

            await screenshotOptOut('cookies-disabled');
        });
    })
});
