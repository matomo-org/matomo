/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReportingMenuController', ReportingMenuController);

    ReportingMenuController.$inject = ['$scope', 'piwik', '$location', '$timeout', 'reportingMenuModel', '$rootScope'];

    function ReportingMenuController($scope, piwik, $location, $timeout, menuModel, $rootScope) {

        var idSite = getUrlParam('idSite');
        var period = getUrlParam('period');
        var date   = getUrlParam('date');
        var segment = getUrlParam('segment');

        function markAllCategoriesAsInactive()
        {
            angular.forEach(menuModel.menu, function (cat) {
                cat.active = false;
            });
        }

        function markAllCategoriesAndChildrenInactive()
        {
            angular.forEach(menuModel.menu, function (cat) {
                cat.active = false;
                angular.forEach(cat.subcategories, function (subcat) {
                    subcat.active = false;

                    if (subcat.isGroup && subcat.subcategories) {
                        angular.forEach(subcat.subcategories, function (sub) {
                            sub.active = false;
                        });

                    }
                });
            });
        }

        function getUrlParam(param)
        {
            var value = piwik.broadcast.getValueFromHash(param);
            if (!value) {
                value = piwik.broadcast.getValueFromUrl(param);
            }
            return value;
        }

        $scope.menuModel = menuModel;

        // highlight the currently hovered subcategory (and category)
        function enterSubcategory(category, subcategory, subsubcategory) {
            if (!category || !subcategory) {
                return;
            }

            markAllCategoriesAndChildrenInactive();

            category.active = true;
            subcategory.active = true;

            if (subsubcategory) {
                subcategory.name = subsubcategory.name;
                subsubcategory.active = true;
            }
        };

        $scope.makeUrl = function (category, subcategory) {

            var url = 'idSite=' + encodeURIComponent(idSite);
            url    += '&period=' + encodeURIComponent(period);
            url    += '&date=' + encodeURIComponent(date);
            url    += '&category=' + encodeURIComponent(category.id);
            url    += '&subcategory=' + encodeURIComponent(subcategory.id);

            if (segment) {
                url+= '&segment='+ segment;
            }
            return url;
        }

        $scope.loadCategory = function (category) {
            if (category.active) {
                category.active = false;
            } else {
                markAllCategoriesAsInactive();
                category.active = true;
            }

            if (category.active && category.subcategories && category.subcategories.length === 1) {
                var subcategory = category.subcategories[0];

                if (subcategory.active) {
                    // we need to manually trigger change as URL would not change and therefore page would not be
                    // reloaded
                    $scope.loadSubcategory(category, subcategory);
                } else {
                    var url = $scope.makeUrl(category, subcategory);
                    $location.search(url);
                }
            }
        };

        $scope.loadSubcategory = function (category, subcategory) {
            if (subcategory && subcategory.active) {
                // this menu item is already active, a location change success would not be triggered,
                // instead trigger an event
                $rootScope.$emit('loadPage', category.id, subcategory.id);
            }
        };

        menuModel.fetchMenuItems().then(function (menu) {
            if (!$location.search().subcategory) {
                // load first, initial page if no subcategory is present
                enterSubcategory(menu[0], menu[0].subcategories[0]);
                $location.search($scope.makeUrl(menu[0], menu[0].subcategories[0]));
            }
        });

        $rootScope.$on('updateReportingMenu', function () {
            menuModel.reloadMenuItems().then(function (menu) {
                var $search = $location.search();
                var category    = $search.category;
                var subcategory = $search.subcategory;
                // we need to make sure to select same categories again
                if (category && subcategory) {
                    var found = menuModel.findSubcategory(category, subcategory);
                    if (found) {
                        enterSubcategory(found.category, found.subcategory, found.subsubcategory);
                    }
                }
            });
            if ('object' === typeof widgetsHelper && widgetsHelper.availableWidgets) {
                // lets also update widgetslist so will be easier to update list of available widgets in dashboard selector
                // immediately
                delete widgetsHelper.availableWidgets;
                widgetsHelper.getAvailableWidgets();
            }
        });

        $rootScope.$on('$locationChangeSuccess', function () {
            var $search = $location.search();
            var category    = $search.category;
            var subcategory = $search.subcategory;

            period = getUrlParam('period');
            date   = getUrlParam('date');
            segment = getUrlParam('segment');

            if (!category || !subcategory) {
                return;
            }

            var found = menuModel.findSubcategory(category, subcategory);
            enterSubcategory(found.category, found.subcategory, found.subsubcategory);
        });

    }
})();
