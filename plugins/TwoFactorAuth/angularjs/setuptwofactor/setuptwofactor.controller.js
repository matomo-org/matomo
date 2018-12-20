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
                var id = '';
                if (self.step === 2) {
                    id = '#twoFactorStep2';
                } else if (self.step === 3) {
                    id = '#twoFactorStep3';
                }
                if (id) {
                    piwik.helper.lazyScrollTo(id, 50, true);
                }
            }, 50);
        }

        this.nextStep = function ()
        {
            this.step++;
            this.scrollToEnd();
        }

        $timeout(function () {

            var qrcode = new QRCode(document.getElementById("qrcode"), {
                text: window.twoFaBarCodeSetupUrl
            });
            angular.element('#qrcode').attr('title', ''); // do not show secret on hover

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
