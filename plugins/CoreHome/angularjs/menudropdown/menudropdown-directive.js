/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-menudropdown menu-title="MyMenuItem" tooltip="My Tooltip">
 *     <a class="item" href="/url">An Item</a>
 *     <a class="item disabled">Disabled</a>
 *     <a class="item active">Active item</a>
 *     <hr class="item separator"/>
 *     <a class="item disabled category">Category</a>
 *     <a class="item" href="/url"></a>
 * </div>
 */
angular.module('piwikApp').directive('piwikMenudropdown', function(){

    return {
        transclude: true,
        replace: true,
        restrict: 'A',
        scope: {
            menuTitle: '@',
            tooltip: '@',
        },
        templateUrl: 'plugins/CoreHome/angularjs/menudropdown/menudropdown.html?cb=' + piwik.cacheBuster
    };
});