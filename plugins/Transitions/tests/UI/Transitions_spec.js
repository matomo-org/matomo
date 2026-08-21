/*!
 * Matomo - free/libre analytics platform
 *
 * transitions screenshot tests
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Transitions", function () {
    var generalParams = 'idSite=1&period=year&date=2012-08-09',
        urlBase = 'module=CoreHome&action=index&' + generalParams;

    async function setThemeMode(themeMode)
    {
        await page.evaluate((mode) => {
            window.piwik.setThemeMode(mode);
        }, themeMode);
        await page.waitForFunction((mode) => window.piwik.getThemeMode() === mode, {}, themeMode);
    }

    /**
     * The row highlight and the ribbon emphasis both animate over .15s, so a capture taken right
     * after a hover can land mid-transition. The end state is what these tests assert on.
     */
    async function freezeTransitions()
    {
        await page.evaluate(() => {
            const style = document.createElement('style');
            style.textContent
                = '.transitionsRow, .transitionsRibbons__band { transition: none !important; }';
            document.head.appendChild(style);
        });
    }

    /** The ribbon layer measures the rows in an animation frame after they render. */
    async function waitForRibbons()
    {
        await page.waitForFunction(() => {
            const rows = document.querySelectorAll('[data-ribbon-key]').length;
            const bands = document.querySelectorAll('.transitionsRibbons__band').length;
            return rows > 0 && rows === bands;
        });
    }

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

        await (await page.jQuery('div.dataTable tbody tr:contains("Space Quest")')).hover();
        await (await page.jQuery('a.actionTransitions:visible')).hover(); // necessary to get popover to display
        await (await page.jQuery('a.actionTransitions:visible')).click();

        await page.waitForNetworkIdle();
        await page.waitForSelector('.ui-dialog', { visible: true });
        await page.waitForSelector('.ui-dialog .transitionsCenterCard', { visible: true });
        await waitForRibbons();

        expect(await page.screenshotSelector('.ui-dialog')).to.matchImage('transitions_popup_titles');
    });

    it('should load the transitions popup correctly for the page urls report', async function() {
        await page.goto('about:blank');
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages&"
                    + "popover=RowAction$3ATransitions$3Aurl$3Ahttp$3A$2F$2Fpiwik.net$2Fdocs$2Fmanage-websites$2F");
        await page.waitForNetworkIdle();

        await page.waitForSelector('.transitionsCenterCard', { visible: true });
        await waitForRibbons();
        await freezeTransitions();
        await (await page.$('.transitionsRow--outgoing')).hover();
        await page.waitForSelector('.transitionsRow--highlighted');

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
        await page.evaluate(
            () => $('.transitionsRow--summary:contains(From search engines)').click()
        );
        await page.waitForFunction(() => Array.prototype.some.call(
            document.querySelectorAll('.transitionsSection__title'),
            (title) => title.textContent.indexOf('From search engines') !== -1,
        ));
        await waitForRibbons();
        expect(await page.screenshotSelector('body')).to.matchImage('transitions_report_search_engines');
    });

    it('should show report in dark mode', async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Transitions_Transitions");
        await page.waitForNetworkIdle();
        await setThemeMode('dark');
        try {
          await page.waitForSelector('.transitionsCenterCard');
          await waitForRibbons();
          expect(await page.screenshotSelector('.pageWrap')).to.matchImage('transitions_report_dark_mode');
        } finally {
          await setThemeMode('light');
        }
    });

    it('should show period not allowed for disabled periods', async function () {

        await testEnvironment.overrideConfig('Transitions_1', 'max_period_allowed', 'day');
        await testEnvironment.save();
        try {
          await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Transitions_Transitions");
          await page.waitForNetworkIdle();
          expect(await page.screenshotSelector('.pageWrap'))
            .to
            .matchImage('transitions_report_period_not_allowed');
        } finally {
          await testEnvironment.overrideConfig('Transitions_1', 'max_period_allowed', 'all');
          await testEnvironment.save();
        }
    });

    it('should escape the export overlay title correctly', async function () {
        await page.goto("?" + urlBase + "#?idSite=1&period=day&date=2012-01-16&category=General_Actions&subcategory=Transitions_Transitions");
        await page.waitForNetworkIdle();

        await page.webpage.evaluate(() => {
            $('[name="actionName"] input.select-dropdown').click()
        });
        await page.waitForTimeout(500);
        await page.webpage.evaluate(() => {
            $('[name="actionName"] .dropdown-content li:contains("script"):last').click()
        });
        await page.waitForNetworkIdle();

        await page.click('.icon-export');
        await page.waitForTimeout(100);

        const title = await page.$('.ui-dialog-title');
        const titleText = await title.getProperty('textContent');

        expect(await titleText.jsonValue()).to.be.equal('http://example.org/<script>_x(6)</script> Transitions');
    });

    it('should keep the transitions popover open when the theme changes live', async function() {
        await page.goto('about:blank');
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Actions_SubmenuPageTitles");

        await setThemeMode('light');

        await (await page.jQuery('div.dataTable tbody tr:contains("Space Quest")')).hover();
        await (await page.jQuery('a.actionTransitions:visible')).hover(); // necessary to get popover to display
        await (await page.jQuery('a.actionTransitions:visible')).click();

        await page.waitForNetworkIdle();
        await page.waitForSelector('.transitionsCenterCard');

        await setThemeMode('dark');
        const centerCardCount = await page.evaluate(() => $('.transitionsCenterCard').length);
        expect(centerCardCount).to.be.equal(1);

        await setThemeMode('light');
    });
});
