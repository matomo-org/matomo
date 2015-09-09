/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('QuickAccessController', QuickAccessController);

    QuickAccessController.$inject = ['$scope', '$filter', 'siteSelectorModel'];

    function QuickAccessController($scope, $filter, siteSelectorModel){

        this.menuItems = [];
        this.numMenuItems = 0;
        this.sitesModel = siteSelectorModel;

        this.onKeypress = function (event) {
            if (38 == event.which) {
                $scope.highlightPreviousItem();
                event.preventDefault();
            } else if (40 == event.which) {
                $scope.highlightNextItem();
                event.preventDefault();
            } else if (13 == event.which) {
                $scope.clickQuickAccessMenuItem();
            }
        };

        this.searchMenu = function (searchTerm) {
            searchTerm = searchTerm.toLowerCase();

            var index = -1;
            var menuItemsIndex = {};
            var menuItems = [];

            var moveToCategory = function (i, submenuItem) {
                submenuItem = angular.copy(submenuItem); // force rerender of element to prevent weird side effects
                submenuItem.menuIndex = ++index; // needed for proper highlighting with arrow keys

                var category = submenuItem.category;
                if (!(category in menuItemsIndex)) {
                    menuItems.push({title: category, items: []});
                    menuItemsIndex[category] = menuItems.length - 1;
                }

                var indexOfCategory = menuItemsIndex[category];
                menuItems[indexOfCategory].items.push(submenuItem);
            };

            $scope.resetSearchIndex();

            if ($scope.hasSitesSelector) {
                this.sitesModel.searchSite(searchTerm);
            }

            var topMenuItems  = $filter('filter')($scope.getTopMenuItems(), searchTerm);
            var leftMenuItems = $filter('filter')($scope.getLeftMenuItems(), searchTerm);
            var segmentItems  = $filter('filter')($scope.getSegmentItems(), searchTerm);

            $.each(topMenuItems, moveToCategory);
            $.each(leftMenuItems, moveToCategory);
            $.each(segmentItems, moveToCategory);

            this.numMenuItems = topMenuItems.length + leftMenuItems.length + segmentItems.length;
            this.menuItems = menuItems;
        };

        this.selectSite = function (idsite) {
            this.sitesModel.loadSite(idsite);
        };

        this.selectMenuItem = function (index) {
            $scope.selectMenuItem(index);
        };

    }
})();
