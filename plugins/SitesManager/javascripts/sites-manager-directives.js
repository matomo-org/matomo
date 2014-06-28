/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').directive('sitesManagerMultilineField', function () {

    return {
        restrict: 'A',
        replace: true,
        scope: {
            managedValue: '=field',
            rows: '@?',
            cols: '@?'
        },
        templateUrl: 'plugins/SitesManager/templates/directives/multiline-field.html?cb=' + piwik.cacheBuster,
        link: function (scope) {

            var separator = '\n';

            var init = function () {

                scope.field = {};
                scope.onChange = updateManagedScopeValue;

                scope.$watch('managedValue', updateInputValue);
            };

            var updateManagedScopeValue = function () {
                scope.managedValue = scope.field.value.trim().split(separator);
            };

            var updateInputValue = function () {

                if(angular.isUndefined(scope.managedValue))
                    return;

                scope.field.value = scope.managedValue.join(separator);
            };

            init();
        }
    };
});

angular.module('piwikApp').directive('sitesManagerEditTrigger', function () {

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
});

angular.module('piwikApp').directive('sitesManagerScroll', function () {

    return {
        restrict: 'A',
        link: function (scope, element) {

            scope.$watch('site.editMode', function() {

                if(scope.site.editMode)
                    scrollToSite();
            });

            var scrollToSite = function() {
                piwikHelper.lazyScrollTo(element[0], 500, true);
            };
        }
    };
});
