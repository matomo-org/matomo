/**
 * Piwik - free/libre analytics platform
 *
 * Series Picker control addition for DataTable visualizations.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    /**
     * This class creates and manages the Series Picker for certain DataTable visualizations.
     *
     * To add the series picker to your DataTable visualization, create a SeriesPicker instance
     * and after your visualization has been rendered, call the 'init' method.
     *
     * To customize SeriesPicker placement and behavior, you can bind callbacks to the following
     * events before calling 'init':
     *
     * 'placeSeriesPicker': Triggered after the DOM element for the series picker link is created.
     *                      You must use this event to add the link to the dataTable. YOu can also
     *                      use this event to position the link however you want.
     *
     *                      Callback Signature: function () {}
     *
     * 'seriesPicked':      Triggered when the user selects one or more columns/rows.
     *
     *                      Callback Signature: function (eventInfo, columns, rows) {}
     *
     * Events are triggered via jQuery, so you bind callbacks to them like this:
     *
     * var picker = new SeriesPicker(dataTable);
     * $(picker).bind('placeSeriesPicker', function () {
     *   $(this.domElem).doSomething(...);
     * });
     *
     * @param {dataTable} dataTable  The dataTable instance to add a series picker to.
     * @constructor
     * @deprecated use the piwik-series-picker directive instead
     */
    var SeriesPicker = function (dataTable) {
        this.domElem = null;
        this.dataTableId = dataTable.workingDivId;

        // the columns that can be selected
        this.selectableColumns = dataTable.props.selectable_columns;

        // the rows that can be selected
        this.selectableRows = dataTable.props.selectable_rows;

        // render the picker?
        this.show = !! dataTable.props.show_series_picker
                 && (this.selectableColumns || this.selectableRows);

        // can multiple rows we selected?
        this.multiSelect = !! dataTable.props.allow_multi_select_series_picker;
    };

    SeriesPicker.prototype = {

        /**
         * Initializes the series picker by creating the element. Must be called when
         * the datatable the picker is being attached to is ready for it to be drawn.
         */
        init: function () {
            if (!this.show) {
                return;
            }

            var self = this;

            var selectedColumns = this.selectableColumns
                .filter(isItemDisplayed)
                .map(function (columnConfig) {
                    return columnConfig.column;
                });

            var selectedRows = this.selectableRows
                .filter(isItemDisplayed)
                .map(function (rowConfig) {
                    return rowConfig.matcher;
                });

            // initialize dom element
            var seriesPicker = '<piwik-series-picker'
                + ' multiselect="' + (this.multiSelect ? 'true' : 'false') + '"'
                + ' selectable-columns="selectableColumns"'
                + ' selectable-rows="selectableRows"'
                + ' selected-columns="selectedColumns"'
                + ' selected-rows="selectedRows"'
                + ' on-select="selectionChanged(columns, rows)"/>';

            this.domElem = $(seriesPicker); // TODO: don't know if this will work without a root scope

            $(this).trigger('placeSeriesPicker');

            piwikHelper.compileAngularComponents(this.domElem, {
                scope: {
                    selectableColumns: this.selectableColumns,
                    selectableRows: this.selectableRows,
                    selectedColumns: selectedColumns,
                    selectedRows: selectedRows,
                    selectionChanged: function selectionChanged(columns, rows) {
                        if (columns.length === 0 && rows.length === 0) {
                            return;
                        }

                        $(self).trigger('seriesPicked', [columns, rows]);

                        // inform dashboard widget about changed parameters (to be restored on reload)
                        var UI = require('piwik/UI');
                        var params = {
                            columns: columns,
                            columns_to_display: columns,
                            rows: rows,
                            rows_to_display: rows
                        };

                        var tableNode = $('#' + self.dataTableId);
                        UI.DataTable.prototype.notifyWidgetParametersChange(tableNode, params);
                    }
                }
            });

            function isItemDisplayed(columnOrRowConfig) {
                return columnOrRowConfig.displayed;
            }
        },

        /**
         * Returns the translation of a metric that can be selected.
         *
         * @param {String} metric The name of the metric, ie, 'nb_visits' or 'nb_actions'.
         * @return {String} The metric translation. If one cannot be found, the metric itself
         *                  is returned.
         */
        getMetricTranslation: function (metric) {
            for (var i = 0; i !== this.selectableColumns.length; ++i) {
                if (this.selectableColumns[i].column === metric) {
                    return this.selectableColumns[i].translation;
                }
            }
            return metric;
        }
    };

    var exports = require('piwik/DataTableVisualizations/Widgets');
    exports.SeriesPicker = SeriesPicker;

})(jQuery, require);
