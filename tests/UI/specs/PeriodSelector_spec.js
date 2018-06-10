/*!
 * Piwik - free/libre analytics platform
 *
 * Period selector screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("PeriodSelector", function () {
    this.timeout(0);

    var generalParams = 'idSite=1&period=day&date=2012-01-01';
    var url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Actions&subcategory=General_Pages';

    var selector = '#periodString,#periodString .dropdown';
    
    it("should load correctly", async function() {
        expect.screenshot("loaded").to.be.captureSelector(selector, function (page) {
            page.goto(url);

            // disable broadcast.propagateNewPage & remove loading gif
            page.evaluate(function () {
                piwikHelper.isAngularRenderingThePage = function () {
                    return false;
                };

                broadcast.propagateNewPage = function () {};

                // hide ajaxLoadingCalendar via CSS (can't just remove it since it's managed by angular)
                $('head').append('<style type="text/css">#ajaxLoadingCalendar { display: none !important; }</style>');
            });
        }, done);
    });

    it("should expand when clicked", async function() {
        expect.screenshot("expanded").to.be.captureSelector(selector, function (page) {
            page.click('.periodSelector .title');
        }, done);
    });

    it("should select a date when a date is clicked in day-period mode", async function() {
        expect.screenshot("day_selected").to.be.captureSelector(selector, function (page) {
            page.click('.period-date .ui-datepicker-calendar a:contains(12)');
        }, done);
    });

    it("should change the month displayed when a month is selected in the month dropdown", async function() {
        expect.screenshot("month_changed").to.be.captureSelector(selector, function (page) {
            page.evaluate(function () {
                $('.ui-datepicker-month').val(1).trigger('change');
            });
        }, done);
    });

    it("should change the year displayed when a year is selected in the year dropdown", async function() {
        expect.screenshot("year_changed").to.be.captureSelector(selector, function (page) {
            page.evaluate(function () {
                $('.ui-datepicker-year').val(2013).trigger('change');
            });
        }, done);
    });

    it("should change the date when a date is clicked in week-period mode", async function() {
        expect.screenshot("week_selected").to.be.captureSelector(selector, function (page) {
            page.click('label[for=period_id_week]');
            page.click('.period-date .ui-datepicker-calendar a:contains(13)');
        }, done);
    });

    it("should change the date when a date is clicked in month-period mode", async function() {
        expect.screenshot("month_selected").to.be.captureSelector(selector, function (page) {
            page.click('label[for=period_id_month]');
            page.click('.period-date .ui-datepicker-calendar a:contains(14)');
        }, done);
    });

    it("should change the date when a date is clicked in year-period mode", async function() {
        expect.screenshot("year_selected").to.be.captureSelector(selector, function (page) {
            page.click('label[for=period_id_year]');
            page.click('.period-date .ui-datepicker-calendar a:contains(15)');
        }, done);
    });

    it("should display the range picker when the range radio button is clicked", async function() {
        expect.screenshot("range_picker_displayed").to.be.captureSelector(selector, function (page) {
            page.click('label[for=period_id_range]');
        }, done);
    });

    it("should change from & to dates when range picker calendar dates are clicked", async function() {
        expect.screenshot("date_range_selected").to.be.captureSelector(selector, function (page) {
            page.click('#calendarFrom .ui-datepicker-calendar a:contains(10)');
            page.click('#calendarTo .ui-datepicker-calendar a:contains(18)');
            page.mouseMove('#calendarApply');
        }, done);
    });
});