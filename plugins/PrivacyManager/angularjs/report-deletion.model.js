/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').factory('reportDeletionModel', reportDeletionModel);

    reportDeletionModel.$inject = ['piwik', 'piwikApi'];

    function reportDeletionModel (piwik, piwikApi) {

        var currentRequest;
        var isFirstLoad = true;

        var model = {
            settings: {},
            showEstimate: false,
            loadingEstimation: false,
            estimation: '',
            isModified: false,
            isEitherDeleteSectionEnabled: isEitherDeleteSectionEnabled,
            reloadDbStats: reloadDbStats,
            savePurageDataSettings: savePurageDataSettings,
            updateSettings: updateSettings,
            initSettings: initSettings
        };

        return model;

        function updateSettings(settings)
        {
            initSettings(settings);
            model.isModified = true;
        }

        function initSettings(settings)
        {
            model.settings = angular.merge({}, model.settings, settings);
            model.reloadDbStats();
        }

        function savePurageDataSettings(controller, apiMethod, settings)
        {
            controller.isLoading = true;
            model.isModified = false;

            return piwikApi.post({
                module: 'API', method: apiMethod
            }, settings).then(function () {
                controller.isLoading = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('CoreAdminHome_SettingsSaveSuccess'), {context: 'success', id:'privacyManagerSettings'});
                notification.scrollToNotification();
            }, function () {
                controller.isLoading = false;
            });
        }

        function isEitherDeleteSectionEnabled() {
            return ('1' === model.settings.enableDeleteLogs || '1' === model.settings.enableDeleteReports);
        }

        function isManualEstimationLinkShowing()
        {
            return $('#getPurgeEstimateLink').length > 0;
        }

        /**
         * @param {boolean} [forceEstimate]  (defaults to false)
         */
        function reloadDbStats(forceEstimate) {
            if (currentRequest) {
                currentRequest.abort();
            }

            // if the manual estimate link is showing, abort unless forcing
            if (forceEstimate !== true
                && (!isEitherDeleteSectionEnabled() || isManualEstimationLinkShowing())) {
                return;
            }

            model.loadingEstimation = true;
            model.estimation = '';
            model.showEstimate = false;

            var formData = model.settings;

            if (forceEstimate === true) {
                formData['forceEstimate'] = 1;
            }

            currentRequest = piwikApi.post({
                module: 'PrivacyManager',
                action: 'getDatabaseSize',
                format: 'html'
            }, formData).then(function (data) {
                currentRequest = undefined;
                model.estimation = data;
                model.showEstimate = true;
                model.loadingEstimation = false;
            }, function () {
                model.loadingEstimation = true;
            });
        }

    }
})();