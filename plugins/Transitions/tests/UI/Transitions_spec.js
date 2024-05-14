/*!
 * Matomo - free/libre analytics platform
 *
 * transitions screenshot tests
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Transitions", function () {
    this.timeout(0);

    var generalParams = 'idSite=1&period=year&date=2012-08-09',
        urlBase = 'module=CoreHome&action=index&' + generalParams;

    async function selectValue(field, title)
    {
        await page.webpage.evaluate((field) => {
            $(field + ' input.select-dropdown').click()
        }, field);
        await page.waitForTimeout(500);
        await page.webpage.evaluate((field, title) => {
            $(field + ' .dropdown-content li:contains("' + title + '"):first').click()
        }, field, title);
    }

    it('should load the transitions popup correctly for the page titles report', async function() {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Actions_SubmenuPageTitles");

        await (await page.jQuery('div.dataTable tbody tr:contains("Sapce Quest")')).hover();
        await (await page.jQuery('a.actionTransitions:visible')).hover(); // necessary to get popover to display
        await (await page.jQuery('a.actionTransitions:visible')).click();

        await page.waitForNetworkIdle();
        await page.waitForSelector('.ui-dialog', { visible: true });

        expect(await page.screenshotSelector('.ui-dialog')).to.matchImage('transitions_popup_titles');
    });

    it('should load the transitions popup correctly for the page urls report', async function() {
        await page.goto('about:blank');
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages&"
                    + "popover=RowAction$3ATransitions$3Aurl$3Ahttp$3A$2F$2Fpiwik.net$2Fdocs$2Fmanage-websites$2F");
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);

        // for some reason the tooltip isn't shown on the screenshot (even if the whole page is taken)
        // but it seems to be placed in the HTML code so, we check for it's contents
        await (await page.$('.Transitions_CurveTextRight')).hover();
        await page.waitForSelector('.ui-tooltip');
        const toolTipHtml = await page.evaluate(() => $('.ui-tooltip:visible').html());
        expect(toolTipHtml).to.equal('<div class="ui-tooltip-content"><strong>4 (out of 4)</strong> to internal pages</div>');

        expect(await page.screenshotSelector('.ui-dialog')).to.matchImage('transitions_popup_urls');
    });

    it('should show no data message in selector', async function () {
        await page.goto("?module=Widgetize&action=iframe&widget=1&moduleToWidgetize=Transitions&actionToWidgetize=getTransitions&idSite=1&period=day&date=today&disableLink=1&widget=1");
        expect(await page.screenshotSelector('body')).to.matchImage('transitions_report_no_data_widget');
    });

    it('should show report in reporting ui with data', async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Transitions_Transitions");
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pageWrap')).to.matchImage('transitions_report_with_data_report');
    });

    it('should show report in widget ui in selector', async function () {
        await page.goto("?module=Widgetize&action=iframe&widget=1&moduleToWidgetize=Transitions&actionToWidgetize=getTransitions&"+generalParams+"&disableLink=1&widget=1");
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('body')).to.matchImage('transitions_report_with_data_widget');
    });

    it('should be possible to switch report', async function () {
        await selectValue('[name="actionName"]', 'category/meta');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('body')).to.matchImage('transitions_report_switch_url');
    });

    it('should be possible to show page titles', async function () {
        await selectValue('[name="actionType"]', 'Title');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('body')).to.matchImage('transitions_report_switch_type_title');
    });

    it('should show the search engines when clicked', async function () {
        await page.evaluate(() => $('.Transitions_SingleLine:contains(From search engines)').click());
        expect(await page.screenshotSelector('body')).to.matchImage('transitions_report_search_engines');
    });

    it('should show period not allowed for disabled periods', async function () {

        testEnvironment.overrideConfig('Transitions_1', 'max_period_allowed', 'day');
        testEnvironment.save();

        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Transitions_Transitions");
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pageWrap')).to.matchImage('transitions_report_period_not_allowed');

        testEnvironment.overrideConfig('Transitions_1', 'max_period_allowed', 'all');
        testEnvironment.save();
    });
});
