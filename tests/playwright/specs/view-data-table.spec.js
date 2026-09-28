/*!
 * Matomo - free/libre analytics platform
 *
 * Report visualization tests, ported from tests/UI/specs/ViewDataTable_spec.js.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const { test, expect } = require('@playwright/test');
const {
  restoreFixture, openSession, expectElementScreenshot, expectPageScreenshot,
} = require('../support/matomo');

const url = '?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&'
  + 'actionToWidgetize=getKeywords&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1';
const flatUrl = `${url.replace(/filter_limit=5/, 'filter_limit=10')}&flat=1`;

test.describe.configure({ mode: 'serial' });

test.describe('ViewDataTableTest', () => {
  let session;
  let page;

  const noHover = () => page.mouse.move(-10, -10);
  // like Puppeteer's page.click(): the first visible match (widgets render some controls twice)
  const visible = (selector) => page.locator(selector).filter({ visible: true }).first();
  const clickVisible = (selector) => visible(selector).click();
  const switchVisualization = async (footerIconId) => {
    await clickVisible('.activateVisualizationSelection > span');
    await clickVisible(`.tableIcon[data-footer-icon-id=${footerIconId}]`);
  };
  const configure = async (option) => {
    await clickVisible('.dropdownConfigureIcon');
    await clickVisible(option);
  };
  const search = async (term) => {
    await clickVisible('.dataTableAction.searchAction');
    await visible('.searchAction .dataTableSearchInput').fill(term);
    await clickVisible('.searchAction .icon-search');
    await session.waitForIdle();
    await page.evaluate(() => document.activeElement.blur());
  };

  test.beforeAll(async ({ browser }) => {
    restoreFixture();
    session = await openSession(browser, {
      userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11.2; rv:85.0) Gecko/20100101 Firefox/85.0',
    });
    page = session.page;
  });

  test.afterEach(() => session.assertClean());

  test.afterAll(async () => {
    await session.close();
  });

  test('should load correctly', async () => {
    await page.goto(url);
    await expectPageScreenshot(session, '0_initial.png');
  });

  test('should load all columns when all columns clicked', async () => {
    await switchVisualization('tableAllColumns');
    await noHover();
    await expectPageScreenshot(session, '1_all_columns.png');
  });

  test('should sort a column in descending order when column clicked initially', async () => {
    await clickVisible('th#avg_time_on_site');
    await noHover();
    await expectPageScreenshot(session, '2_column_sorted_desc.png');
  });

  test('should sort a column in ascending order when column clicked second time', async () => {
    await clickVisible('th#avg_time_on_site');
    await noHover();
    await expectPageScreenshot(session, '3_column_sorted_asc.png');
  });

  test('should show all available visualizations for this report', async () => {
    await clickVisible('.activateVisualizationSelection > span');
    await noHover();
    // The selection is cut off in the screenshot, because the widget's iframe is too small and
    // Materialize crops the selection to the available space.
    await expectElementScreenshot(session, '.dataTableFooterIcons', '5_visualizations.png');
  });

  test('should load goals table when goals footer icon clicked', async () => {
    await clickVisible('.tableIcon[data-footer-icon-id=tableGoals]');
    await noHover();
    await expectPageScreenshot(session, '5_goals.png');
  });

  test('should load bar graph when bar graph footer icon clicked', async () => {
    await switchVisualization('graphVerticalBar');
    await expectPageScreenshot(session, '6_bar_graph.png');
  });

  test('should load pie graph when pie graph footer icon clicked', async () => {
    await switchVisualization('graphPie');
    await expectPageScreenshot(session, '7_pie_graph.png');
  });

  test('should load a tag cloud when tag cloud footer icon clicked', async () => {
    await switchVisualization('cloud');
    await expectPageScreenshot(session, '8_tag_cloud.png');
  });

  test('should load normal table when normal table footer icon clicked', async () => {
    await switchVisualization('table');
    await noHover();
    await expectPageScreenshot(session, '9_normal_table.png');
  });

  test('should show the limit selector when the limit selector is clicked', async () => {
    await clickVisible('.limitSelection input');
    await noHover();
    await expect(page.locator('.limitSelection ul')).toBeVisible();
    await expectPageScreenshot(session, 'limit_selector_open.png');
  });

  test('should change the number of rows when new limit selected', async () => {
    await page.locator('.limitSelection ul li span', { hasText: /^10$/ }).first().click();
    // the reloaded table would otherwise show a hover state for the row under the pointer
    await noHover();
    await expectPageScreenshot(session, '10_change_limit.png');
  });

  test('should flatten the table when the flatten link is clicked', async () => {
    await configure('.dataTableFlatten');
    await noHover();
    await expectPageScreenshot(session, '11_flattened.png');
  });

  test('should show dimensions separately when option is clicked', async () => {
    await configure('.dataTableShowDimensions');
    await noHover();
    await expectPageScreenshot(session, 'dimension_columns.png');
  });

  test('should search in subtable dimensions even when they are displayed separately', async () => {
    await search('Bing');
    await noHover();
    await expectPageScreenshot(session, 'dimension_search.png');
  });

  test('search should still work when showing dimensions combined again', async () => {
    await configure('.dataTableShowDimensions');
    await noHover();
    await expectPageScreenshot(session, 'flatten_search.png');
  });

  test('search should still work when switching to back to separate dimensions', async () => {
    await configure('.dataTableShowDimensions');
    await noHover();
    await session.waitForIdle();
    await page.evaluate(() => document.activeElement.blur());
    await expectPageScreenshot(session, 'dimension_search.png');
  });

  test('should show aggregate rows when the aggregate rows option is clicked', async () => {
    await page.goto(flatUrl);
    await session.waitForIdle();
    await configure('.dataTableIncludeAggregateRows');
    await noHover();
    await expectPageScreenshot(session, '12_aggregate_shown.png');
  });

  test('should make the report hierarchical when the flatten link is clicked again', async () => {
    await configure('.dataTableFlatten');
    await noHover();
    await expectPageScreenshot(session, '13_make_hierarchical.png');
  });

  test('should show the visits percent when hovering over a column', async () => {
    await page.locator('td.column:not(.label)').first().hover();
    await expectPageScreenshot(session, '14_visits_percent.png');
  });

  test('should load subtables correctly when row clicked', async () => {
    await page.locator('tr.subDataTable').nth(0).click();
    await expect(page.locator('.cellSubDataTable > .dataTable')).toHaveCount(1);
    await page.locator('tr.subDataTable').nth(2).click();
    await noHover();
    await expect(page.locator('.cellSubDataTable > .dataTable')).toHaveCount(2);
    await expectPageScreenshot(session, 'subtables_loaded.png');
  });

  test('should search the table when a search string is entered and the search button clicked', async () => {
    await search('term');
    await expectPageScreenshot(session, '15_search.png');
  });

  test('should display the export popover when clicking the export icon', async () => {
    await clickVisible('.activateExportSelection');
    await expect(page.locator('#reportExport .btn')).toBeVisible();
    await expectElementScreenshot(session, '.ui-dialog', 'export_options.png');
  });

  test('should display the ENTER_YOUR_TOKEN_AUTH_HERE text in the export url', async () => {
    await page.goto(flatUrl);
    await session.waitForIdle();
    await clickVisible('.activateExportSelection');
    await clickVisible('.toggle-export-url');
    await expect(visible('.exportFullUrl')).toBeVisible();
    await expect(visible('.ui-dialog .tooltip')).toContainText('ENTER_YOUR_TOKEN_AUTH_HERE');
    await expectElementScreenshot(session, '.ui-dialog', 'export_options_2.png');
  });

  test('should show the totals row when the config link is clicked', async () => {
    await page.goto(url);
    await configure('.dataTableShowTotalsRow');
    await noHover();
    await expectPageScreenshot(session, 'totals_row.png');
  });

  test('should display a related report when related report link is clicked', async () => {
    await page.goto(url.replace('=Referrers', '=DevicesDetection').replace('=getKeywords', '=getOsFamilies'));
    await page.locator('.datatableRelatedReports li > span').filter({ visible: true }).first().click();
    await noHover();
    await expectPageScreenshot(session, 'related_report_click.png');
  });

  test('should exclude low population rows when low population clicked', async () => {
    await page.goto(url
      .replace('moduleToWidgetize=Referrers', 'moduleToWidgetize=Actions')
      .replace('actionToWidgetize=getKeywords', 'actionToWidgetize=getPageUrls'));
    await configure('.dataTableExcludeLowPopulation');
    await noHover();
    await expectPageScreenshot(session, 'exclude_low_population.png');
  });
});
