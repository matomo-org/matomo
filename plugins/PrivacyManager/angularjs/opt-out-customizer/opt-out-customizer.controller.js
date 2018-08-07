/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('OptOutCustomizerController', OptOutCustomizerController);

    OptOutCustomizerController.$inject = ['$scope', 'piwikApi', '$element'];

    function OptOutCustomizerController($scope, piwikApi, $element) {
        var vm = this;
        vm.piwikurl = $scope.piwikurl;
        vm.language = $scope.language;
        vm.fontSizeUnit = 'px';
        vm.fontSizeWithUnit = '';
        vm.backgroundColor = '';
        vm.fontColor = ''; 
        vm.fontSize = ''; 
        vm.fontFamily = '';
        vm.optOutFormMode = 'opted-in';
        vm.saveOptOutText = function () {
            var optOutSetting = getSetting(vm.privacyManagerSettings, 'defaultOptOutFormOptedOutText');
            optOutSetting.value = vm.optedOutText;

            var optInSetting = getSetting(vm.privacyManagerSettings, 'defaultOptOutFormOptedInText');
            optInSetting.value = vm.optedInText;

            vm.isSavingCustomText = true;
            piwikApi.post({
                method: 'CorePluginsAdmin.setSystemSettings'
            }, {
                settingValues: { PrivacyManager: [optOutSetting, optInSetting] }
            }).then(function () {
                var iframe = $element.find('iframe')[0];
                iframe.src = iframe.src; // force iframe to reload
            });
        };
        vm.updateFontSize = function () {
            if (vm.fontSize) {
                vm.fontSizeWithUnit = vm.fontSize + vm.fontSizeUnit;
            } else {
                vm.fontSizeWithUnit = "";
            }
            this.onUpdate();
        };
        vm.onUpdate = function () {
            if (vm.piwikurl) {
                var value = vm.piwikurl + "index.php?module=CoreAdminHome&action=optOut&language=" + vm.language + "&backgroundColor=" + vm.backgroundColor.substr(1) + "&fontColor=" + vm.fontColor.substr(1) + "&fontSize=" + vm.fontSizeWithUnit + "&fontFamily=" + encodeURIComponent(vm.fontFamily);
                var isAnimationAlreadyRunning = $('.optOutCustomizer pre').queue('fx').length > 0;
                if (value !== vm.iframeUrl && !isAnimationAlreadyRunning) {
                    $('.optOutCustomizer pre').effect("highlight", {}, 1500);
                }
                vm.iframeUrl = value;
                
            } else {
                vm.iframeUrl = "";
            }
        };

        fetchOptOutText().then(function () {
            vm.onUpdate();
        });

        $scope.$watch('piwikurl', function (val, oldVal) {
            vm.onUpdate();
        });

        function fetchOptOutText() {
            return piwikApi.fetch({
                method: 'CorePluginsAdmin.getSystemSettings',
            }).then(function (response) {
                var plugin = response.filter(function (p) { return p.pluginName === 'PrivacyManager'; })[0];
                if (!plugin) {
                    return;
                }

                vm.privacyManagerSettings = plugin.settings;
                vm.optedOutText = getSettingValue(vm.privacyManagerSettings, 'defaultOptOutFormOptedOutText');
                vm.optedInText = getSettingValue(vm.privacyManagerSettings, 'defaultOptOutFormOptedInText');
            });
        }

        function getSettingValue(settings, settingName) {
            var setting = getSetting(settings, settingName);
            if (!setting) {
                return null;
            }
            return setting.value;
        }

        function getSetting(settings, settingName) {
            return settings.filter(function (s) { return s.name === settingName; })[0];
        }
    }
})();
