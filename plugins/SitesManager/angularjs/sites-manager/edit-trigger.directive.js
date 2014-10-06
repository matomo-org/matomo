/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').directive('sitesManagerEditTrigger', sitesManagerEditTrigger);

    function sitesManagerEditTrigger() {

        return {
            restrict: 'A',
            link: function (scope, element) {

                element.bind('click', function(){

                    if(!scope.site.editMode)
                        scope.$apply(scope.editSite());
                });

                scope.$watch('site.editMode', function() {

                    element.toggleClass('editable-site-field', !scope.site.editMode);
                });
            }
        };
    }

})();
