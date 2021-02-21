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

        let share = function() {
            $scope.referBanner.show = false;

            setNextReminder(-1);
        }

        let init = function() {
            $scope.referBanner.show = false;
            $scope.referBanner.closeBanner = closeBanner;
            $scope.referBanner.share = share;

            if ($scope.promptForRefer === 1) {
                $scope.referBanner.show = true;
            };
        };

        init();

        $scope.socialUrl = function (type) {
            let title = 'Did you know Google is using your data for "own purposes"?';
            let text = 'Did you know Google is using your data for "own purposes"? This means Google owns your data and uses it to monetise their advertising platforms. If you’re using Google Analytics, stay in control by switching to an ethical alternative like Matomo now!';
            let url = 'https://matomo.org/google-owns-your-data?pk_campaign=share&pk_kwd=onpremise';
            let source = 'matomo.org';

            if (type === 'twitter') {
                let base = 'https://twitter.com/intent/tweet?';

                let params = { text, url};
                let paramString = new URLSearchParams(params);

                return `${base}${paramString.toString()}`;
            }

            if (type === 'facebook') {
                let base = 'https://www.facebook.com/sharer.php?';

                let params = { t: text, u: url};
                let paramString = new URLSearchParams(params);

                return `${base}${paramString.toString()}`;
            }

            if (type === 'linkedin') {
                let base = 'https://www.linkedin.com/shareArticle?';

                let params = { mini: 'true', title, summary: text, url, source};
                let paramString = new URLSearchParams(params);

                return `${base}${paramString.toString()}`;
            }

            return '#';
        };
    }
})();