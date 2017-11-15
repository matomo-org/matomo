/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').service('piwik', piwikService);

    piwikService.$inject = ['piwikPeriods'];

    function piwikService(piwikPeriods) {
        piwik.helper    = piwikHelper;
        piwik.broadcast = broadcast;
        piwik.updatePeriodParamsFromUrl = updatePeriodParamsFromUrl;
        return piwik;

        function updatePeriodParamsFromUrl() {
            var date = piwik.broadcast.getValueFromHash('date');
            var period = piwik.broadcast.getValueFromHash('period');

            if (!piwikPeriods.isRecognizedPeriod(period)
                || !isValidDateStr(date)
            ) {
                // invalid data in URL
                return;
            }

            if (piwik.period === period && piwik.currentDateString === date) {
                // this period / date is already loaded
                return;
            }

            piwik.period = period;

            if (date && date.indexOf(',') > -1) {
                var dateParts = date.split(',');
                if (dateParts[1]) {
                    piwik.currentDateString = dateParts[1];
                } else if (dateParts[0]) {
                    piwik.currentDateString = dateParts[0];
                }
            } else {
                piwik.currentDateString = date;
            }

            var dateRange = piwikPeriods.parse(period, date).getDateRange();
            piwik.startDateString = $.datepicker.formatDate('yy-mm-dd', dateRange[0]);
            piwik.endDateString = $.datepicker.formatDate('yy-mm-dd', dateRange[1]);
        }

        function isValidDateStr(dateStr) {
            if (dateStr.indexOf(',') !== -1) {
                var dateParts = dateStr.split(',');
                return isValidDateStr(dateParts[0]) && isValidDateStr(dateParts[1]);
            }

            try {
                piwikPeriods.parseDate(dateStr);
                return true;
            } catch (e) {
                return false;
            }
        }
    }

    angular.module('piwikApp.service').run(initPiwikService);

    initPiwikService.$inject = ['piwik', '$rootScope'];

    function initPiwikService(piwik, $rootScope) {
        $rootScope.$on('$locationChangeSuccess', piwik.updatePeriodParamsFromUrl);
    }
})();
