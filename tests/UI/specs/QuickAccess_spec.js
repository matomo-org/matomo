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

    // Switches to the AI Insights section the way its top-menu entry does (hash-only, no reload) and
    // waits for the SPA to finish switching: it rewrites the hash with the section's first page (the
    // AI Assistants category) once that section's menu is ready, then the report loads (network idle).
    // Waiting for this deterministic state - rather than a fixed sleep - means the result set is
    // already complete when the caller searches and asserts.
    async function switchToAiInsightsSection() {
        await page.evaluate(() => {
            window.location.hash = '#?idSite=1&period=year&date=2012-08-09&group=CoreHome_AIInsights';
        });
        await page.waitForFunction(() => window.location.href.includes('category=General_AIAssistants'));
        await page.waitForNetworkIdle();
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
      // Switching section only changes the hash (no remount), so the typed text used to persist here.
      await page.goto(url + '#?idSite=1&period=year&date=2012-08-09&category=General_Visitors&subcategory=General_Overview');
      await page.waitForSelector('#secondNavBar', { visible: true });
      await page.waitForNetworkIdle();

      await enterSearchTerm('visitor');
      await page.waitForFunction((selector) => $(selector).length > 0, {}, pagesResultSelector);

      await switchToAiInsightsSection();

      const searchValue = await page.evaluate(() => document.querySelector('.quick-access input').value);
      expect(searchValue).to.equal('');
    });

    it("should not show duplicate results after switching reporting section", async function () {
      // Regression: a hash-only section switch left this component's scraped menu cache pointed at the
      // previous section, duplicating its entries. Start from a hashed URL so the switch stays hash-only
      // (a full reload would remount the component and hide the bug).
      await page.goto(url + '#?idSite=1&period=year&date=2012-08-09&category=General_Visitors&subcategory=General_Overview');
      await page.waitForSelector('#secondNavBar', { visible: true });
      await page.waitForNetworkIdle();

      const hasResults = (selector) => $(selector).length > 0;

      // prime the quick search menu cache while in the default (Analytics) section
      await enterSearchTerm('visitor');
      await page.waitForFunction(hasResults, {}, pagesResultSelector);

      // switch sections and wait for the new section to fully load before searching, so the result
      // set is already complete when asserted (a stale cache surfaces the previous section's entries
      // alongside the live cross-section ones)
      await switchToAiInsightsSection();

      // The dropdown still holds the pre-switch search results, and searchMenu is debounced, so force
      // a known-empty state first (a non-matching term, wait for zero results); otherwise the assertion
      // below could read the stale results before the post-switch search has re-run.
      await page.focus('.quick-access input');
      await page.keyboard.type('zzqqxxnomatch');
      await page.waitForFunction((selector) => $(selector).length === 0, {}, pagesResultSelector);

      // now search for an entry that only exists in the Analytics section
      await page.evaluate(() => {
          const input = document.querySelector('.quick-access input');
          input.value = '';
          input.dispatchEvent(new Event('input', { bubbles: true }));
      });
      await page.keyboard.type('visitor');
      await page.waitForFunction(hasResults, {}, pagesResultSelector);

      // menu results only (exclude the website results that share the same .result class)
      const resultTexts = await page.evaluate((selector) => Array.from(document.querySelectorAll(selector))
          .filter((a) => !a.closest('.quickAccessMatomoSearch'))
          .map((el) => el.textContent.trim()), pagesResultSelector);

      expect(resultTexts.length).to.be.above(0);
      expect(resultTexts.length).to.equal(new Set(resultTexts).size);
    });
});
