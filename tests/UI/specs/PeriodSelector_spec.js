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

    const clickPreset = async function (label) {
        await page.evaluate(function (presetLabel) {
            const match = $('#otherPeriods .presetDateRanges label span').filter(function () {
                return $(this).text().trim() === presetLabel;
            }).first();
            match.click();
        }, label);
    };

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

    it('should render preset date ranges below period options', async function () {
        const presetCount = await page.evaluate(function () {
            return $('#otherPeriods .presetDateRanges input[type=radio]').length;
        });

        expect(presetCount).to.equal(13);
    });

    it('should mark a clicked preset as selected', async function () {
        await clickPreset('Last 30 days');

        const isSelected = await page.evaluate(function () {
            return $('#preset_date_last30days').prop('checked');
        });

        expect(isSelected).to.equal(true);
    });

    it('should keep period and preset checked ownership mutually exclusive', async function () {
        await clickPreset('Last 30 days');
        await page.waitForTimeout(100);

        let checkedCounts = await page.evaluate(function () {
            return {
                period: $('#otherPeriods input[name=period]:checked').length,
                preset: $('#otherPeriods input[name=presetDateRange]:checked').length,
            };
        });

        expect(checkedCounts.period).to.equal(0);
        expect(checkedCounts.preset).to.equal(1);

        await page.click('#period_id_month');
        await page.waitForTimeout(100);

        checkedCounts = await page.evaluate(function () {
            return {
                period: $('#otherPeriods input[name=period]:checked').length,
                preset: $('#otherPeriods input[name=presetDateRange]:checked').length,
            };
        });

        expect(checkedCounts.period).to.equal(1);
        expect(checkedCounts.preset).to.equal(0);
    });

    it('should keep last clicked preset checked after close/reopen without apply', async function () {
        await clickPreset('Today');
        await page.waitForTimeout(100);

        await page.click('.periodSelector .title');
        await page.waitForTimeout(100);
        await page.click('.periodSelector .title');
        await page.waitForTimeout(100);

        const checkedCounts = await page.evaluate(function () {
            return {
                period: $('#otherPeriods input[name=period]:checked').length,
                preset: $('#otherPeriods input[name=presetDateRange]:checked').length,
                todayChecked: $('#preset_date_today').prop('checked'),
            };
        });

        expect(checkedCounts.period).to.equal(0);
        expect(checkedCounts.preset).to.equal(1);
        expect(checkedCounts.todayChecked).to.equal(true);
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

    it('should apply representative preset URL changes', async function () {
        await page.goto(url);
        await page.click('.periodSelector .title');
        await page.evaluate(function () {
            piwikHelper.isReportingPage = function () {
                return true;
            };
        });

        await clickPreset('Today');
        await page.waitForSelector('#calendarApply', {visible: true, timeout: 250});
        await page.click('#calendarApply');
        await page.waitForTimeout(250);
        let currentUrl = await page.url();
        expect(currentUrl).to.contain('period=day');
        expect(currentUrl).to.match(/date=today|date=[0-9]{4}-[0-9]{2}-[0-9]{2}/);

        await page.click('.periodSelector .title');
        await clickPreset('Last 30 days');
        await page.waitForSelector('#calendarApply', {visible: true, timeout: 250});
        await page.click('#calendarApply');
        await page.waitForTimeout(250);
        currentUrl = await page.url();
        expect(currentUrl).to.contain('period=range');
        expect(currentUrl).to.match(/date=last30|date=[0-9]{4}-[0-9]{2}-[0-9]{2},[0-9]{4}-[0-9]{2}-[0-9]{2}/);

        await page.click('.periodSelector .title');
        await clickPreset('Last quarter');
        await page.waitForSelector('#calendarApply', {visible: true, timeout: 250});
        await page.click('#calendarApply');
        await page.waitForTimeout(250);
        currentUrl = await page.url();
        expect(currentUrl).to.contain('period=range');
        expect(currentUrl).to.match(/date=[0-9]{4}-[0-9]{2}-[0-9]{2},[0-9]{4}-[0-9]{2}-[0-9]{2}/);

        await page.click('.periodSelector .title');
        await clickPreset('This week (Mon - Today)');
        await page.waitForSelector('#calendarApply', {visible: true, timeout: 250});
        await page.click('#calendarApply');
        await page.waitForTimeout(250);
        currentUrl = await page.url();
        expect(currentUrl).to.contain('period=week');
        expect(currentUrl).to.match(/date=today|date=[0-9]{4}-[0-9]{2}-[0-9]{2}/);
    });

    it('should switch to dual calendar and keep URL unchanged when switching Yesterday to Last 30 days', async function () {
        await page.goto(url);
        await page.click('.periodSelector .title');
        await page.evaluate(function () {
            piwikHelper.isReportingPage = function () {
                return true;
            };
        });

        const initialUrl = await page.url();

        await clickPreset('Yesterday');
        await page.waitForTimeout(120);
        await clickPreset('Last 30 days');
        await page.waitForTimeout(120);

        const stateBeforeApply = await page.evaluate(function () {
            return {
                expanded: $('.periodSelector').hasClass('expanded'),
                applyVisible: $('#calendarApply').is(':visible'),
                singleVisible: $('.period-date:visible').length > 0,
                rangeVisible: $('.period-range:visible').length > 0,
                last30Checked: $('#preset_date_last30days').prop('checked'),
            };
        });

        expect(stateBeforeApply.expanded).to.equal(true);
        expect(stateBeforeApply.applyVisible).to.equal(true);
        expect(stateBeforeApply.singleVisible).to.equal(false);
        expect(stateBeforeApply.rangeVisible).to.equal(true);
        expect(stateBeforeApply.last30Checked).to.equal(true);
        expect(await page.url()).to.equal(initialUrl);
    });

    it('should keep single calendar for non-range presets like Yesterday', async function () {
        await page.goto(url);
        await page.click('.periodSelector .title');

        await clickPreset('Yesterday');
        await page.waitForTimeout(120);

        const viewState = await page.evaluate(function () {
            return {
                singleVisible: $('.period-date:visible').length > 0,
                rangeVisible: $('.period-range:visible').length > 0,
            };
        });

        expect(viewState.singleVisible).to.equal(true);
        expect(viewState.rangeVisible).to.equal(false);
    });

    it('should show dual calendar for range presets like Last 7 days', async function () {
        await page.goto(url);
        await page.click('.periodSelector .title');

        await clickPreset('Last 7 days');
        await page.waitForTimeout(120);

        const viewState = await page.evaluate(function () {
            return {
                singleVisible: $('.period-date:visible').length > 0,
                rangeVisible: $('.period-range:visible').length > 0,
            };
        });

        expect(viewState.singleVisible).to.equal(false);
        expect(viewState.rangeVisible).to.equal(true);
    });

    it('should apply staged preset only when apply is clicked', async function () {
        await page.goto(url);
        const initialTitle = await page.evaluate(function () {
            return $('.periodSelector .title').text().trim();
        });
        await page.click('.periodSelector .title');
        await page.evaluate(function () {
            piwikHelper.isReportingPage = function () {
                return true;
            };
        });

        const initialUrl = await page.url();

        await clickPreset('Last 30 days');
        await page.waitForSelector('#calendarApply', {visible: true, timeout: 250});
        await page.waitForTimeout(120);
        expect(await page.url()).to.equal(initialUrl);
        expect(await page.evaluate(function () {
            return $('.periodSelector .title').text().trim();
        })).to.equal(initialTitle);

        await page.click('#calendarApply');
        await page.waitForTimeout(250);

        const stateAfterApply = await page.evaluate(function () {
            return {
                expanded: $('.periodSelector').hasClass('expanded'),
            };
        });
        const appliedUrl = await page.url();

        expect(stateAfterApply.expanded).to.equal(false);
        expect(appliedUrl).to.contain('period=range');
        expect(appliedUrl).to.match(/date=last30|date=[0-9]{4}-[0-9]{2}-[0-9]{2},[0-9]{4}-[0-9]{2}-[0-9]{2}/);
        expect(await page.evaluate(function () {
            return $('.periodSelector .title').text().trim();
        })).to.not.equal(initialTitle);
    });

    it('should apply non-range period selection only after calendar click', async function () {
        await page.goto(url);
        await page.click('.periodSelector .title');
        await page.evaluate(function () {
            piwikHelper.isReportingPage = function () {
                return true;
            };
        });

        const initialUrl = await page.url();

        await page.click('#period_id_week');
        await page.waitForTimeout(150);

        const stateAfterPeriodClick = await page.evaluate(function () {
            return {
                expanded: $('.periodSelector').hasClass('expanded'),
                applyVisible: $('#calendarApply').is(':visible'),
                selectedCells: $('.period-date td.ui-datepicker-current-period').length,
            };
        });

        expect(stateAfterPeriodClick.expanded).to.equal(true);
        expect(stateAfterPeriodClick.applyVisible).to.equal(true);
        expect(stateAfterPeriodClick.selectedCells).to.equal(0);
        expect(await page.url()).to.equal(initialUrl);

        const dateCell = await page.jQuery('.period-date .ui-datepicker-calendar a:contains(13)');
        await dateCell.click();
        await page.waitForTimeout(250);

        const appliedUrl = await page.url();
        expect(appliedUrl).to.contain('period=week');
        expect(appliedUrl).to.not.equal(initialUrl);
    });

    it('should keep range selection pending until apply', async function () {
        await page.goto(url);
        await page.click('.periodSelector .title');
        await page.evaluate(function () {
            piwikHelper.isReportingPage = function () {
                return true;
            };
        });

        const initialUrl = await page.url();

        await page.click('#period_id_range');
        await page.waitForTimeout(150);
        expect(await page.url()).to.equal(initialUrl);

        await page.waitForSelector('#calendarApply', {visible: true, timeout: 250});
        await page.click('#calendarApply');
        await page.waitForTimeout(250);

        const appliedUrl = await page.url();
        expect(appliedUrl).to.contain('period=range');
        expect(appliedUrl).to.not.equal(initialUrl);
    });

    it('should close on outside click without applying pending preset', async function () {
        await page.goto(url);
        await page.click('.periodSelector .title');
        await page.evaluate(function () {
            piwikHelper.isReportingPage = function () {
                return true;
            };
        });

        const initialUrl = await page.url();
        await clickPreset('Last 30 days');
        await page.waitForTimeout(120);

        await page.mouse.click(1, 1);
        await page.waitForTimeout(150);

        const isExpanded = await page.evaluate(function () {
            return $('.periodSelector').hasClass('expanded');
        });
        expect(isExpanded).to.equal(false);
        expect(await page.url()).to.equal(initialUrl);
    });

    it('should keep legacy double-click immediate apply behavior for non-range periods', async function () {
        await page.goto(url);
        await page.click('.periodSelector .title');
        await page.evaluate(function () {
            piwikHelper.isReportingPage = function () {
                return true;
            };
        });

        await page.click('#period_id_month', { clickCount: 2 });
        await page.waitForTimeout(250);

        const currentUrl = await page.url();
        expect(currentUrl).to.contain('period=month');
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
