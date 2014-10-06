/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-menudropdown menu-title="MyMenuItem" tooltip="My Tooltip" show-search="false">
 *     <a class="item" href="/url">An Item</a>
 *     <a class="item disabled">Disabled</a>
 *     <a class="item active">Active item</a>
 *     <hr class="item separator"/>
 *     <a class="item disabled category">Category</a>
 *     <a class="item" href="/url"></a>
 * </div>
 */
(function () {
    angular.module('piwikApp').directive('piwikMenudropdown', piwikMenudropdown);

    function piwikMenudropdown(){

        return {
            transclude: true,
            replace: true,
            restrict: 'A',
            scope: {
                menuTitle: '@',
                tooltip: '@',
                showSearch: '=',
                menuTitleChangeOnClick: '='
            },
            templateUrl: 'plugins/CoreHome/angularjs/menudropdown/menudropdown.directive.html?cb=' + piwik.cacheBuster,
            link: function(scope, element, attrs) {

                element.find('.item').on('click', function () {
                    var $self = angular.element(this);

                    if ($self.hasClass('disabled') || $self.hasClass('separator')) {
                        return;
                    }

                    if (scope.menuTitleChangeOnClick !== false) {
                        scope.menuTitle = $self.text().replace(/[\u0000-\u2666]/g, function(c) {
                            return '&#'+c.charCodeAt(0)+';';
                        });
                    }
                    scope.$eval('view.showItems = false');
                    scope.$apply();

                    element.find('.item').removeClass('active');
                    $self.addClass('active');
                });

                scope.searchItems = function (searchTerm)
                {
                    searchTerm = searchTerm.toLowerCase();

                    element.find('.item').each(function (index, node) {
                        var $node = angular.element(node);

                        if (-1 === $node.text().toLowerCase().indexOf(searchTerm)) {
                            $node.hide();
                        } else {
                            $node.show();
                        }
                    });
                };
            }
        };
    }
})();
