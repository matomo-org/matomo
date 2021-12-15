/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('PluginSettingsController', PluginSettingsController);

    PluginSettingsController.$inject = ['$scope', 'piwikApi', '$element'];

    function PluginSettingsController($scope, piwikApi, $element) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;

        this.isLoading = true;
        this.isSaving = {};
        this.passwordConfirmation = '';
        this.settingsToSave = null;

        var apiMethod = 'CorePluginsAdmin.getUserSettings';

        if ($scope.mode === 'admin') {
            apiMethod = 'CorePluginsAdmin.getSystemSettings';
        }

        piwikApi.fetch({method: apiMethod}).then(function (settings) {
            self.isLoading = false;
            self.settingsPerPlugin = settings;
            window.anchorLinkFix.scrollToAnchorInUrl();

            // Add plugin sections to page table of contents
            for (var s in self.settingsPerPlugin) {
                if (self.settingsPerPlugin[s].hasOwnProperty('pluginName')) {
                    var pn = self.settingsPerPlugin[s]['pluginName'];
                    if (pn === 'CoreAdminHome' && self.settingsPerPlugin[s].hasOwnProperty('settings')) {
                        for (var i in self.settingsPerPlugin[s].settings) {
                            if (self.settingsPerPlugin[s].settings[i].hasOwnProperty('introduction')) {
                                $('#generalSettingsTOC').append('<a href="#/' + pn + 'PluginSettings">' + self.settingsPerPlugin[s].settings[i].introduction + '</a> ');
                            }
                        }
                    } else {
                        $('#generalSettingsTOC').append('<a href="#/' + pn + '">' + pn.replace(/([A-Z])/g, ' $1').trim() + '</a> ');
                    }
                }
            }
        }, function () {
            self.isLoading = false;
        });

        //show password confirm modal
        this.showPasswordConfirmModal = function(settings) {
            const vm = this;
            this.settingsToSave = settings;
            const passwordInput = `.modal.open #currentUserPassword`;
            const pluginInput = `.pluginSettings input`;
            $element.find('.confirm-password-modal').modal({
                dismissible: false,
                onOpenEnd: function () {
                    $(passwordInput).prop('readonly', false);

                    $(passwordInput).focus();
                    $(passwordInput) .off('keypress').keypress(function (event) {

                          var keycode = (event.keyCode ? event.keyCode : event.which);
                          if (keycode == '13') {
                              $element.find('.confirm-password-modal').modal('close');
                              vm.save();
                          }
                      });
                },
                onCloseEnd: function () {
                    vm.passwordConfirmation = '';
                    $('.pluginSettings input').prop('readonly', false);
                },
            }).modal('open');
        };
        this.save = function () {
            const settings = this.settingsToSave;
            var apiMethod = 'CorePluginsAdmin.setUserSettings';
            if ($scope.mode === 'admin') {
                apiMethod = 'CorePluginsAdmin.setSystemSettings';
            }

            this.isSaving[settings.pluginName] = true;

            var values = {};
            if (!values[settings.pluginName]) {
                values[settings.pluginName] = [];
            }

            angular.forEach(settings.settings, function (setting) {
                var value = setting.value;
                if (value === false) {
                    value = '0';
                } else if (value === true) {
                    value = '1';
                }
                values[settings.pluginName].push({
                    name: setting.name,
                    value: value
                });
            });

            piwikApi.post({method: apiMethod}, {settingValues: values, passwordConfirmation: this.passwordConfirmation}).then(function (success) {
                self.isSaving[settings.pluginName] = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('CoreAdminHome_PluginSettingsSaveSuccess'), {
                    id: 'generalSettings', context: 'success'
                });
                notification.scrollToNotification();

            }, function () {
                self.isSaving[settings.pluginName] = false;
            });

            this.passwordConfirmation = '';
            this.settingsToSave = null;
        };
    }
})();
