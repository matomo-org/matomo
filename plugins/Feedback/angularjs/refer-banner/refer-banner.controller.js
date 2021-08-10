/*!
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReferBannerController', ReferBannerController);

    ReferBannerController.$inject = ['$scope', '$timeout'];

    function ReferBannerController($scope, $timeout) {
        var setNextReminder = function(nextReminder) {
            var ajaxHandler = new ajaxHelper();
            ajaxHandler.addParams({'module': 'Feedback', 'action': 'updateReferReminderDate'}, 'GET');
            ajaxHandler.addParams({'nextReminder': nextReminder}, 'POST');
            ajaxHandler.send();
        };

        var remindMeLater = function() {
            $scope.referBanner.show = false;

            setNextReminder(6 * 30);
        };

        var dontShowAgain = function() {
            $scope.referBanner.show = false;

            setNextReminder(-1);
        };

        var share = function() {
            var modal = M.Modal.getInstance($('.modal.open'));

            if (modal) {
                modal.close();
            }

            $scope.referBanner.showThanks = true;
            $scope.referBanner.show = false;

            setNextReminder(-1);
        }

        $scope.socialUrl = function (type) {
            var text = _pk_translate('Feedback_ReferBannerSocialShareText');
            var url = 'https://matomo.org/?pk_campaign=share&pk_kwd=onpremise';

            if (type === 'twitter') {
                var base = 'https://twitter.com/intent/tweet?';

                var params = { 'text': text, 'url': url};
                var paramString = '';
                for (var param in params) {
                    paramString += param + '=' + encodeURIComponent(params[param]) + '&';
                }

                return base + paramString.slice(0, -1);
            }

            if (type === 'facebook') {
                var base = 'https://www.facebook.com/sharer.php?';

                var params = { 't': text, 'u': url};
                var paramString = '';
                for (var param in params) {
                    paramString += param + '=' + encodeURIComponent(params[param]) + '&';
                }

                return base + paramString.slice(0, -1);
            }

            if (type === 'linkedin') {
                var base = 'https://www.linkedin.com/sharing/share-offsite/?';

                var params = { 'url': url };
                var paramString = '';
                for (var param in params) {
                    paramString += param + '=' + encodeURIComponent(params[param]) + '&';
                }

                return base + paramString.slice(0, -1);
            }

            return '#';
        };

        $scope.referEmail = function () {
            var subject = _pk_translate('Feedback_ReferBannerEmailShareSubject');
            var body = _pk_translate('Feedback_ReferBannerEmailShareBody');

            return encodeURI('mailto:YOUR_FRIEND@EMAIL.ADDRESS?subject=' + subject + '&body=' + body);
        }

        var init = function() {
            $scope.referBanner.showThanks = false;
            $scope.referBanner.remindMeLater = remindMeLater;
            $scope.referBanner.dontShowAgain = dontShowAgain;
            $scope.referBanner.share = share;

            if ($scope.showReferBanner === 1) {
                $timeout(function() {
                    $scope.referBanner.show = true;
                });
            }
        };

        init();
    }
})();
