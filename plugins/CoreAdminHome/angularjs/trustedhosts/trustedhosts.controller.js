/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Controller to save archiving settings
 */
(function () {
    angular.module('piwikApp').controller('TrustedHostsController', TrustedHostsController);

    TrustedHostsController.$inject = ['$scope', 'piwikApi', '$timeout'];

    function TrustedHostsController($scope, piwikApi, $timeout) {

        var self = this;
        this.isLoading = false;

        this.addTrustedHost = function () {
            this.hosts.push({host: ''});

            $timeout(function () {
                $('#trustedHostSettings').find('li:last input').val('').focus();
            });
        };

        this.removeTrustedHost = function (index) {
            this.hosts.splice(index,1);
        };

        this.save = function () {
            var hosts = [];
            angular.forEach(self.hosts, function (host) {
                hosts.push(host.host);
            });

            var doSubmit = function () {
                self.isLoading = true;

                piwikApi.post({module: 'API', method: 'CoreAdminHome.setTrustedHosts'}, {
                    trustedHosts: hosts
                }).then(function (success) {
                    self.isLoading = false;

                    var UI = require('piwik/UI');
                    var notification = new UI.Notification();
                    notification.show(_pk_translate('CoreAdminHome_SettingsSaveSuccess'), {
                        id: 'generalSettings', context: 'success'
                    });
                    notification.scrollToNotification();
                }, function () {
                    self.isLoading = false;
                });
            };

            piwikHelper.modalConfirm('#confirmTrustedHostChange', {yes: doSubmit});
        };
    }
})();