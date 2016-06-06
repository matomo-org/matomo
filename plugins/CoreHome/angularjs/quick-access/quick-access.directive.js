/*!
 * Piwik - free/libre analytics platform
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
(function () {
    angular.module('piwikApp').directive('piwikQuickAccess', QuickAccessDirective);

    QuickAccessDirective.$inject = ['$rootElement', '$timeout', 'piwik', '$filter'];

    function QuickAccessDirective ($rootElement, $timeout, piwik, $filter) {

        return {
            restrict: 'A',
            replace: true,
            scope: {},
            templateUrl: 'plugins/CoreHome/angularjs/quick-access/quick-access.directive.html?cb=' + piwik.cacheBuster,
            controller: 'QuickAccessController',
            controllerAs: 'quickAccess',
            link: function (scope, element, attrs) {

                var menuIndex = -1; // the menu index is used to identify which element to click
                var topMenuItems = []; // cache for top menu items
                var leftMenuItems = []; // cache for left menu items
                var segmentItems = []; // cache for segment items
                var hasSegmentSelector = angular.element('.segmentEditorPanel').length;
                scope.hasSitesSelector = angular.element('.top_controls [piwik-siteselector]').length;


                var translate = $filter('translate');
                var searchAreasTitle = '';
                var searchAreas = [translate('CoreHome_MenuEntries')]

                if (hasSegmentSelector) {
                    searchAreas.push(translate('CoreHome_Segments'))
                }

                if (scope.hasSitesSelector) {
                    searchAreas.push(translate('SitesManager_Sites'))
                }

                while (searchAreas.length) {
                    searchAreasTitle += searchAreas.shift();
                    if (searchAreas.length >= 2) {
                        searchAreasTitle += ', ';
                    } else if (searchAreas.length === 1) {
                        searchAreasTitle += ' ' + translate('General_And') + ' ';
                    }
                }

                scope.quickAccessTitle = translate('CoreHome_QuickAccessTitle', searchAreasTitle);

                function trim(str) {
                    return str.replace(/^\s+|\s+$/g,'');
                }

                scope.getTopMenuItems = function()
                {
                    if (topMenuItems && topMenuItems.length) {
                        return topMenuItems;
                    }

                    var category = _pk_translate('CoreHome_Menu');

                    $rootElement.find('#topRightBar .navbar-right li > a').each(function (index, element) {
                        var $element = $(element);

                        if ($element.is('#topmenu-usersmanager')) {
                            // ignore languages manager
                            return;
                        }

                        var text = trim($element.text());

                        if (!text) {
                            text = trim($element.attr('title')); // possibly a icon, use title instead
                        }

                        if (text) {
                            topMenuItems.push({name: text, index: ++menuIndex, category: category});
                            $element.attr('quick_access', menuIndex);
                        }
                    });

                    return topMenuItems;
                };

                scope.getLeftMenuItems = function ()
                {
                    if (leftMenuItems && leftMenuItems.length) {
                        return leftMenuItems;
                    }

                    $rootElement.find('#secondNavBar .menuTab').each(function (index, element) {
                        var $element = angular.element(element);
                        var category = trim($element.find('> .item').text());

                        if (category && -1 !== category.lastIndexOf("\n")) {
                            // remove "\n\nMenu"
                            category = trim(category.substr(0, category.lastIndexOf("\n")));
                        }

                        $element.find('li .item').each(function (i, element) {
                            var $element = angular.element(element);
                            var text = trim($element.text());

                            if (text) {
                                leftMenuItems.push({name: text, category: category, index: ++menuIndex});
                                $element.attr('quick_access', menuIndex);
                            }
                        })

                    });

                    return leftMenuItems;
                };

                scope.getSegmentItems = function()
                {
                    if (!hasSegmentSelector) {
                        return [];
                    }

                    if (segmentItems && segmentItems.length) {
                        return segmentItems;
                    }

                    var category = _pk_translate('CoreHome_Segments');

                    $rootElement.find('.segmentList [data-idsegment]').each(function (index, element) {
                        var $element = angular.element(element);
                        var text = trim($element.find('.segname').text());

                        if (text) {
                            segmentItems.push({name: text, category: category, index: ++menuIndex});
                            $element.attr('quick_access', menuIndex);
                        }
                    });

                    return segmentItems;
                };

                scope.activateSearch = function()
                {
                    scope.$eval('view.searchActive = true');
                    $timeout(function () {
                        scope.$apply();
                    }, 0);
                };

                scope.deactivateSearch = function()
                {
                    scope.$eval('search.term = ""');
                    scope.$eval('view.searchActive = false');
                    element.find('input').blur();
                    $timeout(function () {
                        scope.$apply();
                    }, 0);
                };

                function isElementInViewport(element) {

                    var rect = element.getBoundingClientRect();

                    return (
                        rect.top >= 0 &&
                        rect.left >= 0 &&
                        rect.bottom <= $(window).height() &&
                        rect.right <= $(window).width()
                    );
                }

                function getCurrentlySelectedElement(index)
                {
                    var results = element.find('li.result');
                    if (results && results.length && results[scope.search.index]) {
                        return $(results[scope.search.index]);
                    }
                }

                function makeSureSelectedItemIsInViewport() {
                    var element = getCurrentlySelectedElement();

                    if (element && element[0] && !isElementInViewport(element[0])) {
                        scrollFirstElementIntoView(element);
                    }
                }

                function scrollFirstElementIntoView(element)
                {
                    if (element && element[0] && element[0].scrollIntoView) {
                        // make sure search is visible
                        element[0].scrollIntoView();
                    }
                }

                scope.highlightPreviousItem = function()
                {
                    if (0 >= (scope.search.index - 1)) {
                        scope.search.index = 0;
                    } else {
                        scope.search.index--;
                    }
                    makeSureSelectedItemIsInViewport();
                };

                scope.resetSearchIndex = function () {
                    scope.search.index = 0;
                    makeSureSelectedItemIsInViewport();
                };

                scope.highlightNextItem = function()
                {
                    var numTotal = element.find('li.result').length;

                    if (numTotal <= (scope.search.index + 1)) {
                        scope.search.index = numTotal - 1;
                    } else {
                        scope.search.index++;
                    }

                    makeSureSelectedItemIsInViewport();
                };

                scope.clickQuickAccessMenuItem = function()
                {
                    var selectedMenuElement = getCurrentlySelectedElement();
                    if (selectedMenuElement) {
                        $timeout(function () {
                            selectedMenuElement.click();
                        }, 20);
                    }
                };

                scope.selectMenuItem = function(index)
                {
                    var target = $rootElement.find('[quick_access=' + index + ']');

                    if (target && target.length && target[0]) {
                        scope.deactivateSearch();

                        var actualTarget = target[0];

                        var href = $(actualTarget).attr('href');

                        if (href && href.length > 10 && actualTarget && actualTarget.click) {
                            try {
                                actualTarget.click();
                            } catch (e) {
                                $(actualTarget).click();
                            }
                        } else {
                            $(actualTarget).click();
                        }
                    }
                };

                Mousetrap.bind('f', function(event) {
                    if (event.preventDefault) {
                        event.preventDefault();
                    } else {
                        event.returnValue = false; // IE
                    }

                    scrollFirstElementIntoView(element);

                    scope.activateSearch();
                });

            }
        };
    }
})();
