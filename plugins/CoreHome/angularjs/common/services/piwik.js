/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').service('piwik', piwikService);

    piwikService.$inject = ['piwikPeriods'];

    function piwikService(piwikPeriods) {
        var originalTitle;
        piwik.helper    = piwikHelper;
        piwik.broadcast = broadcast;
        piwik.updatePeriodParamsFromUrl = updatePeriodParamsFromUrl;
        piwik.updateDateInTitle = updateDateInTitle;
        piwik.hasUserCapability = hasUserCapability;
        return piwik;

        function hasUserCapability(capability) {
            return angular.isArray(piwik.userCapabilities) && piwik.userCapabilities.indexOf(capability) !== -1;
        }

        function updatePeriodParamsFromUrl() {
            var date = piwik.broadcast.getValueFromHash('date') || piwik.broadcast.getValueFromUrl('date');
            var period = piwik.broadcast.getValueFromHash('period') || piwik.broadcast.getValueFromUrl('period');
            if (!isValidPeriod(period, date)) {
                // invalid data in URL
                return;
            }

            if (piwik.period === period && piwik.currentDateString === date) {
                // this period / date is already loaded
                return;
            }

            piwik.period = period;

            var dateRange = piwikPeriods.parse(period, date).getDateRange();
            piwik.startDateString = piwikPeriods.format(dateRange[0]);
            piwik.endDateString = piwikPeriods.format(dateRange[1]);

            updateDateInTitle(date, period);

            // do not set anything to previousN/lastN, as it's more useful to plugins
            // to have the dates than previousN/lastN.
            if (piwik.period === 'range') {
                date = piwik.startDateString + ',' + piwik.endDateString;
            }

            piwik.currentDateString = date;
        }

        function isValidPeriod(periodStr, dateStr) {
            try {
                piwikPeriods.get(periodStr).parse(dateStr);
                return true;
            } catch (e) {
                return false;
            }
        }

        function updateDateInTitle( date, period ) {
            if (!$('.top_controls #periodString').length) {
                return;
            }

            // Cache server-rendered page title
            originalTitle = originalTitle || document.title;

            if (0 === originalTitle.indexOf(piwik.siteName)) {
                var dateString = ' - ' + piwikPeriods.parse(period, date).getPrettyString() + ' ';
                document.title = piwik.siteName + dateString + originalTitle.substr(piwik.siteName.length);
            }
        }
    }

    angular.module('piwikApp.service').run(initPiwikService);

    initPiwikService.$inject = ['piwik', '$rootScope'];

    function initPiwikService(piwik, $rootScope) {
        $rootScope.$on('$locationChangeSuccess', piwik.updatePeriodParamsFromUrl);
    }
})();
