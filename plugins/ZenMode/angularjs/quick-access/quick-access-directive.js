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
angular.module('piwikApp.directive').directive('piwikQuickAccess', function($rootElement, $timeout) {

    return {
        restrict: 'A',
        replace: true,
        scope: {},
        templateUrl: 'plugins/ZenMode/angularjs/quick-access/quick-access.html?cb=' + piwik.cacheBuster,
        link: function (scope, element, attrs) {

            var menuIndex = -1;

            function getMenuEntries()
            {
                var menuEntries = [];

                $rootElement.find('#topLeftBar .topBarElem a, #topRightBar .topBarElem a').each(function (index, element) {
                    menuEntries.push({name: $(element).text(), index: ++menuIndex, category: 'menu'});
                    $(element).attr('quick_access', menuIndex);
                });

                return menuEntries;
            }
            function getReportEntries()
            {
                var reportEntries = [];

                $rootElement.find('.Menu-tabList a').each(function (index, element) {
                    reportEntries.push({name: $(element).text(), menu: 'Report', index: ++menuIndex});
                    $(element).attr('quick_access', menuIndex);
                });

                return reportEntries;
            }

            scope.menuEntries = getMenuEntries();
            scope.reportEntries = getReportEntries();

            scope.onKeypress = function (event) {

                if (38 == event.which) {

                    if (0 >= (this.search.index - 1)) {
                        this.search.index = 0;
                    } else {
                        this.search.index--;
                    }
                } else if (40 == event.which) {
                    // down
                    var numTotal = element.find('li.result').length;

                    if (numTotal <= (this.search.index + 1)) {
                        this.search.index = numTotal - 1;
                    } else {
                        this.search.index++;
                    }
                } else if (13 == event.which) {
                    var results = element.find('li.result');
                    if (results && results.length && results[this.search.index]) {
                        var selectedMenuElement = $(results[this.search.index]);
                        $timeout(function () {
                            selectedMenuElement.click();
                        }, 20);
                    }
                }
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