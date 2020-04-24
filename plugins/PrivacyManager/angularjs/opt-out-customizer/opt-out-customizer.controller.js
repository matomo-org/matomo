/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('OptOutCustomizerController', OptOutCustomizerController);

    OptOutCustomizerController.$inject = ["$scope"];

    function OptOutCustomizerController($scope) {
        var vm = this;
        vm.piwikurl = $scope.piwikurl;
        vm.language = $scope.language;
        vm.fontSizeUnit = 'px';
        vm.fontSizeWithUnit = '';
        vm.backgroundColor = '';
        vm.fontColor = ''; 
        vm.fontSize = ''; 
        vm.fontFamily = '';
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
                if (vm.backgroundColor === '' && vm.fontColor !== '' && vm.nearlyWhite(vm.fontColor.substr(1))) {
                    $('#previewIframe').addClass('withBg');
                } else {
                    $('#previewIframe').removeClass('withBg');
                }
                var value = vm.piwikurl + "index.php?module=CoreAdminHome&action=optOut&language=" + vm.language + "&backgroundColor=" + vm.backgroundColor.substr(1) + "&fontColor=" + vm.fontColor.substr(1) + "&fontSize=" + vm.fontSizeWithUnit + "&fontFamily=" + encodeURIComponent(vm.fontFamily);
                var isAnimationAlreadyRunning = $('.optOutCustomizer pre').queue('fx').length > 0;
                if (value !== vm.iframeUrl && !isAnimationAlreadyRunning) {
                    $('.optOutCustomizer pre').effect("highlight", {}, 1500);
                }
                vm.iframeUrl = value;
                
            } else {
                vm.iframeUrl = "";
            };
        }
        vm.nearlyWhite = function (hex) {
            var bigint = parseInt(hex, 16);
            var r = (bigint >> 16) & 255;
            var g = (bigint >> 8) & 255;
            var b = bigint & 255;
            
            return (r >= 225 && g >= 225 && b >= 225);
        }
        vm.onUpdate();

        $scope.$watch('piwikurl', function (val, oldVal) {
            vm.onUpdate();
        });
    }
})();
