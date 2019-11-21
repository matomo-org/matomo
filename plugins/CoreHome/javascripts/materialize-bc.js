/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    $(document).ready(function () {
        window.Materialize = window.M;
        $.fn.sideNav = $.fn.sidenav;
        $.fn.material_select = $.fn.formSelect;
    });
})();