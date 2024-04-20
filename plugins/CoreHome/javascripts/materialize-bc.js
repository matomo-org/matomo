/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    $(document).ready(function () {
        window.Materialize = window.M;
        $.fn.sideNav = $.fn.sidenav;
        $.fn.material_select = $.fn.formSelect;

        // we load jquery-ui after materialize so we can use the jquery-ui datepicker, but
        // some controls in materialize get overwritten too. so we undo that here.
        M.initializeJqueryWrapper(M.Tabs, 'tabs', 'M_Tabs');
        M.initializeJqueryWrapper(M.Modal, 'modal', 'M_Modal');
    });
})();
