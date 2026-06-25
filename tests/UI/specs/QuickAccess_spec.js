/*!
 * Matomo - free/libre analytics platform
 *
 * ActionsDataTable screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("QuickAccess", function () {
    const selectorToCapture = ".quick-access,.quick-access .dropdown";
    const url = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09";
    const pagesResultSelector = '.quick-access .result a';

    async function enterSearchTerm(searchTermToAdd) {
        await page.evaluate(function () {
            $('.quick-access input').val('');
        });

        await page.focus(".quick-access input");
        await page.keyboard.type(searchTermToAdd);
        await page.waitForTimeout(100);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(100);

        await page.evaluate(function () {
            $('.quick-access input').blur();
        });
    }

    async function searchForPages() {
        await page.focus('.quick-access input');
        await page.keyboard.type('page');
        await page.waitForTimeout(200);
        await page.waitForFunction(
            (selector) => Array.from(document.querySelectorAll(selector)).some((element) => element.textContent.includes('Pages')),
            {},
            pagesResultSelector,
        );
    }

    it("should be displayed", async function () {
        await page.goto(url);
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('initially');
    });

    it('should display a styled tooltip on hover', async function () {
        await page.hover('.quick-access input');
        const content = await page.waitForFunction(() => $('.ui-tooltip:visible').text());
        expect(content).to.match(/Search for Menu entries, Segments, and Websites\. Use the arrow keys to navigate through search results\. Shortcut: Press 'f' to search\./);
    });

    it("should search for something and update view", async function () {
        await page.mouse.move(0,0);
        await enterSearchTerm('s');
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_1');
    });

    it("should search again when typing another letter", async function () {
        await enterSearchTerm('as');
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_2');
    });

    it("should show message if no results", async function () {
        await enterSearchTerm('alaskdjfs');
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_no_result');
    });

    it("should be possible to activate via shortcut", async function () {
        await page.goto(url);
        await page.focus('body');
        await page.keyboard.type('f');

        await page.evaluate(function () {
            $('.quick-access input').blur();
        });

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('shortcut');
    });

    it("should search for websites", async function () {
        await enterSearchTerm('si');
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_sites');
    });

    it("clicking on a category should show all items that belong to that category", async function () {
        const element = await page.jQuery('.quick-access-category:first');
        await element.click();
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_category');
    });

    it("should close the mobile side menu when selecting a search result", async function () {
        await page.webpage.setViewport({ width: 768, height: 1200 });
        await page.goto('?module=CoreHome&action=index&idSite=1&period=year&date=2009-01-04#?idSite=1&period=year&date=2009-01-04&category=General_Visitors&subcategory=General_Overview');
        await page.waitForNetworkIdle();

        await page.evaluate(function () {
            $('.activateLeftMenu>span').click();
        });
        await page.waitForFunction(() => $('#secondNavBar').hasClass('mobileLeftMenuOpen'));

        await searchForPages();
        await page.click(pagesResultSelector);

        await page.waitForFunction(() => !$('#secondNavBar').hasClass('mobileLeftMenuOpen'));
        await page.waitForFunction(() => window.location.href.includes('subcategory=General_Pages'));
    });
    it("should clear the active state when tabbing away", async function () {
      await page.goto(url);
      await searchForPages();

      await page.keyboard.press('Tab');

      await page.waitForFunction(() => !$('.quick-access').hasClass('active'));
      await page.waitForFunction(() => !$('.quick-access .dropdown').is(':visible'));
    });

    it("should navigate when clicking a search result", async function () {
      await page.goto(url);
      await searchForPages();

      await page.click(pagesResultSelector);

      await page.waitForFunction(() => window.location.href.includes('subcategory=General_Pages'));
    });

    it("should clear the search field when switching reporting section", async function () {
      // Regression: switching section is a hash-only change, so this component stays mounted and used
      // to keep whatever was typed before the switch (inconsistent with the full-reload direction).
      await page.goto(url + '#?idSite=1&period=year&date=2012-08-09&category=General_Visitors&subcategory=General_Overview');
      await page.waitForSelector('#secondNavBar', { visible: true });
      await page.waitForNetworkIdle();

      await enterSearchTerm('visitor');
      await page.waitForFunction((selector) => $(selector).length > 0, {}, pagesResultSelector);

      await page.evaluate(() => {
          window.location.hash = '#?idSite=1&period=year&date=2012-08-09&group=CoreHome_AIInsights';
      });
      await page.waitForNetworkIdle();
      await page.waitForTimeout(500);

      const searchValue = await page.evaluate(() => document.querySelector('.quick-access input').value);
      expect(searchValue).to.equal('');
    });

    it("should not show duplicate results after switching reporting section", async function () {
      // Regression: switching section (e.g. Analytics -> AI Insights) is a hash-only change, so this
      // component stays mounted. Its scraped left-menu cache used to survive the switch and surface
      // the previous section's (now removed) menu entries as duplicate, unclickable results next to
      // the live cross-section ones pulled from the reporting menu store. Starting from a reporting
      // page that already has a hash is important so switching section is a pure hash change (no full
      // page reload that would remount this component and reset the cache on its own).
      await page.goto(url + '#?idSite=1&period=year&date=2012-08-09&category=General_Visitors&subcategory=General_Overview');
      await page.waitForSelector('#secondNavBar', { visible: true });
      await page.waitForNetworkIdle();

      const hasResults = (selector) => $(selector).length > 0;

      // prime the quick search menu cache while in the default (Analytics) section
      await enterSearchTerm('visitor');
      await page.waitForFunction(hasResults, {}, pagesResultSelector);

      // switch to the AI Insights section via a pure hash change, the way the top-menu entry does it
      // in the app (the section lives in the URL hash, so this does not reload the page / remount the
      // component - which is exactly the condition that exposed the stale-cache duplicates)
      await page.evaluate(() => {
          window.location.hash = '#?idSite=1&period=year&date=2012-08-09&group=CoreHome_AIInsights';
      });
      await page.waitForNetworkIdle();
      await page.waitForTimeout(500);

      // clear the field (resetting the bound search term) so the next search starts fresh
      await page.evaluate(() => {
          const input = document.querySelector('.quick-access input');
          input.value = '';
          input.dispatchEvent(new Event('input', { bubbles: true }));
      });

      // search again for an entry that only exists in the Analytics section
      await page.focus('.quick-access input');
      await page.keyboard.type('visitor');
      await page.waitForFunction(hasResults, {}, pagesResultSelector);

      // the stale cross-section entries only surfaced once the switched-to section had finished
      // loading, so let it settle before asserting there are no duplicates
      await page.waitForNetworkIdle();
      await page.waitForTimeout(1000);

      const resultTexts = await page.evaluate((selector) => Array.from(
          document.querySelectorAll(selector)
      ).map((el) => el.textContent.trim()), pagesResultSelector);

      expect(resultTexts.length).to.be.above(0);
      expect(resultTexts.length).to.equal(new Set(resultTexts).size);
    });
});
