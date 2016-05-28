/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Will activate the materialize side nav feature once rendered. We use this directive as it makes sure
 * the actual left menu is rendered at the time we init the side nav.
 *
 * Has to be set on a collaapsible element
 *
 * Example:
 * <div class="collapsible" piwik-side-nav="nav .activateLeftMenu">...</div>
 */
(function () {
    angular.module('piwikApp.directive').directive('piwikSideNav', piwikSideNav);

    piwikSideNav.$inject = ['$timeout'];
    var initialized = false;
    
    function piwikSideNav($timeout){
        return {
            restrict: 'A',
            priority: 10,
            link: function(scope, element, attr, ctrl) {

                if (attr.piwikSideNav) {
                    $timeout(function () {
                        if (!initialized) {
                            initialized = true;

                            var sideNavActivator = $(attr.piwikSideNav).show();

                            sideNavActivator.sideNav({
                                closeOnClick: true
                            });
                        }

                        if (element.hasClass('collapsible')) {
                            element.collapsible();
                        }
                    });
                }
            }
        };
    }
})();
