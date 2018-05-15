/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('PiwikMarketplaceLicenseController', PiwikMarketplaceLicenseController);

    PiwikMarketplaceLicenseController.$inject = ['piwik', 'piwikApi'];

    function PiwikMarketplaceLicenseController(piwik, piwikApi) {

        this.licenseKey = '';
        this.enableUpdate = false;
        this.isUpdating = false;

        var self = this;

        function updateLicenseKey(action, licenseKey, onSuccessMessage)
        {

            piwikApi.withTokenInUrl();
            piwikApi.post({
                module: 'API',
                method: 'Marketplace.' + action,
                format: 'JSON'
            }, {licenseKey: licenseKey}).then(function (response) {
                self.isUpdating = false;

                if (response && response.value) {
                    var UI = require('piwik/UI');
                    var notification = new UI.Notification();
                    notification.show(onSuccessMessage, {context: 'success'});

                    piwik.helper.redirect();
                }
            }, function () {
                self.isUpdating = false;
            });
        }

        this.updatedLicenseKey = function () {
            this.enableUpdate = !!this.licenseKey;
        };

        this.updateLicense = function () {
            this.enableUpdate = false;
            this.isUpdating = true;

            updateLicenseKey('saveLicenseKey', this.licenseKey, _pk_translate('Marketplace_LicenseKeyActivatedSuccess'));
        };

        this.removeLicense = function () {
            piwik.helper.modalConfirm('#confirmRemoveLicense', {yes: function () {
                self.enableUpdate = false;
                self.isUpdating = true;
                updateLicenseKey('deleteLicenseKey', '', _pk_translate('Marketplace_LicenseKeyDeletedSuccess'));
            }});
        };

    }
})();