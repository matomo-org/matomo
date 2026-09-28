/*!
 * Matomo - free/libre analytics platform
 *
 * Period selector tests, ported from tests/UI/specs/PeriodSelector_spec.js.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const { test, expect } = require('@playwright/test');
const { restoreFixture, openSession, expectAreaScreenshot } = require('../support/matomo');

const generalParams = 'idSite=1&period=day&date=2012-01-01';
const url = `?module=CoreHome&action=index&${generalParams}#?${generalParams}&category=General_Actions&subcategory=General_Pages`;
const area = ['#periodString', '#periodString .dropdown'];

test.describe.configure({ mode: 'serial' });

test.describe('PeriodSelector', () => {
  let session;
  let page;

  const calendarDay = (day, calendar = '.period-date') => page.locator(`${calendar} .ui-datepicker-calendar a`, { hasText: new RegExp(`^${day}$`) });
  const disablePagePropagation = () => page.evaluate(() => {
    window.piwikHelper.isReportingPage = () => false;
    window.broadcast.propagateNewPage = () => {};
    // hide the loading indicator via CSS, it is managed by Vue
    window.jQuery('head').append('<style>#ajaxLoadingCalendar { display: none !important; }</style>');
  });
  const enableReportingPage = () => page.evaluate(() => {
    window.piwikHelper.isReportingPage = () => true;
  });

  test.beforeAll(async ({ browser }) => {
    restoreFixture();
    session = await openSession(browser);
    page = session.page;
  });

  test.afterEach(() => session.assertClean());

  test.afterAll(async () => {
    await session.close();
  });

  test('should load correctly', async () => {
    await page.goto(url);
    await disablePagePropagation();
    await expectAreaScreenshot(session, area, 'loaded.png');
  });

  test('should expand when clicked', async () => {
    await page.locator('.periodSelector .title').click();
    await expectAreaScreenshot(session, area, 'expanded.png');
  });

  test('should select a date when a date is clicked in day-period mode', async () => {
    await calendarDay(12).click();
    await expectAreaScreenshot(session, area, 'day_selected.png');
  });

  test('should change the month displayed when a month is selected in the month dropdown', async () => {
    await page.evaluate(() => window.jQuery('.ui-datepicker-month').val(1).trigger('change'));
    await page.mouse.move(-10, -10);
    await expectAreaScreenshot(session, area, 'month_changed.png');
  });

  test('should change the year displayed when a year is selected in the year dropdown', async () => {
    await page.evaluate(() => window.jQuery('.ui-datepicker-year').val(2013).trigger('change'));
    await page.mouse.move(-10, -10);
    await expectAreaScreenshot(session, area, 'year_changed.png');
  });

  test('should change the date when a date is clicked in week-period mode', async () => {
    await page.locator('#period_id_week').click({ force: true });
    await expect(page.locator('#period_id_week')).toBeChecked();
    await calendarDay(13).click();
    await expectAreaScreenshot(session, area, 'week_selected.png');
  });

  test('should activate a period option via Enter key', async () => {
    await page.locator('#period_id_week').focus();
    await page.keyboard.press('Enter');
    await expect(page.locator('#period_id_week')).toBeChecked();
    await expect(page.locator('#period_id_day')).not.toBeChecked();
  });

  test('should activate a period option via Space key', async () => {
    await page.locator('#period_id_month').focus();
    await page.keyboard.press('Space');
    await expect(page.locator('#period_id_month')).toBeChecked();
    await expect(page.locator('#period_id_day')).not.toBeChecked();
  });

  test('should change the date when a date is clicked in month-period mode', async () => {
    await page.locator('#period_id_month').click({ force: true });
    await expect(page.locator('#period_id_month')).toBeChecked();
    await calendarDay(14).click();
    await expectAreaScreenshot(session, area, 'month_selected.png');
  });

  test('should change the date when a date is clicked in year-period mode', async () => {
    await page.locator('#period_id_year').click({ force: true });
    await expect(page.locator('#period_id_year')).toBeChecked();
    await calendarDay(15).click();
    await expectAreaScreenshot(session, area, 'year_selected.png');
  });

  test('should display the range picker when the range radio button is clicked', async () => {
    await page.locator('#period_id_range').click({ force: true });
    await expect(page.locator('#period_id_range')).toBeChecked();
    await expect(page.locator('#calendarFrom .ui-datepicker-calendar')).toBeVisible();
    await expectAreaScreenshot(session, area, 'range_picker_displayed.png');
  });

  test('should change from & to dates when range picker calendar dates are clicked', async () => {
    await calendarDay(10, '#calendarFrom').click();
    await calendarDay(18, '#calendarTo').click();
    await page.locator('#calendarApply').hover();
    await expectAreaScreenshot(session, area, 'date_range_selected.png');
  });

  test("should enable the comparison dropdown when 'compare' is checked", async () => {
    await page.locator('#comparePeriodTo + span').click();
    await expect(page.locator('#comparePeriodTo')).toBeChecked();
    await expectAreaScreenshot(session, area, 'comparison_checked.png');
  });

  test('should show range inputs when custom date range compare is selected', async () => {
    await page.evaluate(() => {
      window.jQuery('#comparePeriodToDropdown select').val('string:custom').trigger('change');
    });
    await expect(page.locator('#comparePeriodToDropdown select')).toHaveValue('string:custom');
    await expectAreaScreenshot(session, area, 'custom_comparison.png');
  });

  test('should close on click if previously opened', async () => {
    await page.locator('.periodSelector .title').click();
    await expect(page.locator('.periodSelector')).not.toHaveClass(/expanded/);
    await expectAreaScreenshot(session, area, 'closed.png');
  });

  test('should apply non-range period selection only after calendar click', async () => {
    await page.goto(url);
    await page.locator('.periodSelector .title').click();
    await enableReportingPage();
    const initialUrl = page.url();

    await page.locator('#period_id_week').click({ force: true });
    await expect(page.locator('#period_id_week')).toBeChecked();
    await expect(page.locator('.periodSelector')).toHaveClass(/expanded/);
    await expect(page.locator('#calendarApply')).toBeVisible();
    await expect(page.locator('.period-date td.ui-datepicker-current-period')).toHaveCount(0);
    expect(page.url()).toBe(initialUrl);

    await calendarDay(13).click();
    await expect(page).not.toHaveURL(initialUrl);
    expect(page.url()).toContain('period=week');
  });

  test('should keep the calendar interactive after selecting a preset', async () => {
    await page.clock.setFixedTime(new Date('2012-01-09T12:00:00Z'));
    await page.goto(url);
    await page.locator('.periodSelector .title').click();
    await enableReportingPage();

    await page.locator('#preset_date_yesterday').click({ force: true });
    await expect(page.locator('#period_id_day')).toBeChecked();
    await expect(page.locator('#preset_date_yesterday')).toBeChecked();

    const selectedDay = (await page.locator('.period-date td.ui-datepicker-current-period').locator('a, span').first().textContent()).trim();
    const otherDay = page.locator('.period-date .ui-datepicker-calendar a').filter({ hasNotText: new RegExp(`^${selectedDay}$`) }).first();
    const initialUrl = page.url();

    await otherDay.click();
    await expect(page).not.toHaveURL(initialUrl);
    await expect(page.locator('#period_id_day')).toBeChecked();
    await expect(page.locator('#preset_date_yesterday')).not.toBeChecked();
  });

  test('should keep range selection pending until apply', async () => {
    await page.goto(url);
    await page.locator('.periodSelector .title').click();
    await enableReportingPage();
    const initialUrl = page.url();

    await page.locator('#period_id_range').click({ force: true });
    await expect(page.locator('#period_id_range')).toBeChecked();
    expect(page.url()).toBe(initialUrl);

    await page.locator('#calendarApply').click();
    await expect(page).not.toHaveURL(initialUrl);
    expect(page.url()).toContain('period=range');
  });

  test('should keep legacy double-click immediate apply behavior for non-range periods', async () => {
    await page.goto(url);
    await page.locator('.periodSelector .title').click();
    await enableReportingPage();

    await page.locator('#period_id_month').dblclick({ force: true });
    await expect(page).toHaveURL(/period=month/);
  });

  test('should keep rolling last7 token after reload and apply', async () => {
    await page.goto('?module=CoreHome&action=index&idSite=1&period=range&date=last7#?idSite=1&period=range&date=last7&category=General_Actions&subcategory=General_Pages');
    await page.locator('.periodSelector .title').click();
    await enableReportingPage();
    await page.locator('#calendarApply').click();
    await session.waitForIdle();

    expect(page.url()).toContain('period=range');
    expect(page.url()).toContain('date=last7');
    expect(page.url()).not.toMatch(/date=\d{4}-\d{2}-\d{2}%2C\d{4}-\d{2}-\d{2}/);
  });

  test('should move forward two days when next period selector is clicked twice', async () => {
    await page.goto(url);
    await page.locator('.periodSelector .move-period-next').click();
    await session.waitForIdle();
    await page.locator('.periodSelector .move-period-next').click();
    await session.waitForIdle();
    await page.mouse.move(-10, -10);
    await expectAreaScreenshot(session, area, 'two_days_forward.png');
  });

  test('should move back one days when previous period selector is clicked once', async () => {
    await page.locator('.periodSelector .move-period-prev').click();
    await session.waitForIdle();
    await page.mouse.move(-10, -10);
    await expectAreaScreenshot(session, area, 'one_day_back.png');
  });

  test('should display disabled previous period button when at the start of site tracking', async () => {
    const params = 'idSite=1&period=day&date=2011-01-01';
    await page.goto(`?module=CoreHome&action=index&${params}#?${params}&category=General_Actions&subcategory=General_Pages`);
    await expectAreaScreenshot(session, area, 'disabled_previous_period.png');
  });

  test('should hide prev/next buttons when dates range selection', async () => {
    const params = 'idSite=1&period=range&date=2011-01-01,2011-02-01';
    await page.goto(`?module=CoreHome&action=index&${params}#?${params}&category=General_Actions&subcategory=General_Pages`);
    await disablePagePropagation();
    await expectAreaScreenshot(session, area, 'hide_prevnext_for_range.png');
  });

  test.describe('match selected compare settings with URL', () => {
    const selectedCompareType = () => page.locator('#comparePeriodToDropdown input');

    test('should select "previous period" from URL', async () => {
      await page.goto(`${url}&comparePeriods[]=day&comparePeriodType=previousPeriod&compareDates[]=2011-12-31`);
      await expect(selectedCompareType()).toHaveValue(/Period/);
    });

    test('should select "previous year" from URL', async () => {
      await page.goto(`${url}&comparePeriods[]=day&comparePeriodType=previousYear&compareDates[]=2011-01-01`);
      await expect(selectedCompareType()).toHaveValue(/Year/);
    });

    test('should select "custom" from URL', async () => {
      await page.goto(`${url}&comparePeriods[]=range&comparePeriodType=custom&compareDates[]=2013-01-01,2013-01-02`);
      await expect(selectedCompareType()).toHaveValue(/Custom/);

      // ensure inputs are properly filled
      await page.locator('.periodSelector .title').click();
      await expect(page.locator('#calendarApply')).toBeVisible();
      await expect(page.locator('#comparePeriodStartDate input')).toHaveValue('2013-01-01');
      await expect(page.locator('#comparePeriodEndDate input')).toHaveValue('2013-01-02');
      await page.mouse.move(-10, -10);
      await expectAreaScreenshot(session, area, 'custom_comparison_url.png');
    });
  });

  test('should show an error when invalid date/period combination is given', async () => {
    // the period selector deliberately throws on the invalid date
    session.allowServerErrors();
    await page.goto(url.replace(/date=[^&#]+&/, 'date=2020-08-08,2020-08-09&'));
    await expect(page.locator('.periodSelector .title')).not.toHaveText('');
    await expectAreaScreenshot(session, [...area, '#notificationContainer'], 'invalid.png');
    session.allowServerErrors(false);
  });

  test('should rediscover the last week preset for explicit week dates after navigation', async () => {
    await page.clock.setFixedTime(new Date('2012-01-09T12:00:00Z'));
    await page.goto('?module=CoreHome&action=index&idSite=1&period=week&date=2012-01-03'
      + '#?idSite=1&period=week&date=2012-01-03&category=General_Actions&subcategory=General_Pages');
    await page.locator('.periodSelector .title').click();
    await expect(page.locator('#period_id_week')).toBeChecked();
    await expect(page.locator('#preset_date_lastWeekMonSun')).toBeChecked();
  });
});
