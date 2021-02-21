/*!
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReferBannerController', ReferBannerController);

    ReferBannerController.$inject = ['$scope'];

    function ReferBannerController($scope) {
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

        $scope.socialUrl = function (type) {
            let text = 'Did you know Google is using your data for "own purposes"? This means Google owns your data and uses it to monetise their advertising platforms. If you’re using Google Analytics, stay in control by switching to an ethical alternative like Matomo now!';
            let url = 'https://matomo.org/google-owns-your-data/?pk_campaign=share&pk_kwd=onpremise';

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
                let base = 'https://www.linkedin.com/sharing/share-offsite/?';

                let params = { url };
                let paramString = new URLSearchParams(params);

                return `${base}${paramString.toString()}`;
            }

            return '#';
        };

        $scope.referEmail = function () {
            let subject = 'Why I no longer use Google Analytics';
            let body = 'Did you know Google uses data for "own purposes"? That means Google owns your data and is using it to monetise their advertising platforms.\r\nThis is why I don’t use Google Analytics for analysing the performance of my website.\r\n\r\nInstead I choose Matomo, an ethical alternative to Google Analytics that gives me 100% data ownership and protects the data of my website visitors.\r\nI’m sharing this message in the hope that you too will take back the power from Google and get complete ownership of your own data.\r\n\r\nCheck out Matomo at https://matomo.org';

            return encodeURI(`mailto:YOUR_FRIEND@EMAIL.ADDRESS?subject=${subject}&body=${body}`);
        }

        let init = function() {
            $scope.referBanner.show = false;
            $scope.referBanner.closeBanner = closeBanner;
            $scope.referBanner.share = share;

            if ($scope.showReferBanner === 1) {
                $scope.referBanner.show = true;
            };
        };

        init();
    }
})();