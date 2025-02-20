/*!
 * Matomo - free/libre analytics platform
 *
 * SitesManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SitesManager", function () {
    const parentSuite = this;

    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\SitesManager\\tests\\Fixtures\\ManySites";

    const url = "?module=SitesManager&action=index&idSite=1&period=day&date=yesterday&showaddsite=false";

    async function assertScreenshotEquals(screenshotName, test, selectorToWaitFor = ".enrichedHeadline:contains(Manage Measurables)")
    {
        await test();
        await page.waitForNetworkIdle();
        const pageWrap = await page.$('#content');
        await page.waitForFunction((s) => {
          return !!$(s).length;
        }, {}, selectorToWaitFor);
        expect(await pageWrap.screenshot()).to.matchImage(screenshotName);
    }

    async function loadNextPage()
    {
        await (await page.jQuery('.SitesManager .paging:first .next')).click();
    }

    async function loadPreviousPage()
    {
        await (await page.jQuery('.SitesManager .paging:first .prev')).click();
    }

    async function searchForText(textToAppendToSearchField)
    {
        await (await page.jQuery('.SitesManager .search:first input')).type(textToAppendToSearchField);
        await page.waitForTimeout(100);
        await (await page.jQuery('.SitesManager .search:first .search_ico')).click();
    }

    it("should load correctly and show page 0", async function() {
        await assertScreenshotEquals("loaded", async function () {
            await page.goto(url);
        });
    });

    it("should show page 1 when clicking next", async function() {
        await assertScreenshotEquals("page_1", async function () {
            await loadNextPage();
        });
    });

    it("should show page 2 when clicking next", async function() {
        await assertScreenshotEquals("page_2", async function () {
            await loadNextPage();
        });
    });

    it("should show page 1 when clicking prev", async function() {
        await assertScreenshotEquals("page_1_again", async function () {
            await loadPreviousPage();
        });
    });

    it("should search for websites and reset page to 0", async function() {
        await assertScreenshotEquals("search", async function () {
            await searchForText('SiteTes');
        });
    });

    it("should page within search result to page 1", async function() {
        await assertScreenshotEquals("search_page_1", async function () {
            await loadNextPage();
        });
    });

    it("should search for websites no result", async function() {
        await assertScreenshotEquals("search_no_result", async function () {
            await searchForText('RanDoMSearChTerm');
        });
    });

    it("should load the global settings page", async function() {
        await assertScreenshotEquals("global_settings", async function () {
            await page.goto('?module=SitesManager&action=globalSettings&idSite=1&period=day&date=yesterday&showaddsite=false');
            await page.evaluate(function () {
                $('.form-help:contains(UTC time is)').hide();
            });
        }, "h2:contains(Global websites settings)");
    });

    it("should be able to open and edit a site directly based on url parameter", async function() {
        await assertScreenshotEquals("site_edit_url", async function () {
            await page.goto(url + '#/editsiteid=23');
            await page.evaluate(function () {
                $('.form-help:contains(UTC time is)').hide();
            });
        });
    });

    describe('the UI should change depending on what exclusion type gets selected', async function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        async function assertExcludedParametersScreenshot(screenshotName) {
            await expect(
              await page.screenshotSelector('.siteManagerGlobalExcludedUrlParameters')
            ).to.matchImage(screenshotName);
        }

        it('excludes common session parameters by default', async function () {
            await page.goto('?module=SitesManager&action=globalSettings');
            await assertExcludedParametersScreenshot('global_url_param_exclusion_default')
        });

        it('excludes recommended parameters if chosen', async function () {
            await page.click('#exclusionTypematomo_recommended_pii');
            await assertExcludedParametersScreenshot('global_url_param_exclusion_common_exclusions');
        });

        it('excludes a custom list of parameters if chosen', async function () {
            await page.click('#exclusionTypecustom');
            await assertExcludedParametersScreenshot('global_url_param_exclusion_custom');
        });

        it('can add recommended parameters to custom list', async function () {
            await page.click('.siteManagerGlobalExcludedUrlParameters input[type=button]');
            await assertExcludedParametersScreenshot('global_url_param_exclusion_add_common');
        });

        it('resets custom list of parameters when changing type', async function () {
            await page.click('#exclusionTypematomo_recommended_pii');
            await page.click('#exclusionTypecustom');

            // screenshot should be identical to first selection of "custom"
            await assertExcludedParametersScreenshot('global_url_param_exclusion_custom');
        });
    });
});
