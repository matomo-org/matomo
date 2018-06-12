/*!
 * Piwik - free/libre analytics platform
 *
 * Period selector screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PeriodSelector", function () {
    var generalParams = 'idSite=1&period=day&date=2012-01-01';
    var url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Actions&subcategory=General_Pages';

    var selector = '#periodString,#periodString .dropdown';

    it("should load correctly", async function() {
        await page.goto(url);

        // disable broadcast.propagateNewPage & remove loading gif
        await page.evaluate(function () {
            piwikHelper.isAngularRenderingThePage = function () {
                return false;
            };

            broadcast.propagateNewPage = function () {};

            // hide ajaxLoadingCalendar via CSS (can't just remove it since it's managed by angular)
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
        await page.click('label[for=period_id_week]');
        await page.waitFor(250); // wait for animation

        const element = await page.jQuery('.period-date .ui-datepicker-calendar a:contains(13)');
        await element.click();

        expect(await page.screenshotSelector(selector)).to.matchImage('week_selected');
    });

    it("should change the date when a date is clicked in month-period mode", async function() {
        await page.click('label[for=period_id_month]');
        await page.waitFor(250); // wait for animation

        const element = await page.jQuery('.period-date .ui-datepicker-calendar a:contains(14)');
        await element.click();

        expect(await page.screenshotSelector(selector)).to.matchImage('month_selected');
    });

    it("should change the date when a date is clicked in year-period mode", async function() {
        await page.click('label[for=period_id_year]');
        await page.waitFor(250); // wait for animation

        const element = await page.jQuery('.period-date .ui-datepicker-calendar a:contains(15)');
        await element.click();

        expect(await page.screenshotSelector(selector)).to.matchImage('year_selected');
    });

    it("should display the range picker when the range radio button is clicked", async function() {
        await page.click('label[for=period_id_range]');
        await page.waitFor(250); // wait for animation

        expect(await page.screenshotSelector(selector)).to.matchImage('range_picker_displayed');
    });

    it("should change from & to dates when range picker calendar dates are clicked", async function() {
        let element = await page.jQuery('#calendarFrom .ui-datepicker-calendar a:contains(10)');
        await element.click();

        element = await page.jQuery('#calendarTo .ui-datepicker-calendar a:contains(18)');
        await element.click();

        await page.hover('#calendarApply');

        expect(await page.screenshotSelector(selector)).to.matchImage('date_range_selected');
    });
});