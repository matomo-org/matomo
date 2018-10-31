/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    angular.module('piwikApp').controller('SetupTwoFactorAuthController', SetupTwoFactorAuthController);

    SetupTwoFactorAuthController.$inject = ['$timeout', 'piwik', '$scope'];

    function SetupTwoFactorAuthController($timeout, piwik, $scope) {

        var self = this;
        this.step = 1;
        this.hasDownloadedRecoveryCode = false;

        this.scrollToEnd = function () {
            $timeout(function () {
                piwik.helper.lazyScrollTo('.setupTwoFactorAuthentication h2:visible:last', 50, true);
            }, 50);
        }

        this.nextStep = function ()
        {
            this.step++;
            this.scrollToEnd();
        }

        $timeout(function () {
            angular.element('.backupRecoveryCode').click(function () {
                self.hasDownloadedRecoveryCode = true;
                $timeout(function () {
                    $scope.$apply();
                }, 1);
            });

            if (angular.element('.setupTwoFactorAuthentication .message_container').length) {
                // user entered something wrong
                self.step = 3;
                self.scrollToEnd();
            }
        });
    }
})();