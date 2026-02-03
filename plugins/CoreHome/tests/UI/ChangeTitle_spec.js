/*!
 * Matomo - free/libre analytics platform
 *
 * ChangeTitle UI tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ChangeTitle', function () {
  const generalParams = '?module=CoreHome&action=index&idSite=1&period=day&date=2025-11-11';
  let url = generalParams+ "&category=Dashboard_Dashboard&subcategory=1";

  /**
   * Get the query parameters from a URL string.
   * We will use this to create the expected title
   * @param urlString
   * @returns {{category, subcategory, date, segment}}
   */
  function extractFilters(urlString) {
    const url = new URL(urlString);

    // base query params
    const params = url.searchParams;
    // Matomo sometimes stores params in the hash (`#?foo=bar`), so merge those too.
    if (url.hash && url.hash.startsWith('#?')) {
      for (const [key, value] of new URLSearchParams(url.hash.slice(2))) {
        params.set(key, value); // prefer hash values when present
      }
    }
    return {
      category: params.get('category') || '',
      subcategory: params.get('subcategory') || '',
      date: params.get('date') || '',
      segment: params.get('segment') || ''
    };
  }

  /**
   * Adding a mapping of category and subcategory ids to their names.
   * We just fake/hard-code the names for expected titles.
   * @param categoryId
   * @param subCategoryId
   * @returns {{categoryName: string, subcategoryName: string}}
   */
  function getActiveMenuNames(categoryId, subCategoryId) {
    let catNameMap = {
      'Dashboard_Dashboard': 'Dashboard',
      'General_Visitors': 'Visitors',
      'General_Actions': 'Behaviour',
    };
    let subCatNameMap = {
      '1': 'D4',
      'General_Overview': 'Overview ',
      'Actions_SubmenuPagesEntry' : 'Entry pages',
      'UserCountryMap_RealTimeMap': 'Real-time Map',
    };
    let catName = '' , subCatName = '';
    if (categoryId !=='') {
      catName = catNameMap[categoryId];
    }
    if (subCategoryId !=='') {
      subCatName = subCatNameMap[subCategoryId];
    }
    return {categoryName: catName, subcategoryName: subCatName};
  }

  /**
   * Adding a mapping of segment ids to their names.
   * We just fake/hard-code the names for expected titles.
   * @param segmentId
   * @returns {string}
   */
  function getSegment(segmentId) {
    if (!segmentId || segmentId === '') {
      return ' - All visits';
    }
    const segments = {
      'browserCode==FF': '<script>_x(50)</script>',
      'continentCode==eur': 'From Europe {{_Vue.h.constructor`_x(51)`()}}',
      'actions>=2' : 'Multiple actions',
    };
    return ` - ${segments[segmentId]}`;
  }
  async function escapeHtml(page, str) {
    return await page.evaluate((raw) => window.piwikHelper.htmlEntities(raw), str);
  }

  /**
   * This will try to create the expected title based on what we get from the URL.
   * @param catId
   * @param subCatId
   * @param date
   * @param segment
   * @returns {Promise<string>}
   */
  async function makeTitle(catId, subCatId, date, segment) {
    let titleSuffix = 'Web Analytics Reports - Matomo';
    let {categoryName, subcategoryName} = getActiveMenuNames(catId, subCatId);
    categoryName = await escapeHtml(page, categoryName ?? '');
    subcategoryName = await escapeHtml(page, subcategoryName ?? '');
    const siteName = await page.evaluate(() => window.piwik.siteName);
    if (categoryName === subcategoryName) {
      subcategoryName = null;
    }
    const catTitle = subcategoryName ? ` - ${categoryName} > ${subcategoryName}` : categoryName;
    segment = await getSegment(segment);
    segment = await escapeHtml(page, segment);
    return `${siteName} - ${date}${catTitle}${segment} - ${titleSuffix}`;
  }

  it ('should show the default title', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();
    await page.waitForSelector('#dashboard', {visible: true});
    const { category, subcategory, date, segment } = extractFilters(page.url());
    let currentTitle = await page.title();
    let title = await makeTitle(category, subcategory, date, segment);
    expect(currentTitle).to.equal(title);
  });

  /** We just go through some known categories and subcategories to make sure the title changes accordingly. */
  it ('should change the title based on new category and subcategory when clicking pages', async function () {
    await page.click('div.reportingMenu ul.navbar li[data-category-id="General_Actions"]');
    await page.waitForNetworkIdle();
    await page.click('div.reportingMenu ul.navbar li[data-category-id="General_Actions"] ul li:nth-of-type(2)');
    await page.waitForNetworkIdle();
    let { category, subcategory, date, segment } = extractFilters(page.url());
    let title = await makeTitle(category, subcategory, date, segment);
    let currentTitle = await page.title();
    expect(currentTitle).to.equal(title);

    await page.click('div.reportingMenu ul.navbar li[data-category-id="General_Visitors"]');
    await page.waitForNetworkIdle();
    await page.click('div.reportingMenu ul.navbar li[data-category-id="General_Visitors"] ul li:nth-of-type(4)');
    await page.waitForNetworkIdle();
    let { category: category2, subcategory: subcategory2, date: date2, segment: segment2 } = extractFilters(page.url());
    title = await makeTitle(category2, subcategory2, date2, segment2);
    currentTitle = await page.title();
    expect(currentTitle).to.equal(title);
  });

  it ('should change the title if date changes', async function () {
    let newdate = '2025-04-15';
    await page.goto(`${url}#?date=${newdate}`);
    await page.waitForNetworkIdle();
    let theUrl = new URL(page.url());
    const { category, subcategory, date, segment } = extractFilters(page.url());
    let title = await makeTitle(category, subcategory, date, segment);
    let currentTitle = await page.title();
    expect(currentTitle).to.equal(title);
  });

  /** We go through the segments and make sure the title changes accordingly. */
  it ('should change the title if new segment is chosen', async function () {
    await page.click('div.segmentEditorPanel div.segmentListContainer div.segmentationContainer a.title');
    await page.waitForNetworkIdle();
    let segmentDataDef = 'continentCode==eur';
    let me = await page.waitForSelector(`div.segmentationContainer div.segmentList ul li:nth-of-type(3)`);
    await me.click();
    await page.waitForNetworkIdle();
    const { category, subcategory, date, segment } = extractFilters(page.url());
    let title = await makeTitle(category, subcategory, date, segment);
    let currentTitle = await page.title();
    expect(currentTitle).to.equal(title);

    await page.click('div.segmentEditorPanel div.segmentListContainer div.segmentationContainer a.title');
    await page.waitForNetworkIdle();
    segmentDataDef = 'browserCode==FF';
    me = await page.waitForSelector(`div.segmentationContainer div.segmentList ul li:nth-of-type(2)`);
    await me.click();
    await page.waitForNetworkIdle();
    let { category: category2, subcategory: subcategory2, date: date2, segment: segment2 } = extractFilters(page.url());
    title = await makeTitle(category2, subcategory2, date2, segment2);
    currentTitle = await page.title();
    expect(currentTitle).to.equal(title);

    await page.click('div.segmentEditorPanel div.segmentListContainer div.segmentationContainer a.title');
    await page.waitForNetworkIdle();
    me = await page.waitForSelector(`div.segmentationContainer div.segmentList ul li:nth-of-type(1)`);
    await me.click();
    await page.waitForNetworkIdle();
    let { category: category3, subcategory: subcategory3, date: date3, segment: segment3 } = extractFilters(page.url());
    title = await makeTitle(category3, subcategory3, date3, segment3);
    currentTitle = await page.title();
    expect(currentTitle).to.equal(title);

    await page.click('div.segmentEditorPanel div.segmentListContainer div.segmentationContainer a.title');
    await page.waitForNetworkIdle();
    me = await page.waitForSelector(`div.segmentationContainer div.segmentList ul li:nth-of-type(4)`);
    await me.click();
    await page.waitForNetworkIdle();
    let { category: category4, subcategory: subcategory4, date: date4, segment: segment4 } = extractFilters(page.url());
    title = await makeTitle(category4, subcategory4, date4, segment4);
    currentTitle = await page.title();
    expect(currentTitle).to.equal(title);
  });

});
