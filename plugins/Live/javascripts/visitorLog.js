/**
 * Piwik - free/libre analytics platform
 *
 * Visitor profile popup control.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI'),
        DataTable = exports.DataTable,
        dataTablePrototype = DataTable.prototype;

    /**
     * DataTable UI class for jqPlot graph datatable visualizations.
     *
     * @constructor
     */
    exports.VisitorLog = function (element) {
        DataTable.call(this, element);
    };

    $.extend(exports.VisitorLog.prototype, dataTablePrototype, {

        handleColumnHighlighting: function () {

        },

        setFixWidthToMakeEllipsisWork: function () {

        },

        /**
         * Initializes this class.
         */
        init: function () {
            dataTablePrototype.init.call(this);

            initializeVisitorActions(this.$element);

            // launch visitor profile on visitor profile link click
            this.$element.on('click', '.visitor-log-visitor-profile-link', function (e) {
                e.preventDefault();
                broadcast.propagateNewPopoverParameter('visitorProfile', $(this).attr('data-visitor-id'));
                return false;
            });
        }
    });

})(jQuery, require);
