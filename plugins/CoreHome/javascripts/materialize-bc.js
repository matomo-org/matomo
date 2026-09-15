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

        // A dismissible modal traps focus by refocusing itself whenever focus lands outside its
        // own subtree. Controls that render at page level but belong to a field inside the modal
        // - the expandable select teleports its option list to <body> so scrolling ancestors
        // cannot clip it - are outside that subtree, so their inputs could never hold focus and
        // keystrokes reached the modal instead. Let such an element opt out by marking itself.
        // _handleFocus is private API and materialize is pinned on a caret range, so a minor
        // upgrade could rename it. Only wrap it when it is there: without the guard the wrapper
        // would still install and throw on every focus event while a modal is open, breaking the
        // modal outright rather than just losing the opt-out.
        var handleFocus = M.Modal && M.Modal.prototype && M.Modal.prototype._handleFocus;

        if (typeof handleFocus === 'function') {
            M.Modal.prototype._handleFocus = function (event) {
                var target = event && event.target;
                var escapee = target && target.closest
                    ? target.closest('[data-matomo-modal-escapee]')
                    : null;

                // Only the modal the element names lets it through. Exempting it from every trap
                // would let an element belonging to an underlying modal hold focus over the one
                // stacked on top of it, which is the case Materialize's own _nthModalOpened test
                // is there to handle.
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
