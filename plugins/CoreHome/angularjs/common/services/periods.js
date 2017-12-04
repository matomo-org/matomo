/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Piwik period management service for the frontend.
 *
 * Usage:
 *
 *     var DayPeriod = piwikPeriods.get('day');
 *     var day = new DayPeriod(new Date());
 *
 * or
 *
 *     var day = piwikPeriods.parse('day', '2013-04-05');
 *
 * Adding custom periods:
 *
 * To add your own period to the frontend, create a period class for it
 * w/ the following methods:
 *
 * - **getPrettyString()**: returns a human readable display string for the period.
 * - **getDateRange()**: returns an array w/ two elements, the first being the start
 *                       Date of the period, the second being the end Date. The dates
 *                       must be Date objects, not strings, and are inclusive.
 * - (_static_) **parse(strDate)**: creates a new instance of this period from the
 *                                  value of the 'date' query parameter.
 * - (_static_) **getDisplayText**: returns translated text for the period, eg, 'month',
 *                                  'week', etc.
 *
 * Then call piwik.addCustomPeriod w/ your period class:
 *
 *     piwik.addCustomPeriod('mycustomperiod', MyCustomPeriod);
 *
 * NOTE: currently only single date periods like day, week, month year can
 *       be extended. Other types of periods that require a special UI to
 *       view/edit aren't, since there is currently no way to use a
 *       custom UI for a custom period.
 */
(function () {
    angular.module('piwikApp.service').factory('piwikPeriods', piwikPeriods);

    var periods = {}, periodOrder = [];

    piwik.addCustomPeriod = addCustomPeriod;

    // day period
    function DayPeriod(date) {
        this.dateInPeriod = date;
    }

    DayPeriod.parse = singleDatePeriodFactory(DayPeriod);
    DayPeriod.getDisplayText = function () {
        return _pk_translate('Intl_PeriodDay');
    };

    DayPeriod.prototype = {
        getPrettyString: function () {
            return $.datepicker.formatDate('yy-mm-dd', this.dateInPeriod);
        },

        getDateRange: function () {
            return [this.dateInPeriod, this.dateInPeriod];
        }
    };

    addCustomPeriod('day', DayPeriod);

    // week period
    function WeekPeriod(date) {
        this.dateInPeriod = date;
    }

    WeekPeriod.parse = singleDatePeriodFactory(WeekPeriod);

    WeekPeriod.getDisplayText = function () {
        return _pk_translate('Intl_PeriodWeek');
    };

    WeekPeriod.prototype = {
        getPrettyString: function () {
            var weekDates = this.getDateRange(this.dateInPeriod);
            var startWeek = $.datepicker.formatDate('yy-mm-dd', weekDates[0]);
            var endWeek = $.datepicker.formatDate('yy-mm-dd', weekDates[1]);

            return _pk_translate('General_DateRangeFromTo', [startWeek, endWeek]);
        },

        getDateRange: function () {
            var daysToMonday = (this.dateInPeriod.getDay() + 6) % 7;

            var startWeek = new Date(this.dateInPeriod.getTime());
            startWeek.setDate(this.dateInPeriod.getDate() - daysToMonday);

            var endWeek = new Date(startWeek.getTime());
            endWeek.setDate(startWeek.getDate() + 6);

            return [startWeek, endWeek];
        }
    };

    addCustomPeriod('week', WeekPeriod);

    // month period
    function MonthPeriod(date) {
        this.dateInPeriod = date;
    }

    MonthPeriod.parse = singleDatePeriodFactory(MonthPeriod);

    MonthPeriod.getDisplayText = function () {
        return _pk_translate('Intl_PeriodMonth');
    };

    MonthPeriod.prototype = {
        getPrettyString: function () {
            return _pk_translate('Intl_Month_Long_StandAlone_' + (this.dateInPeriod.getMonth() + 1)) + ' ' +
                this.dateInPeriod.getFullYear();
        },

        getDateRange: function () {
            var startMonth = new Date(this.dateInPeriod.getTime());
            startMonth.setDate(1);

            var endMonth = new Date(this.dateInPeriod.getTime());
            endMonth.setMonth(endMonth.getMonth() + 1);
            endMonth.setDate(0);

            return [startMonth, endMonth];
        }
    };

    addCustomPeriod('month', MonthPeriod);

    // year period
    function YearPeriod(date) {
        this.dateInPeriod = date;
    }

    YearPeriod.parse = singleDatePeriodFactory(YearPeriod);

    YearPeriod.getDisplayText = function () {
        return _pk_translate('Intl_PeriodYear');
    };

    YearPeriod.prototype = {
        getPrettyString: function () {
            return this.dateInPeriod.getFullYear();
        },

        getDateRange: function () {
            var startYear = new Date(this.dateInPeriod.getTime());
            startYear.setMonth(0);
            startYear.setDate(1);

            var endYear = new Date(this.dateInPeriod.getTime());
            endYear.setMonth(12);
            endYear.setDate(0);

            return [startYear, endYear];
        }
    };

    addCustomPeriod('year', YearPeriod);

    // range period
    function RangePeriod(startDate, endDate) {
        this.startDate = startDate;
        this.endDate = endDate;
    }

    RangePeriod.parse = function parseRangePeriod(strDate) {
        var dates = [];

        if (/^previous/.test(strDate)) {
            dates = getLastNRange(strDate.substring(8), 1);
        } else if (/^last/.test(strDate)) {
            dates = getLastNRange(strDate.substring(4), 0);
        } else {
            var parts = strDate.split(',');
            dates[0] = parseDate(parts[0]);
            dates[1] = parseDate(parts[1]);
        }

        return new RangePeriod(dates[0], dates[1]);

        function getLastNRange(strAmount, extraDaysStart) {
            var nAmount = Math.max(parseInt(strAmount) - 1, 0);
            if (isNaN(nAmount)) {
                throw new Error('Invalid range date: ' + strDate);
            }

            var endDate = getToday();
            endDate.setDate(endDate.getDate() - extraDaysStart);

            var startDate = new Date(endDate.getTime());
            startDate.setDate(startDate.getDate() - nAmount);

            return [startDate, endDate];
        }
    };

    RangePeriod.getDisplayText = function () {
        return _pk_translate('General_DateRangeInPeriodList');
    };

    RangePeriod.prototype = {
        getPrettyString: function () {
            var start = $.datepicker.formatDate('yy-mm-dd', this.startDate);
            var end = $.datepicker.formatDate('yy-mm-dd', this.endDate);
            return _pk_translate('General_DateRangeFromTo', [start, end]);
        },

        getDateRange: function () {
            return [this.startDate, this.endDate];
        }
    };

    addCustomPeriod('range', RangePeriod);

    // piwikPeriods service
    function piwikPeriods() {
        return {
            getAllLabels: getAllLabels,
            isRecognizedPeriod: isRecognizedPeriod,
            get: get,
            parse: parse,
            parseDate: parseDate
        };

        function getAllLabels() {
            return [].concat(periodOrder);
        }

        function get(strPeriod) {
            var periodClass = periods[strPeriod];
            if (!periodClass) {
                throw new Error('Invalid period label: ' + strPeriod);
            }
            return periodClass;
        }

        function parse(strPeriod, strDate) {
            return get(strPeriod).parse(strDate);
        }

        function isRecognizedPeriod(strPeriod) {
            return !! periods[strPeriod];
        }
    }

    function addCustomPeriod(name, periodClass) {
        if (periods[name]) {
            throw new Error('The "' + name + '" period already exists! It cannot be overridden.');
        }

        periods[name] = periodClass;
        periodOrder.push(name);
    }

    function singleDatePeriodFactory(periodClass) {
        return function (strDate) {
            return new periodClass(parseDate(strDate));
        };
    }

    function parseDate(strDate) {
        if (strDate === 'today'
            || strDate === 'now'
        ) {
            return getToday();
        }

        if (strDate === 'yesterday'
            // note: ignoring the 'same time' part since the frontend doesn't care about the time
            || strDate === 'yesterdaySameTime'
        ) {
            var yesterday = getToday();
            yesterday.setDate(yesterday.getDate() - 1);
            return yesterday;
        }

        try {
            return $.datepicker.parseDate('yy-mm-dd', strDate);
        } catch (err) {
            // angular swallows this error, so manual console log here
            console.error(err.message || err);
            throw err;
        }
    }

    function getToday() {
        var date = new Date(Date.now());

        // undo browser timezone
        date.setTime(date.getTime() + date.getTimezoneOffset() * 60 * 1000);

        // apply piwik site timezone (if it exists)
        date.setHours(piwik.timezoneOffset || 0);

        // get rid of minutes/seconds/etc.
        date.setMinutes(0);
        date.setSeconds(0);
        date.setMilliseconds(0);
        return date;
    }
})();