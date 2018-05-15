/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 *
 * Usage:
 * <div piwik-goal-page-link="idGoal">
 */
(function () {
    angular.module('piwikApp.directive').directive('piwikGoalPageLink', piwikGoalPageLink);

    piwikGoalPageLink.$inject = ['$location', 'piwik'];

    function piwikGoalPageLink($location, piwik){

        return {
            restrict: 'A',
            compile: function (element, attrs) {

                if (attrs.piwikGoalPageLink && piwik.helper.isAngularRenderingThePage()) {
                    var title = element.text();
                    element.html('<a></a>');
                    var link =  element.find('a');
                    link.text(title);
                    link.attr('href', 'javascript:void(0)');
                    link.bind('click', function () {
                        var $search = $location.search();
                        $search.category = 'Goals_Goals';
                        $search.subcategory = encodeURIComponent(attrs.piwikGoalPageLink);
                        $location.search($search);
                    });
                }

                return function (scope, element, attrs) {

                };
            }
        };
    }
})();