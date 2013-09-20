/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    /**
     * The DataTableManager class manages the initialization of JS dataTable
     * instances. It's main purpose is to give each dataTable div a unique ID.
     * This is done in the browser since it's possible for any report to be
     * loaded via AJAX, some more than once.
     *
     * The singleton instance can be accessed via piwik.DataTableManager.
     */
    var DataTableManager = function () {
        this.nextId = 0;
    };

    DataTableManager.prototype = {

        /**
         * Gets the next available dataTable ID.
         *
         * @return {string}
         */
        getNextId: function () {
            this.nextId += 1;
            return 'dataTable_' + this.nextId;
        },

        /**
         * Gets the ID used for the last table or 0 if a DataTable hasn't been
         * initialized yet.
         */
        getLastId: function () {
            return 'dataTable_' + this.nextId;
        },

        /**
         * Initializes all uninitialized datatable elements. Uninitialized
         * datatable elements do not have an ID set.
         */
        initNewDataTables: function () {
            var self = this;

            // find each datatable that hasn't been initialized (has no id attribute),
            // and initialize it
            $('div.dataTable').each(function () {
                if (!$(this).attr('id')) {
                    var params = JSON.parse($(this).attr('data-params') || '{}');
                    var props = JSON.parse($(this).attr('data-props') || '{}');
                    var tableType = $(this).attr('data-table-type') || 'DataTable';

                    // convert values in params that are arrays to comma separated string lists
                    for (var key in params) {
                        if (params[key] instanceof Array) {
                            params[key] = params[key].join(',');
                        }
                    }
                    
                    var klass = require('piwik/UI')[tableType] || require(tableType);
                    self.initSingleDataTable(this, klass, params, props);
                }
            });
        },

        /**
         * Initializes a single datatable element.
         *
         * @param {Element} domElem The DataTable div element.
         * @param {Function} klass The DataTable's JS class.
         * @param {Object} params The request params used.
         * @param {Object} props The view properties that should be visible to the JS.
         */
        initSingleDataTable: function (domElem, klass, params, props) {
            var newId = this.getNextId();

            $(domElem).attr('id', newId);

            var table = new klass($(domElem));
            table.param = params;
            table.props = props;
            table.init();
        },

        /**
         * Returns the first datatable div displaying a specific report.
         *
         * @param {string} report  The report, eg, UserSettings.getWideScreen
         * @return {Element} The datatable div displaying the report, or undefined if
         *                   it cannot be found.
         */
        getDataTableByReport: function (report) {
            var result = undefined;
            $('.dataTable').each(function () {
                if ($(this).attr('data-report') == report) {
                    result = this;
                    return false;
                }
            });
            return result;
        },

        /**
         * Returns the datatable instance of the first datatable div displaying
         * a specific report.
         *
         * @param {string} report  The report, eg, UserSettings.getWideScrren
         * @return {dataTable} The DataTable instance created for the element, if
         *                     the element can be found. undefined, if it can't be found.
         */
        getDataTableInstanceByReport: function (report) {
            var dataTableElement = this.getDataTableByReport(report);
            return dataTableElement ? $(dataTableElement).data('uiControlObject') : undefined;
        }
    };

    piwik.DataTableManager = new DataTableManager();

}(jQuery, require));