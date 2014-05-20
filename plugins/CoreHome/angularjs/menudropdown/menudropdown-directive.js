/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-menudropdown title="MyMenuItem">
 *     <a class="item" href="/url"></a>
 *     <a class="item active">test</a>
 *     <a class="item disabled">-------</a>
 *     <a class="item" href="/url"></a>
 * </div>
 */
angular.module('piwikApp').directive('piwikMenudropdown', function(){

    return {
        transclude: true,
        replace: true,
        restrict: 'A',
        scope: {
            title: '@'
        },
        templateUrl: 'plugins/CoreHome/angularjs/menudropdown/menudropdown.html?cb=' + piwik.cacheBuster
    };
});