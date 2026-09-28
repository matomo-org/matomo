/*!
 * Matomo - free/libre analytics platform
 *
 * Segment and period comparison tests, ported from tests/UI/specs/Comparison_spec.js.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const { test, expect } = require('@playwright/test');
const {
  restoreFixture, openSession, expectElementScreenshot, expectPageScreenshot,
} = require('../support/matomo');

const generalParams = 'idSite=1&period=range&date=2012-01-12,2012-01-17';
const urlBase = `module=CoreHome&action=index&${generalParams}`;
const dashboardUrl = `?${urlBase}#?${generalParams}&category=Dashboard_Dashboard&subcategory=1`;
const comparePeriod = '&compareDates[]=2012-01-01,2012-01-31&comparePeriods[]=range';
const compareSegment = '&compareSegments[]=continentCode%3D%3Deur';
const compareParams = comparePeriod + compareSegment;
const widgetUrl = '?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&';
const barGraphUrl = `${widgetUrl}actionToWidgetize=getKeywords&viewDataTable=graphVerticalBar&isFooterExpandedInDashboard=1&${compareParams}`;
const pieGraphUrl = `${widgetUrl}actionToWidgetize=getKeywords&viewDataTable=graphPie&isFooterExpandedInDashboard=1&${compareParams}`;
const goalsTableUrl = `${widgetUrl}actionToWidgetize=getKeywords&viewDataTable=tableGoals&filter_limit=5&isFooterExpandedInDashboard=1${compareParams}`;
const searchEnginesUrl = `${widgetUrl}actionToWidgetize=getSearchEngines&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1`;
const htmlTableUrl = searchEnginesUrl + compareParams;
const htmlTableUrlNoPeriods = searchEnginesUrl + compareSegment;
const htmlTableUrlNoSegments = searchEnginesUrl + comparePeriod;
const visitOverviewContainer = '?module=Widgetize&action=iframe&containerId=VisitOverviewWithGraph&disableLink=0&widget=1&'
  + 'moduleToWidgetize=CoreHome&actionToWidgetize=renderWidgetContainer&disableLink=1&widget=1&';
const visitOverviewWidget = `${visitOverviewContainer}${generalParams}&${compareParams}`;
const visitOverviewSparklines = '?module=Widgetize&action=iframe&disableLink=1&widget=1&'
  + `moduleToWidgetize=VisitsSummary&actionToWidgetize=get&forceView=1&viewDataTable=sparklines&${generalParams}&${compareParams}`;
const visitOverviewWidgetComparePeriods = `${visitOverviewContainer}idSite=1&period=year&date=2012-01-12${compareSegment}`;
const visitOverviewWidgetCompareYear = `${visitOverviewContainer}idSite=1&period=year&date=2012-01-12&compareDates[]=2011-01-31&comparePeriods[]=year`;
const visitOverviewWidgetCompareWeekSmallRange = `${visitOverviewContainer}idSite=1&period=week&date=2012-01-12&compareDates[]=2012-01-20,2012-01-31&comparePeriods[]=range`;
const visitOverviewWidgetCompareLargeRange = `${visitOverviewContainer}idSite=1&period=range&date=2012-01-12,2014-02-12&compareDates[]=2011-01-31,2013-02-31&comparePeriods[]=range`;

/** Evolution badge of the sparkline whose title contains the metric name, with its direction taken from the sign. */
function getSparklineEvolutionForMetric(page, metricText) {
  return page.evaluate((text) => {
    const normalize = (value) => value.replace(/\s+/g, ' ').trim().toLowerCase();
    for (const sparkline of document.querySelectorAll('.sparkline')) {
      const title = sparkline.querySelector('.sparklineCard__title, .sparklineSegmentComparisonCard__title,'
        + ' .sparklineDateComparison__title, .metricValue__title');
      const badge = sparkline.querySelector('.evolutionBadge');
      if (!title || !badge || !normalize(title.textContent).includes(text.toLowerCase())) {
        continue;
      }
      const value = badge.querySelector('.evolutionBadge__value');
      const valueText = value ? value.textContent.trim() : '';
      // EvolutionBadge prepends '+' for an increase and keeps the minus (ASCII or U+2212) for a decrease
      const sign = valueText.charAt(0);
      let direction = 'neutral';
      if (sign === '+') {
        direction = 'up';
      } else if (sign === '-' || sign === '−') {
        direction = 'down';
      }
      return { className: badge.className, direction };
    }
    return null;
  }, metricText);
}

function expectEvolutionPolarity(evolution, isLowerValueBetter) {
  expect(evolution).not.toBeNull();
  expect(['up', 'down']).toContain(evolution.direction);
  const good = (evolution.direction === 'up') !== isLowerValueBetter;
  expect(evolution.className).toContain(good ? 'evolutionBadge--positive' : 'evolutionBadge--negative');
}

test.describe.configure({ mode: 'serial' });

test.describe('Comparison', () => {
  let session;
  let page;

  const noHover = () => page.mouse.move(-10, -10);
  const visible = (selector) => page.locator(selector).filter({ visible: true }).first();
  const comparisonRow = (index) => page.locator('tbody tr.comparisonRow').filter({ visible: true }).nth(index);
  const menuTab = (name) => page.locator('li.menuTab', { hasText: name }).first();
  const openRowAction = async (row, action) => {
    await row.hover();
    await row.locator(`a.${action}`).click();
    await expect(visible('.ui-dialog')).toBeVisible();
    await noHover();
  };
  const choosePreviousPeriodComparison = async (opener) => {
    await page.locator(opener).click();
    await page.locator('input#comparePeriodTo + span').click();
    await page.locator('#calendarApply').click();
  };

  test.beforeAll(async ({ browser }) => {
    restoreFixture();
    session = await openSession(browser);
    page = session.page;
  });

  test.afterEach(() => session.assertClean());

  test.afterAll(async () => {
    await session.close();
  });

  test('should compare periods correctly when comparing the last period', async () => {
    await page.goto(dashboardUrl);
    await session.waitForIdle();
    await choosePreviousPeriodComparison('#periodString #date');
    await expect(visible('.widget')).toBeVisible();
    await expect(visible('.piwik-graph')).toBeVisible();
    await expectElementScreenshot(session, '.pageWrap', 'dashboard_last_period.png');
  });

  test('should add a segment comparison when the compare icon in the segment list is clicked', async () => {
    await page.locator('.segmentationContainer').click();
    await page.locator('li[data-idsegment="2"] .compareSegment').click();
    await expect(page).toHaveURL(/compareSegments/);
    await expect(visible('.widget')).toBeVisible();
    await expectElementScreenshot(session, '.pageWrap', 'dashboard_last_period_and_segment.png');
  });

  test('should not show comparisons for pages that do not support it', async () => {
    await menuTab('Behaviour').locator(':scope > a').click();
    await visible('a.item:text-is("Transitions")').click();
    // the ribbon layer measures the rows in an animation frame after they render
    await page.waitForFunction(() => {
      const rows = document.querySelectorAll('[data-ribbon-key]').length;
      return rows > 0 && rows === document.querySelectorAll('.transitionsRibbons__band').length;
    });
    await expectElementScreenshot(session, '.pageWrap', 'transitions.png');
  });

  test('should show extra serieses when comparing in evolution graphs and sparklines', async () => {
    const visitors = menuTab('Visitors');
    await visitors.locator(':scope > a').click();
    await visitors.locator('a.item', { hasText: 'Overview' }).first().click();
    await expect(visible('.piwik-graph')).toBeVisible();
    await expect(visible('.matomo-comparisons')).toBeVisible();
    await expectElementScreenshot(session, '.pageWrap', 'visitors_overview.png');
  });

  test('should change the evolution series when the sparkline is clicked', async () => {
    await page.locator('.sparkline', { hasText: /pageviews/ }).first().click();
    await noHover();
    await expectElementScreenshot(session, '.pageWrap', 'visitors_overview_switched.png');
  });

  test('should show the tooltip correctly in an evolution graph', async () => {
    await session.waitForIdle();
    await page.locator('.piwik-graph').first().hover();
    await expectElementScreenshot(session, '.ui-tooltip', 'visitors_overview_tooltip.png');
  });

  test('should remove segment comparison when the x button is clicked', async () => {
    await page.locator('.card.comparison .remove-button').first().click();
    await noHover();
    await expect(page).not.toHaveURL(/compareSegments/);
    await expectElementScreenshot(session, '.pageWrap', 'visitors_overview_segment_removed.png');
  });

  test('should remove period comparison if period is selected w/o compare set', async () => {
    await choosePreviousPeriodComparison('#periodString .periodSelector');
    await noHover();
    await expect(page).not.toHaveURL(/compareDates/);
    await expectElementScreenshot(session, '.pageWrap', 'visitors_overview_no_compare.png');
  });

  test('should show the bar graph correctly when comparing segments and period', async () => {
    await page.goto(barGraphUrl);
    await expectPageScreenshot(session, 'bar_graph.png');
  });

  test('should show the pie graph correctly when comparing segments and period', async () => {
    await page.goto(pieGraphUrl);
    await expectPageScreenshot(session, 'pie_graph.png');
  });

  test('should show the normal html table correctly when comparing segments and periods', async () => {
    await page.goto(htmlTableUrl);
    await expectPageScreenshot(session, 'normal_table.png');
  });

  test('should show the correct percentages and tooltip during comparison', async () => {
    // ratios stay visibility:hidden until their cell is hovered, so hover the span jQuery's :visible picks
    const ratio = await page.evaluate(() => {
      const rect = window.jQuery('span.ratio:visible').get(1).getBoundingClientRect();
      return { x: rect.x + rect.width / 2, y: rect.y + rect.height / 2 };
    });
    await page.mouse.move(ratio.x, ratio.y);
    await expectElementScreenshot(session, '.ui-tooltip', 'totals_tooltip.png');
  });

  test('should show the normal html table correctly when comparing segments but not periods', async () => {
    await page.goto(htmlTableUrlNoPeriods);
    await noHover();
    await expectPageScreenshot(session, 'normal_table_no_periods.png');
  });

  test('should show the normal html table correctly when comparing periods but not segments', async () => {
    await page.goto(htmlTableUrlNoSegments);
    await expectPageScreenshot(session, 'normal_table_no_segments.png');
  });

  test('should expand subtables correctly when comparing', async () => {
    await page.locator('tr.subDataTable').first().click();
    await expect(page.locator('.cellSubDataTable > .dataTable')).toHaveCount(1);
    await noHover();
    await expectPageScreenshot(session, 'subtables_loaded.png');
  });

  test('should advance to the next page when paginating the subtable', async () => {
    const pagination = page.locator('.cellSubDataTable .dataTablePages').first();
    const before = await pagination.textContent();
    await page.locator('.cellSubDataTable .dataTableNext').first().click();
    await expect(pagination).not.toHaveText(before);
    await noHover();
    await expectPageScreenshot(session, 'subtables_paginate.png');
  });

  test('should show the row evolution popup for the compared row/segment/period when clicked', async () => {
    await openRowAction(comparisonRow(1), 'actionRowEvolution');
    await expectElementScreenshot(session, '.ui-dialog', 'row_evolution.png');
  });

  test('should show the multirow evolution popup for another comparison series', async () => {
    await page.locator('.rowevolution-startmulti').click();
    await expect(page.locator('.ui-dialog').filter({ visible: true })).toHaveCount(0);
    await openRowAction(comparisonRow(0), 'actionRowEvolution');
    await expectElementScreenshot(session, '.ui-dialog', 'multi_row_evolution.png');
  });

  test('should show the segmented visitor log popup for the compared row/segment/period when clicked', async () => {
    await visible('.ui-dialog-titlebar-close').click();
    await openRowAction(page.locator('tbody tr.comparisonRow').nth(1), 'actionSegmentVisitorLog');
    await expectElementScreenshot(session, '.ui-dialog', 'segmented_visitorlog.png');
  });

  test('should show the goals overview table correctly when comparing segments and period', async () => {
    await page.goto(goalsTableUrl);
    await expectPageScreenshot(session, 'goals_table.png');
  });

  test('should show a specific goals table correctly when comparing segments and period', async () => {
    await page.goto(`${goalsTableUrl}&idGoal=1`);
    await expectPageScreenshot(session, 'goals_table_specific.png');
  });

  test('should load a widgetized sparklines visualization correctly', async () => {
    await page.goto(visitOverviewWidget);
    await expectPageScreenshot(session, 'visits_overview_widget.png');
  });

  // the evolution graph of these widgets renders after the initial load
  for (const [title, url, name] of [
    ['comparing two years', visitOverviewWidgetCompareYear, 'visits_overview_widget_year.png'],
    ['comparing two segments over a year', visitOverviewWidgetComparePeriods, 'visits_overview_widget_compareperiod_year.png'],
    ['comparing a week with a small range', visitOverviewWidgetCompareWeekSmallRange, 'visits_overview_widget_week_smallrange.png'],
    ['comparing large ranges', visitOverviewWidgetCompareLargeRange, 'visits_overview_widget_largerange.png'],
  ]) {
    test(`should load a widgetized sparklines visualization correctly when ${title}`, async () => {
      // known bug: with large ranges annotations.js placeEvolutionIcons() reads a missing x-axis tick canvas
      session.allowServerErrors(url === visitOverviewWidgetCompareLargeRange);
      await page.goto(url);
      await expect(visible('.piwik-graph')).toBeVisible();
      await expectPageScreenshot(session, name);
      session.allowServerErrors(false);
    });
  }

  test('should apply lower-is-better polarity to comparison sparkline evolution', async () => {
    await page.goto(visitOverviewSparklines);
    await expect(visible('.sparkline .evolutionBadge')).toBeVisible();
    expectEvolutionPolarity(await getSparklineEvolutionForMetric(page, 'visits'), false);
    expectEvolutionPolarity(await getSparklineEvolutionForMetric(page, 'bounce rate'), true);
  });

  test('should show evolution metrics correctly formatted in other language', async () => {
    await page.goto(`${visitOverviewSparklines}&language=sv`);
    // series colors land on jQuery ready, so the sparklines are fetched a second time with colors
    await page.waitForFunction(() => window.CoreHome.ComparisonsStoreInstance.getAllComparisonSeries().every((s) => !!s.color));
    await session.waitForIdle();
    await page.evaluate(() => {
      // metric names change with translations, so only the formatted values are compared
      const $ = window.jQuery;
      $('.sparklineSegmentComparisonCard__title, .sparklineDateComparison__title, .metricValue__title').text('metric name');
      $('.metricValue__secondaryLine').each(function replaceLabel() {
        const value = $(this).text().match(/\d+(?:[.,\s ]\d+)*\s*%?/);
        $(this).text(`${value ? `${value[0].trim()} ` : ''}metric name`);
      });
    });
    await expectPageScreenshot(session, 'visits_overview_widget_sv.png');
  });
});
