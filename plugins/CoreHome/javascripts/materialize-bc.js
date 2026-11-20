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

        // A dismissible modal refocuses itself whenever focus lands outside its own subtree, so a
        // control rendered at page level but belonging to a field inside it - the expandable
        // select teleports its option list to <body> - can never hold focus. Let it opt out.
        // Guarded because _handleFocus is private API under a caret range: unwrapped, a rename
        // would throw on every focus event rather than just losing the opt-out.
        var handleFocus = M.Modal && M.Modal.prototype && M.Modal.prototype._handleFocus;

        if (typeof handleFocus === 'function') {
            M.Modal.prototype._handleFocus = function (event) {
                var target = event && event.target;
                var escapee = target && target.closest
                    ? target.closest('[data-matomo-modal-escapee]')
                    : null;

                // only the modal the element names lets it through, or one belonging to an
                // underlying modal could hold focus over the modal stacked on top of it
                if (
                    escapee
                    && this.el
                    && escapee.getAttribute('data-matomo-modal-escapee')
                        === this.el.getAttribute('data-matomo-modal-id')
                ) {
                    return;
                }

                handleFocus.call(this, event);
            };
        }
    });
})();
