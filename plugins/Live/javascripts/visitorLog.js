/**
 * Matomo - free/libre analytics platform
 *
 * Visitor profile popup control.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI'),
        DataTable = exports.DataTable,
        dataTablePrototype = DataTable.prototype;

    /**
     * DataTable UI class for the Visitor Log visualization.
     *
     * @constructor
     */
    exports.VisitorLog = function (element) {
        DataTable.call(this, element);
    };

    $.extend(exports.VisitorLog.prototype, dataTablePrototype, {

        // the Visitor Log does not use column highlighting
        handleColumnHighlighting: function () {

        },

        // the Visitor Log manages its own column widths, so the inherited ellipsis fix is a no-op
        setFixWidthToMakeEllipsisWork: function () {

        },

        /**
         * Initializes this class.
         */
        init: function () {
            dataTablePrototype.init.call(this);

            var self = this;
            initializeVisitorActions(this.$element);

            // launch visitor profile on visitor profile link click
            this.$element.on('click', '.visitor-log-visitor-profile-link', function (e) {
                e.preventDefault();
                broadcast.propagateNewPopoverParameter('visitorProfile', $(this).attr('data-visitor-id'));
                return false;
            });

            // Delegated from the parent, because on a full-page report the action row this button
            // lives in is moved out of the datatable and into the report header (see
            // dataTable.js). The parent survives the reload that replaces $element, hence the
            // namespaced off() - init runs again for every reload.
            this.$element.parent()
                .off('click.visitorLogAddSegment')
                .on('click.visitorLogAddSegment', '.addSegmentToMatomo.dataTableAction', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var url = window.location.href;
                    url = broadcast.updateParamValue('addSegmentAsNew=' + self.param.segment, url);
                    url = broadcast.updateParamValue('popover=', url);
                    // Show user the Visits Log so that they can easily refine their new segment if needed
                    url = broadcast.updateParamValue('category=General_Visitors', url);
                    url = broadcast.updateParamValue('subcategory=Live_VisitorLog', url);
                    url = broadcast.updateParamValue('segment=' + self.param.segment, url);

                    window.open(url, '_blank');
                });
        },

        _destroy: function () {
            try {
                this.$element.tooltip('destroy');
            } catch (e) {
                // ignore
            }

            dataTablePrototype._destroy.call(this);
        }
    });

})(jQuery, require);
