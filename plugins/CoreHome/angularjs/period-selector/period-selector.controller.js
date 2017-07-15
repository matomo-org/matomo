/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('PeriodSelectorController', PeriodSelectorController);

    PeriodSelectorController.$inject = ['piwik', '$location', 'piwikPeriods'];

    function PeriodSelectorController(piwik, $location, piwikPeriods) {
        var vm = this;

        // the period & date currently being viewed
        vm.periodValue = null;
        vm.dateValue = null;

        vm.selectedPeriod = null;

        vm.startRangeDate = null;
        vm.endRangeDate = null;

        vm.getCurrentlyViewingText = getCurrentlyViewingText;
        vm.changeViewedPeriod = changeViewedPeriod;
        vm.setPiwikPeriodAndDate = setPiwikPeriodAndDate;
        vm.onApplyClicked = onApplyClicked;
        vm.updateSelectedValuesFromHash = updateSelectedValuesFromHash;
        vm.getPeriodDisplayText = getPeriodDisplayText;

        vm.updateSelectedValuesFromHash();
        initTopControls(); // must be called when a top control changes width

        function updateSelectedValuesFromHash() {
            var search = $location.search();

            vm.periodValue = search.period;
            vm.selectedPeriod = search.period;

            if (search.period === 'range') {
                var parts = search.date.split(',');
                vm.startRangeDate = parts[0];
                vm.endRangeDate = parts[1];

                vm.dateValue = piwikPeriods.parseDate(parts[0]);
            } else {
                vm.dateValue = piwikPeriods.parseDate(search.date);

                var range = piwikPeriods.parse(search.period, search.date).getDateRange();
                vm.startRangeDate = formatDate(range[0]);
                vm.endRangeDate = formatDate(range[1]);
            }
        }

        function getPeriodDisplayText(periodLabel) {
            return piwikPeriods.get(periodLabel).getDisplayText();
        }

        function getCurrentlyViewingText() {
            var search = $location.search();
            return piwikPeriods.parse(search.period, search.date).getPrettyString();
        }

        function changeViewedPeriod(period) {
            // only change period if it's different from what's being shown currently
            if (period === vm.periodValue) {
                return;
            }

            // can't just change to a range period, w/o setting two new dates
            if (period === 'range') {
                return;
            }

            setPiwikPeriodAndDate(period, vm.dateValue);
        }

        function onApplyClicked() {
            if (vm.selectedPeriod === 'range') {
                var dateFrom = vm.startRangeDate,
                    dateTo = vm.endRangeDate,
                    oDateFrom = piwikPeriods.parseDate(dateFrom),
                    oDateTo = piwikPeriods.parseDate(dateTo);

                if (!isValidDate(oDateFrom)
                    || !isValidDate(oDateTo)
                    || oDateFrom > oDateTo
                ) {
                    // TODO: use a notification instead?
                    $('#alert').find('h2').text(_pk_translate('General_InvalidDateRange'));
                    piwikHelper.modalConfirm('#alert', {});
                    return;
                }

                propagateNewUrlParams(dateFrom + ',' + dateTo, 'range');
                return;
            }

            setPiwikPeriodAndDate(vm.selectedPeriod, vm.dateValue);

        }

        function setPiwikPeriodAndDate(period, date) {
            period = period || vm.periodValue;
            date = date || vm.dateValue;

            piwik.period = period;
            piwik.currentDateString = formatDate(date);

            var dateRange = piwikPeriods.parse(period, piwik.currentDateString).getDateRange();
            piwik.startDateString = formatDate(dateRange[0]);
            piwik.endDateString = formatDate(dateRange[1]);

            propagateNewUrlParams(piwik.currentDateString, vm.selectedPeriod);
            initTopControls();
        }

        function propagateNewUrlParams(date, period) {
            vm.closePeriodSelector(); // defined in directive

            var $search = $location.search();
            if (date !== $search.date || period !== $search.period) {
                // eg when using back button the date might be actually already changed in the URL and we do not
                // want to change the URL again
                $search.date = date;
                $search.period = period;
                $location.search($search);
            }
        }

        function isValidDate(d) {
            if (Object.prototype.toString.call(d) !== "[object Date]") {
                return false;
            }

            return !isNaN(d.getTime());
        }

        function formatDate(date) {
            return $.datepicker.formatDate('yy-mm-dd', date);
        }
    }
})();