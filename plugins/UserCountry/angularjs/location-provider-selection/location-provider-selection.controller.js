/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('LocationProviderSelectionController', LocationProviderSelectionController);

    LocationProviderSelectionController.$inject = ['piwikApi'];

    function LocationProviderSelectionController(piwikApi) {
        var self = this;

        this.isLoading = false;
        this.updateLoading = {};

        // handle 'refresh location' link click
        this.refreshProviderInfo = function (providerId) {

            this.updateLoading[providerId] = true;

            // this should not be in a controller... ideally we fetch this data always from client side and do not
            // prefill it server side
            var $locationNode = $('.provider' + providerId + ' .location');
            $locationNode.css('visibility', 'hidden');

            piwikApi.fetch({
                module: 'UserCountry',
                action: 'getLocationUsingProvider',
                id: providerId,
                format: 'html'
            }).then(function (response) {
                self.updateLoading[providerId] = false;
                $locationNode.html('<strong>' + response + '</strong>').css('visibility', 'visible');
            }, function () {
                self.updateLoading[providerId] = false;
            });
        };

        this.save = function () {
            if (!this.selectedProvider) {
                return;
            }

            this.isLoading = true;
            
            var parent = $(this).closest('p'),
                loading = $('.loadingPiwik', parent),
                ajaxSuccess = $('.success', parent);

            piwikApi.withTokenInUrl();
            piwikApi.fetch({
                method: 'UserCountry.setLocationProvider',
                providerId: this.selectedProvider
            }).then(function () {
                self.isLoading = false;
                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('General_Done'), {
                    context: 'success',
                    noclear: true,
                    type: 'toast',
                    id: 'userCountryLocationProvider'
                });
                notification.scrollToNotification();
            }, function () {
                self.isLoading = false;
            });
        };
    }
})();