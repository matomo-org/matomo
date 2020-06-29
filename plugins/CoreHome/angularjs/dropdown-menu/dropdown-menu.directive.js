/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * A materializecss dropdown menu that supports submenus.
 *
 * To use a submenu, just use this directive within another dropdown.
 *
 * Note: if submenus are used, then dropdowns will never scroll.
 *
 * Usage:
 * <a class='dropdown-trigger btn' href='' data-target='mymenu' piwik-dropdown-menu>Menu</a>
 * <ul id='mymenu' class='dropdown-content'>
 *     <li>
 *         <a class='dropdown-trigger' data-target="mysubmenu" piwik-dropdown-menu>Submenu</a>
 *         <ul id="mysubmenu" class="dropdown-content">
 *             <li>Submenu Item</li>
 *         </ul>
 *     </li>
 *     <li>
 *         <a href="">Another item</a>
 *     </li>
 * </ul>
 */
(function () {
    angular.module('piwikApp').directive('piwikDropdownMenu', piwikDropdownMenu);

    piwikDropdownMenu.$inject = ['$timeout'];

    function piwikDropdownMenu($timeout){
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                var options = {};

                var isSubmenu = !! element.parent().closest('.dropdown-content').length;
                if (isSubmenu) {
                    options = { hover: true };
                    element.addClass('submenu');
                    angular.element('#' + attrs.activates).addClass('submenu-dropdown-content');

                    // if a submenu is used, the dropdown will never scroll
                    element.parents('.dropdown-content').addClass('submenu-container');
                }

                $timeout(function () {
                    element.dropdown(options);
                });
            }
        };
    }
})();