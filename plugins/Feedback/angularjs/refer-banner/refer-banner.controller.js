/*!
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReferBannerController', ReferBannerController);

    ReferBannerController.$inject = ['$scope', '$timeout', 'piwikApi'];

    function ReferBannerController($scope, $timeout, piwikApi) {
        let setNextReminder = function(nextReminder) {
            let ajaxHandler = new ajaxHelper();
            ajaxHandler.addParams({'module': 'Feedback', 'action': 'updateReferReminderDate'}, 'GET');
            ajaxHandler.addParams({'nextReminder': nextReminder}, 'POST');
            ajaxHandler.send();
        };

        let closeBanner = function() {
            $scope.referBanner.show = false;
            setNextReminder(6 * 30);
        };

        let init = function() {
            $scope.referBanner.show = false;
            $scope.referBanner.closeBanner = closeBanner;

            if ($scope.promptForRefer === 1) {
                $scope.referBanner.show = true;
            };
        };

        init();

        $scope.socialUrl = function (type) {
             // {% set tweetParams = {'text': 'Did you know Google is using your data for "own purposes"? This means Google owns your data and uses it to monetise their advertising platforms. If you’re using Google Analytics, stay in control by switching to an ethical alternative like Matomo now!', 'url': 'https://matomo.org/google-owns-your-data?pk_campaign=share&pk_kwd=onpremise'}|url_encode %}
            // {% set facebookParams = {'t': 'Did you know Google is using your data for "own purposes"? This means Google owns your data and uses it to monetise their advertising platforms. If you’re using Google Analytics, stay in control by switching to an ethical alternative like Matomo now!', 'u': 'https://matomo.org/google-owns-your-data?pk_campaign=share&pk_kwd=onpremise'}|url_encode %}
            // {% set linkedParams = { 'mini': 'true', 'title': 'Did you know Google is using your data for "own purposes"?', 'summary': 'This means Google owns your data and uses it to monetise their advertising platforms. If you’re using Google Analytics, stay in control by switching to an ethical alternative like Matomo now!', 'url': 'https://matomo.org/google-owns-your-data?pk_campaign=share&pk_kwd=onpremise', 'source': 'matomo.org'}|url_encode %}
            let text = 'Did you know Google is using your data for "own purposes"? This means Google owns your data and uses it to monetise their advertising platforms. If you’re using Google Analytics, stay in control by switching to an ethical alternative like Matomo now!';
            let url = 'https://matomo.org/google-owns-your-data?pk_campaign=share&pk_kwd=onpremise';

            if (type === 'twitter') {
                let base = 'https://twitter.com/intent/tweet?';

                let params = { text, url};
                let paramString = new URLSearchParams(params);

                return `${base}${paramString.toString()}`;
            }
        };
    }
})();