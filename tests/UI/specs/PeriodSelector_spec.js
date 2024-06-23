/*!
 * Matomo - free/libre analytics platform
 *
 * Period selector screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PeriodSelector", function () {
    const parentSuite = this;

    const generalParams = 'idSite=1&period=day&date=2012-01-01';
    const url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Actions&subcategory=General_Pages';

    const selector = '#periodString,#periodString .dropdown';

    it("should load correctly", async function() {
        await page.goto(url);

        // disable broadcast.propagateNewPage & remove loading gif
        await page.evaluate(function () {
            piwikHelper.isReportingPage = function () {
                return false;
            };

            broadcast.propagateNewPage = function () {};

            // hide ajaxLoadingCalendar via CSS (can't just remove it since it's managed by vue)
            $('head').append('<style type="text/css">#ajaxLoadingCalendar { display: none !important; }</style>');
        });

        expect(await page.screenshotSelector(selector)).to.matchImage('loaded');
    });

    it("should expand when clicked", async function() {
        await page.click('.periodSelector .title');
        expect(await page.screenshotSelector(selector)).to.matchImage('expanded');
    });

    it("should select a date when a date is clicked in day-period mode", async function() {
        const element = await page.jQuery('.period-date .ui-datepicker-calendar a:contains(12)');
        await element.click();

        expect(await page.screenshotSelector(selector)).to.matchImage('day_selected');
    });

    it("should change the month displayed when a month is selected in the month dropdown", async function() {
        await page.evaluate(function () {
            $('.ui-datepicker-month').val(1).trigger('change');
        });
        await page.mouse.move(-10, -10);

        expect(await page.screenshotSelector(selector)).to.matchImage('month_changed');
    });

    it("should change the year displayed when a year is selected in the year dropdown", async function() {
        await page.evaluate(function () {
            $('.ui-datepicker-year').val(2013).trigger('change');
        });
        await page.mouse.move(-10, -10);

        expect(await page.screenshotSelector(selector)).to.matchImage('year_changed');
    });

    it("should change the date when a date is clicked in week-period mode", async function() {
        await page.click('#period_id_week');
        await page.waitForTimeout(250); // wait for animation

        const element = await page.jQuery('.period-date .ui-datepicker-calendar a:contains(13)');
        await element.click();

        expect(await page.screenshotSelector(selector)).to.matchImage('week_selected');
    });

    it("should change the date when a date is clicked in month-period mode", async function() {
        await page.click('#period_id_month');
        await page.waitForTimeout(250); // wait for animation

        const element = await page.jQuery('.period-date .ui-datepicker-calendar a:contains(14)');
        await element.click();

        expect(await page.screenshotSelector(selector)).to.matchImage('month_selected');
    });

    it("should change the date when a date is clicked in year-period mode", async function() {
        await page.click('#period_id_year');
        await page.waitForTimeout(250); // wait for animation

        const element = await page.jQuery('.period-date .ui-datepicker-calendar a:contains(15)');
        await element.click();

        expect(await page.screenshotSelector(selector)).to.matchImage('year_selected');
    });

    it("should display the range picker when the range radio button is clicked", async function() {
        await page.click('#period_id_range');
        await page.waitForTimeout(250); // wait for animation

        expect(await page.screenshotSelector(selector)).to.matchImage('range_picker_displayed');
    });

    it("should change from & to dates when range picker calendar dates are clicked", async function() {
        let element = await page.jQuery('#calendarFrom .ui-datepicker-calendar a:contains(10)');
        await element.click();

        element = await page.jQuery('#calendarTo .ui-datepicker-calendar a:contains(18)');
        await element.click();

        await page.hover('#calendarApply');
        await page.waitForTimeout(250);

        expect(await page.screenshotSelector(selector)).to.matchImage('date_range_selected');
    });

    it("should enable the comparison dropdown when 'compare' is checked", async function () {
        await page.click('#comparePeriodTo + span');
        await page.waitForTimeout(250); // wait for animation

        expect(await page.screenshotSelector(selector)).to.matchImage('comparison_checked');
    });

    it('should show range inputs when custom date range compare is selected', async function () {
        await page.evaluate(function () {
            $('#comparePeriodToDropdown select').val('string:custom').trigger('change');
        });
        await page.waitForTimeout(250); // wait for animation

        expect(await page.screenshotSelector(selector)).to.matchImage('custom_comparison');
    });

    it("should close on click if previously opened", async function () {
      await page.click('.periodSelector .title');
      expect(await page.screenshotSelector(selector)).to.matchImage('closed');
    });

    it("should move forward two days when next period selector is clicked twice", async function () {
        await page.goto(url);

        await page.click('.periodSelector .move-period-next');
        await page.waitForNetworkIdle();
        await page.click('.periodSelector .move-period-next');

        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        expect(await page.screenshotSelector(selector)).to.matchImage('two_days_forward');
    });

    it("should move back one days when previous period selector is clicked once", async function () {
        await page.click('.periodSelector .move-period-prev');

        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        expect(await page.screenshotSelector(selector)).to.matchImage('one_day_back');
    });

    it("should display disabled previous period button when at the start of site tracking", async function () {
        const generalParams = 'idSite=1&period=day&date=2011-01-01';
        const url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Actions&subcategory=General_Pages';

        await page.goto(url);

        expect(await page.screenshotSelector(selector)).to.matchImage('disabled_previous_period');
    });

    it("should hide prev/next buttons when dates range selection", async function () {
        const generalParams = 'idSite=1&period=range&date=2011-01-01,2011-02-01';
        const url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Actions&subcategory=General_Pages';

        await page.goto(url);

        await page.evaluate(function () {
          // disable page propagation again for further tests
          broadcast.propagateNewPage = function () {};
        });

        expect(await page.screenshotSelector(selector)).to.matchImage('hide_prevnext_for_range');
    });

    describe('match selected compare settings with URL', async function() {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        const getSelectedPeriodType = async function () {
          const compareToTypeInput = await page.$('#comparePeriodToDropdown input');
          const compareToTypeValue = await compareToTypeInput.getProperty('value');

          return await compareToTypeValue.jsonValue();
        };

        it('should select "previous period" from URL', async function () {
          await page.goto(url + '&comparePeriods[]=day&comparePeriodType=previousPeriod&compareDates[]=2011-12-31');
          await page.waitForNetworkIdle();

          expect(await getSelectedPeriodType()).to.match(/Period/);
        });

        it('should select "previous year" from URL', async function () {
          await page.goto(url + '&comparePeriods[]=day&comparePeriodType=previousYear&compareDates[]=2011-01-01');
          await page.waitForNetworkIdle();

          expect(await getSelectedPeriodType()).to.match(/Year/);
        });

        it ('should select "custom" from URL', async function() {
          await page.goto(url + '&comparePeriods[]=range&comparePeriodType=custom&compareDates[]=2013-01-01,2013-01-02');
          await page.waitForNetworkIdle();

          expect(await getSelectedPeriodType()).to.match(/Custom/);

          // ensure inputs are properly filled
          await page.click('.periodSelector .title');
          await page.waitForSelector('#calendarApply', {visible: true, timeout: 250});

          expect(await page.screenshotSelector(selector)).to.matchImage('custom_comparison_url');
        });
    });

    it('should show an error when invalid date/period combination is given', async function () {
        await page.goto('about:blank');
        await page.goto(url.replace(/date=[^&#]+&/, 'date=2020-08-08,2020-08-09&'));
        await page.waitForTimeout(250);

        expect(await page.screenshotSelector(selector + ',#notificationContainer')).to.matchImage('invalid');
    });
});
