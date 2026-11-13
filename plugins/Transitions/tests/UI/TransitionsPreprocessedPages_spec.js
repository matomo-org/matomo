/*!
 * Matomo - free/libre analytics platform
 *
 * Pre-processed Transitions: the admin page, and the source badge and toggle in the report.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("TransitionsPreprocessedPages", function () {
    this.fixture = "Piwik\\Plugins\\Transitions\\tests\\Fixtures\\PreprocessedPages";

    // cron archiving, the main setup: an added page gets its past months rebuilt. The fixture has
    // archived what the reports below read; the raw-data page is served from the logs anyway.
    before(function () {
        testEnvironment.overrideConfig('General', 'enable_browser_archiving_triggering', 0);
        testEnvironment.optionsOverride = {
            enableBrowserTriggerArchiving: '0',
        };
        testEnvironment.save();
    });

    after(function () {
        delete testEnvironment.configOverride.General.enable_browser_archiving_triggering;
        testEnvironment.optionsOverride = {};
        testEnvironment.save();
    });

    var generalParams = 'idSite=1&period=day&date=2010-03-06',
        manageUrl = '?module=Transitions&action=manage&' + generalParams,
        reportUrl = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams
            + '&category=General_Actions&subcategory=Transitions_Transitions';

    async function rowUrls()
    {
        return page.evaluate(() => Array.prototype.map.call(
            document.querySelectorAll('.managePreprocessedPages__url'),
            (cell) => cell.textContent.trim(),
        ));
    }

    async function textOf(selector)
    {
        return page.evaluate((selector) => {
            const element = document.querySelector(selector);
            return element ? element.textContent.trim() : null;
        }, selector);
    }

    async function waitForText(selector, text)
    {
        await page.waitForFunction((selector, text) => {
            const element = document.querySelector(selector);
            return !!element && element.textContent.indexOf(text) !== -1;
        }, {}, selector, text);
    }

    async function typePageUrl(url)
    {
        await page.focus('.managePreprocessedPages__field input');
        await page.evaluate(() => {
            document.querySelector('.managePreprocessedPages__field input').select();
        });
        await page.keyboard.press('Backspace');
        await page.type('.managePreprocessedPages__field input', url);
        await page.waitForFunction(() => !document.querySelector('.managePreprocessedPages__add').disabled);
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

    async function selectActionName(title)
    {
        await page.webpage.evaluate(() => {
            $('[name="actionName"] input.select-dropdown').click();
        });
        await page.waitForSelector('[name="actionName"] .dropdown-content', { visible: true });
        await page.webpage.evaluate((title) => {
            $('[name="actionName"] .dropdown-content li:contains("' + title + '"):first').click();
        }, title);
    }

    it('should list the selected pages on the admin page', async function () {
        await page.goto(manageUrl);
        await page.waitForNetworkIdle();
        await page.waitForSelector('.managePreprocessedPages__row');

        const menuEntries = await page.evaluate(() => $('a[href*="module=Transitions"][href*="action=manage"]')
            .filter((index, link) => $(link).text().indexOf('Pre-processed Transitions') !== -1).length);
        expect(menuEntries).to.be.above(0);

        expect(await rowUrls()).to.deep.equal([
            'http://example.org/page/one.html',
            'http://example.org/sub/dir/page2.html',
        ]);
        expect(await textOf('.managePreprocessedPages__unknownUser')).to.equal('Unknown');

        // no row or button under the mouse, so no hover state in the capture
        await page.mouse.move(-10, -10);
        expect(await page.screenshotSelector('.managePreprocessedPages')).to.matchImage('admin_page');
    });

    it('should add a page and refuse the same page again', async function () {
        await typePageUrl('http://example.org/page3.html');
        await page.click('.managePreprocessedPages__add');
        await page.waitForFunction(() => document.querySelectorAll('.managePreprocessedPages__row').length === 3);

        expect((await rowUrls())[0]).to.equal('http://example.org/page3.html');
        expect(await textOf('.managePreprocessedPages__usageLabel')).to.equal('3 of 20 pages selected');

        await typePageUrl('https://www.example.org/page3.html');
        await page.click('.managePreprocessedPages__add');
        await page.waitForSelector('.managePreprocessedPages__duplicateHelp');

        expect(await textOf('.managePreprocessedPages__addError .managePreprocessedPages__error'))
            .to.equal('This page is already selected.');
        expect((await rowUrls()).length).to.equal(3);
    });

    it('should remove a page after the inline confirmation', async function () {
        await page.evaluate(() => {
            $('.managePreprocessedPages__row:contains("page3.html") .managePreprocessedPages__remove').click();
        });
        await page.waitForSelector('.managePreprocessedPages__confirm');
        expect(await textOf('.managePreprocessedPages__confirmText'))
            .to.contain('Stop pre-processing example.org/page3.html?');

        await page.click('.managePreprocessedPages__stop');
        await page.waitForFunction(() => document.querySelectorAll('.managePreprocessedPages__row').length === 2);

        expect(await rowUrls()).to.deep.equal([
            'http://example.org/page/one.html',
            'http://example.org/sub/dir/page2.html',
        ]);
    });

    it('should fit a phone screen with the remove confirmation open', async function () {
        await page.webpage.setViewport({ width: 375, height: 812 });

        try {
            await page.goto(manageUrl);
            await page.waitForNetworkIdle();
            await page.waitForSelector('.managePreprocessedPages__row');

            await page.click('.managePreprocessedPages__remove');
            await page.waitForSelector('.managePreprocessedPages__confirm');

            const layout = await page.evaluate(() => {
                const isVisible = (selector) => {
                    const element = document.querySelector(selector);
                    if (!element) {
                        return false;
                    }

                    const rect = element.getBoundingClientRect();
                    const style = window.getComputedStyle(element);
                    return rect.width > 0 && rect.height > 0 && style.visibility !== 'hidden'
                        && rect.left >= 0 && rect.right <= window.innerWidth;
                };

                return {
                    scrollWidth: document.documentElement.scrollWidth,
                    innerWidth: window.innerWidth,
                    cancelVisible: isVisible('.managePreprocessedPages__cancel'),
                    stopVisible: isVisible('.managePreprocessedPages__stop'),
                };
            });

            expect(layout.scrollWidth).to.be.at.most(layout.innerWidth);
            expect(layout.cancelVisible).to.equal(true);
            expect(layout.stopVisible).to.equal(true);

            // no row or button under the mouse, so no hover state in the capture
            await page.mouse.move(-10, -10);
            expect(await page.screenshotSelector('.managePreprocessedPages')).to.matchImage('admin_page_phone');
        } finally {
            await page.webpage.setViewport({ width: 1350, height: 768 });
        }
    });

    it('should show the pre-processed badge in the report of an archived page', async function () {
        await page.goto(reportUrl);
        await page.waitForNetworkIdle();
        await waitForRibbons();
        await page.waitForSelector('.transitionsSourceHeader__toggle');

        expect(await textOf('.transitionsSourceHeader__badge')).to.equal('Pre-processed');
        expect(await textOf('.transitionsSourceHeader__toggle')).to.contain('Stop pre-processing');

        // no row or button under the mouse, so no hover state in the capture
        await page.mouse.move(-10, -10);
        expect(await page.screenshotSelector('.transitionsSourceHeader')).to.matchImage('report_header');
    });

    it('should let an admin pre-process a page served from raw data', async function () {
        await selectActionName('page3.html');
        await page.waitForNetworkIdle();
        await waitForRibbons();
        await waitForText('.transitionsSourceHeader__toggle', 'Pre-process this page');

        expect(await textOf('.transitionsSourceHeader__badge')).to.equal('Raw data');
        expect(await textOf('.transitionsSourceHeader__usage')).to.equal('2 of 20 used');

        await page.click('.transitionsSourceHeader__toggle');
        await waitForText('.transitionsSourceHeader__toggle', 'Stop pre-processing');
        expect(await textOf('.transitionsSourceHeader__message')).to.contain('Data for the past 6 months');
        expect(await textOf('.transitionsSourceHeader__badge')).to.equal('Raw data');

        await page.click('.transitionsSourceHeader__toggle');
        await page.waitForSelector('.transitionsSourceHeader__confirm');
        await page.click('.transitionsSourceHeader__stop');
        await waitForText('.transitionsSourceHeader__toggle', 'Pre-process this page');
        expect(await textOf('.transitionsSourceHeader__usage')).to.equal('2 of 20 used');
    });
});
