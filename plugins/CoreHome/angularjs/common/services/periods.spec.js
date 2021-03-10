/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    describe('piwikPeriods', function() {
        var piwikPeriods;

        function clearDate(date)
        {
            var date = new Date(date);
            date.setHours(0);
            date.setMinutes(0);
            date.setSeconds(0);
            date.setMilliseconds(0);

            return date;
        }

        beforeEach(module('piwikApp.service'));
        beforeEach(inject(function($injector) {
            piwikPeriods = $injector.get('piwikPeriods');
        }));

        it('should get daterange for day', function() {
            var day = '2021-03-10';

            var result = piwikPeriods.parse('day', day).getDateRange();
            var expected = [clearDate(day), clearDate(day)];

            expect(result).to.eql(expected);
        });

        it('should get daterange for week', function() {
            var day = '2021-03-10';
            var monday = '2021-03-08';
            var sunday = '2021-03-14';

            var result = piwikPeriods.parse('week', day).getDateRange();
            var expected = [clearDate(monday), clearDate(sunday)];

            expect(result).to.eql(expected);
        });

        it('should get daterange for month', function() {
            var day = '2021-03-10';
            var first = '2021-03-01';
            var last = '2021-03-31';

            var result = piwikPeriods.parse('month', day).getDateRange();
            var expected = [clearDate(first), clearDate(last)];

            expect(result).to.eql(expected);
        });

        it('should get daterange for month for date 31th', function() {
            var day = '2021-03-31';
            var first = '2021-03-01';
            var last = '2021-03-31';

            var result = piwikPeriods.parse('month', day).getDateRange();
            var expected = [clearDate(first), clearDate(last)];

            expect(result).to.eql(expected);
        });

        it('should get daterange for year', function() {
            var day = '2021-03-10';
            var first = '2021-01-01';
            var last = '2021-12-31';

            var result = piwikPeriods.parse('year', day).getDateRange();
            var expected = [clearDate(first), clearDate(last)];

            expect(result).to.eql(expected);
        });

        it('should get daterange for year for date 31th december', function() {
            var day = '2021-12-31';
            var first = '2021-01-01';
            var last = '2021-12-31';

            var result = piwikPeriods.parse('year', day).getDateRange();
            var expected = [clearDate(first), clearDate(last)];

            expect(result).to.eql(expected);
        });

        it('should get proper month rangeperiod when date is 31th march', function() {
            var day = '2021-03-31';
            var first = '2021-02-01';
            var last = '2021-03-31';

            var result = piwikPeriods.RangePeriod.getLastNRange('month', 2, day);

            expect(result.startDate).to.eql(clearDate(first));
            expect(result.endDate).to.eql(clearDate(last));
        });
    });
})();
