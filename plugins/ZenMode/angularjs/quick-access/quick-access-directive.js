/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-dialog="showDialog">...</div>
 * Will show dialog once showDialog evaluates to true.
 *
 * Will execute the "executeMyFunction" function in the current scope once the yes button is pressed.
 */
angular.module('piwikApp.directive').directive('piwikQuickAccess', function($rootElement, $timeout, siteSelectorModel) {

    return {
        restrict: 'A',
        replace: true,
        scope: {},
        templateUrl: 'plugins/ZenMode/angularjs/quick-access/quick-access.html?cb=' + piwik.cacheBuster,
        link: function (scope, element, attrs) {

            var menuIndex = -1;

            function highlightPreviousItem()
            {
                if (0 >= (scope.search.index - 1)) {
                    scope.search.index = 0;
                } else {
                    scope.search.index--;
                }
            }

            function highlightNextItem()
            {
                var numTotal = element.find('li.result').length;

                if (numTotal <= (scope.search.index + 1)) {
                    scope.search.index = numTotal - 1;
                } else {
                    scope.search.index++;
                }
            }

            function executeMenuItem()
            {
                var results = element.find('li.result');
                if (results && results.length && results[scope.search.index]) {
                    var selectedMenuElement = $(results[scope.search.index]);
                    $timeout(function () {
                        selectedMenuElement.click();
                    }, 20);
                }
            }

            siteSelectorModel.loadInitialSites();

            scope.menuItems = getMenuItems();
            scope.reportEntries = getReportEntries();
            scope.sitesModel = siteSelectorModel;

            scope.onKeypress = function (event) {

                if (38 == event.which) {
                    highlightPreviousItem();
                } else if (40 == event.which) {
                    highlightNextItem();
                } else if (13 == event.which) {
                    executeMenuItem();
                }
            };

            scope.search = function (searchTerm) {
                this.search.index = 0;
                this.sitesModel.searchSite(searchTerm);
            };

            scope.selectSite = function (idsite) {
                this.sitesModel.loadSite(idsite);
            };

            scope.selectMenuItem = function (index) {
                var target = $rootElement.find('[quick_access=' + index + ']');

                if (target && target.length && target[0]) {
                    var actualTarget = target[0];

                    var href = $(actualTarget).attr('href');

                    if (href && href.length > 10) {
                        actualTarget.click();
                    } else {
                        $(actualTarget).click();
                    }
                }
            };

        }
    };
});