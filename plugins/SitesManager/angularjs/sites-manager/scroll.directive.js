/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').directive('sitesManagerScroll', sitesManagerScroll);

    sitesManagerScroll.$inject = ['piwik'];

    function sitesManagerScroll (piwik) {

        return {
            restrict: 'A',
            link: function (scope, element) {

                function scrollToSite () {
                    piwik.helper.lazyScrollTo(element[0], 500, true);
                }

                scope.$watch('site.editMode', function() {

                    if(scope.site.editMode)
                        scrollToSite();
                });

            }
        };
    }

})();
