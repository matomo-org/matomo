/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ManageSmsProviderController', ManageSmsProviderController);

    ManageSmsProviderController.$inject = ['piwikApi', 'piwik'];

    function ManageSmsProviderController(piwikApi, piwik) {

        var self = this;
        this.isDeletingAccount = false;
        this.isUpdatingAccount = false;
        this.showAccountForm = false;
        this.isUpdateAccountPossible = false;
        this.credentials = '{}';

        function deleteApiAccount() {
            self.isDeletingAccount = true;

            piwikApi.fetch(
                {method: 'MobileMessaging.deleteSMSAPICredential'},
                {placeat: '#ajaxErrorManageSmsProviderSettings'}
            ).then(function () {
                self.isDeletingAccount = false;
                piwik.helper.redirect();
            }, function () {
                self.isDeletingAccount = false;
            });
        }

        this.showUpdateAccount = function () {
            this.showAccountForm = true;
        };

        this.isUpdateAccountPossible = function () {

            var self = this;
            self.canBeUpdated = !!this.smsProvider;

            var credentials = angular.fromJson(this.credentials);

            angular.forEach(credentials, function(value, key) {
                if (value == '') {
                    self.canBeUpdated = false;
                }
            });

            return self.canBeUpdated;
        };

        this.updateAccount = function () {
            if (this.isUpdateAccountPossible()) {
                this.isUpdatingAccount = true;

                piwikApi.post(
                    {method: 'MobileMessaging.setSMSAPICredential'},
                    {provider: this.smsProvider, credentials: angular.fromJson(this.credentials)},
                    {placeat: '#ajaxErrorManageSmsProviderSettings'}
                ).then(function () {
                    self.isUpdatingAccount = false;
                    piwik.helper.redirect();
                }, function () {
                    self.isUpdatingAccount = false;
                });
            }
        };

        this.deleteAccount = function () {
            piwikHelper.modalConfirm('#confirmDeleteAccount', {yes: deleteApiAccount});
        };
    }
})();