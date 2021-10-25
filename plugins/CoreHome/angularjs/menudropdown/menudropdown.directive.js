/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-menudropdown menu-title="MyMenuItem" tooltip="My Tooltip" show-search="false">
 *     <a class="item" href="/url">An Item</a>
 *     <a class="item disabled">Disabled</a>
 *     <a class="item active">Active item</a>
 *     <hr class="item separator"/>
 *     <a class="item disabled category">Category</a>
 *     <a class="item" href="/url"></a>
 * </div>
 */
(function () {
    angular.module('piwikApp').directive('piwikMenudropdown', piwikMenudropdown);

    function piwikMenudropdown(){

        return {
            transclude: true,
            replace: true,
            restrict: 'A',
            scope: {
                menuTitle: '@',
                tooltip: '@',
                showSearch: '=',
                menuTitleChangeOnClick: '='
            },
            templateUrl: 'plugins/CoreHome/angularjs/menudropdown/menudropdown.directive.html?cb=' + piwik.cacheBuster,
            link: function(scope, element, attrs) {

                scope.selectItem = function (event) {
                };

                scope.searchItems = function (searchTerm)
                {
                };
            }
        };
    }
})();
