/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').factory('reportingMenuModel', reportingMenuModelService);

    reportingMenuModelService.$inject = ['$filter', '$q', 'reportingPagesModel', '$location'];

    function reportingMenuModelService ($filter, $q, reportingPagesModel, $location) {

        // those sites are going to be displayed
        var model = {
            menu: [],
            fetchMenuItems: fetchMenuItems,
            reloadMenuItems: reloadMenuItems,
            findSubcategory: findSubcategory
        };

        return model;


        function buildMenuFromPages(pages)
        {
            var menu = [];

            var activeCategory = $location.search().category;
            var activeSubcategory = $location.search().subcategory;

            var categoriesHandled = {};
            angular.forEach(pages, function (page, key) {
                var category   = page.category;
                var categoryId = category.id;

                if (categoriesHandled[categoryId]) {
                    return;
                }

                categoriesHandled[categoryId] = true;

                if (activeCategory && category.id === activeCategory) {
                    // this doesn't really belong here but placed it here for convenience
                    category.active = true;
                }

                category.subcategories = [];

                var categoryGroups = false;

                angular.forEach(pages, function (page, key) {
                    if (page.category.id === categoryId) {
                        var subcategory = page.subcategory;

                        if (subcategory.id === activeSubcategory && categoryId === activeCategory) {
                            subcategory.active = true;
                        }

                        if (page.widgets && page.widgets[0] && isNumeric(page.subcategory.id)) {
                            // we handle a goal
                            if (!categoryGroups) {
                                categoryGroups = angular.copy(subcategory);
                                categoryGroups.name = $filter('translate')('CoreHome_ChooseX', [category.name]);
                                categoryGroups.isGroup = true;
                                categoryGroups.subcategories = [];
                                categoryGroups.order = 10;
                            }

                            if (subcategory.active) {
                                categoryGroups.name = subcategory.name;
                                categoryGroups.active = true;
                            }

                            var goalId = page.subcategory.id;
                            subcategory.tooltip = subcategory.name + ' (id = ' + goalId + ' )';

                            categoryGroups.subcategories.push(subcategory);
                            return;
                        }

                        category.subcategories.push(subcategory);
                    }
                });

                if (categoryGroups && categoryGroups.subcategories && categoryGroups.subcategories.length <= 5) {
                    angular.forEach(categoryGroups.subcategories, function (subcategory) {
                        category.subcategories.push(subcategory);
                    });
                } else if(categoryGroups) {
                    category.subcategories.push(categoryGroups);
                }

                category.subcategories = sortMenuItems(category.subcategories);

                menu.push(category);

                return menu;
            });

            menu = sortMenuItems(menu);

            return menu;
        }

        function sortMenuItems(menu) {
            return $filter('orderBy')(menu, 'order');
        };
    }
})();
