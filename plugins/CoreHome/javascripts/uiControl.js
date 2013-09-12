/**
 * Piwik - Web Analytics
 *
 * Visitor profile popup control.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI');

    /**
     * Base type for Piwik UI controls. Provides functionality that all controls need (such as
     * cleanup on destruction).
     * 
     * @param {Element} element The root element of the control.
     */
    var UIControl = function (element) {
        this._controlIndex = UIControl._controls.length;
        UIControl._controls.push(this);

        var $element = this.$element = $(element);
        $element.data('uiControlObject', this);
    };

    /**
     * Contains all active control instances.
     */
    UIControl._controls = [];

    /**
     * Utility method that will clean up all piwik UI controls whose elements are not attached
     * to the DOM.
     * 
     * TODO: instead of having other pieces of the UI manually calling cleanupUnusedControls,
     *       MutationObservers should be called
     */
    UIControl.cleanupUnusedControls = function () {
        var controls = UIControl._controls;

        for (var i = 0; i != controls.length; ++i) {
            var control = controls[i];
            if (control.$element
                && !$.contains(document.documentElement, control.$element[0])
            ) {
                controls[i] = null;
                control._destroy();

                if (!control._baseDestroyCalled) {
                    throw new Error("Error: " + control.constructor.name + "'s destroy method does not call " +
                                    "UIControl.destroy. You may have a memory leak.");
                }
            }
        }
    };

    UIControl.initElements = function (klass, selector) {
        $(selector).each(function () {
            if (!$(this).attr('data-inited')) {
                var control = new klass(this);
                $(this).attr('data-inited', 1);
            }
        });
    };

    UIControl.prototype = {

        /**
         * Perform cleanup. Called when the control has been removed from the DOM. Derived
         * classes should overload this function to perform their own cleanup.
         */
        _destroy: function () {
            this.$element.removeData('uiControlObject');
            delete this.$element;

            this._baseDestroyCalled = true;
        },
    };

    exports.UIControl = UIControl;

})(jQuery, require);