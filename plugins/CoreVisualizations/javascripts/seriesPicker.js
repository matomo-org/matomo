/**
 * Piwik - free/libre analytics platform
 *
 * Series Picker control addition for DataTable visualizations.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, doc, require) {

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

        // language strings
        this.lang =
        {
            metricsToPlot: _pk_translate('General_MetricsToPlot'),
            metricToPlot: _pk_translate('General_MetricToPlot'),
            recordsToPlot: _pk_translate('General_RecordsToPlot')
        };

        this._pickerState = null;
        this._pickerPopover = null;
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

            // initialize dom element
            this.domElem = $(doc.createElement('a'))
                .addClass('jqplot-seriespicker')
                .attr('href', '#')
                .html('+')

                // set opacity on 'hide'
                .on('hide', function () {
                    $(this).css('opacity', .55);
                })
                .trigger('hide')

                // show picker on hover
                .hover(
                    function () {
                        var $this = $(this);

                        $this.css('opacity', 1);
                        if (!$this.hasClass('open')) {
                            $this.addClass('open');
                            self._showPicker();
                        }
                    },
                    function () {
                        // do nothing on mouseout because using this event doesn't work properly.
                        // instead, the timeout check beneath is used (_bindCheckPickerLeave()).
                    }
                )
                .click(function (e) {
                    e.preventDefault();
                    return false;
                });

            $(this).trigger('placeSeriesPicker');
        },

        /**
         * Returns the translation of a metric that can be selected.
         *
         * @param {String} metric The name of the metric, ie, 'nb_visits' or 'nb_actions'.
         * @return {String} The metric translation. If one cannot be found, the metric itself
         *                  is returned.
         */
        getMetricTranslation: function (metric) {
            for (var i = 0; i != this.selectableColumns.length; ++i) {
                if (this.selectableColumns[i].column == metric) {
                    return this.selectableColumns[i].translation;
                }
            }
            return metric;
        },

        /**
         * Creates the popover DOM element, binds event handlers to it, and then displays it.
         */
        _showPicker: function () {
            this._pickerState = {manipulated: false};
            this._pickerPopover = this._createPopover();

            this._positionPopover();

            // hide and replot on mouse leave
            var self = this;
            this._bindCheckPickerLeave(function () {
                var replot = self._pickerState.manipulated;
                self._hidePicker(replot);
            });
        },

        /**
         * Creates a checkbox and related elements for a selectable column or selectable row.
         */
        _createPickerPopupItem: function (config, type) {
            var self = this;

            if (type == 'column') {
                var columnName = config.column,
                    columnLabel = config.translation,
                    cssClass = 'pickColumn';
            } else {
                var columnName = config.matcher,
                    columnLabel = config.label,
                    cssClass = 'pickRow';
            }

            var checkbox = $(document.createElement('input')).addClass('select')
                .attr('type', this.multiSelect ? 'checkbox' : 'radio');

            if (config.displayed && !(!this.multiSelect && this._pickerState.oneChecked)) {
                checkbox.prop('checked', true);
                this._pickerState.oneChecked = true;
            }

            // if we are rendering a column, remember the column name
            // if it's a row, remember the string that can be used to match the row
            checkbox.data('name', columnName);

            var el = $(document.createElement('p'))
                .append(checkbox)
                .append($('<label/>').text(columnLabel))
                .addClass(cssClass);

            var replot = function () {
                self._unbindPickerLeaveCheck();
                self._hidePicker(true);
            };

            var checkBox = function (box) {
                if (!self.multiSelect) {
                    self._pickerPopover.find('input.select:not(.current)').prop('checked', false);
                }
                box.prop('checked', true);
                replot();
            };

            el.click(function (e) {
                self._pickerState.manipulated = true;
                var box = $(this).find('input.select');
                if (!$(e.target).is('input.select')) {
                    if (box.is(':checked')) {
                        box.prop('checked', false);
                    } else {
                        checkBox(box);
                    }
                } else {
                    if (box.is(':checked')) {
                        checkBox(box);
                    }
                }
            });

            return el;
        },

        /**
         * Binds an event to document that checks if the user has left the series picker.
         */
        _bindCheckPickerLeave: function (onLeaveCallback) {
            var offset = this._pickerPopover.offset();
            var minX = offset.left;
            var minY = offset.top;
            var maxX = minX + this._pickerPopover.outerWidth();
            var maxY = minY + this._pickerPopover.outerHeight();

            var self = this;
            this._onMouseMove = function (e) {
                var currentX = e.pageX, currentY = e.pageY;
                if (currentX < minX || currentX > maxX
                    || currentY < minY || currentY > maxY
                ) {
                    self._unbindPickerLeaveCheck();
                    onLeaveCallback();
                }
            };

            $(doc).mousemove(this._onMouseMove);
        },

        /**
         * Unbinds the callback that was bound in _bindCheckPickerLeave.
         */
        _unbindPickerLeaveCheck: function () {
            $(doc).unbind('mousemove', this._onMouseMove);
        },

        /**
         * Removes and destroys the popover dom element. If any columns/rows were selected, the
         * 'seriesPicked' event is triggered.
         */
        _hidePicker: function (replot) {
            // hide picker
            this._pickerPopover.hide();
            this.domElem.trigger('hide').removeClass('open');

            // replot
            if (replot) {
                var columns = [];
                var rows = [];
                this._pickerPopover.find('input:checked').each(function () {
                    if ($(this).closest('p').hasClass('pickRow')) {
                        rows.push($(this).data('name'));
                    } else {
                        columns.push($(this).data('name'));
                    }
                });

                var noRowSelected = this._pickerPopover.find('.pickRow').size() > 0
                                 && this._pickerPopover.find('.pickRow input:checked').size() == 0;
                if (columns.length > 0 && !noRowSelected) {
                    $(this).trigger('seriesPicked', [columns, rows]);

                    // inform dashboard widget about changed parameters (to be restored on reload)
                    var UI = require('piwik/UI')
                    var params = {columns: columns,  columns_to_display: columns,
                                  rows: rows, rows_to_display: rows};
                    var tableNode = $('#' + this.dataTableId);
                    UI.DataTable.prototype.notifyWidgetParametersChange(tableNode, params);
                }
            }

            this._pickerPopover.remove();
        },

        /**
         * Creates and returns the popover element. This element shows a list of checkboxes, one
         * for each selectable column/row.
         */
        _createPopover: function () {
            var hasColumns = $.isArray(this.selectableColumns) && this.selectableColumns.length;
            var hasRows    = $.isArray(this.selectableRows) && this.selectableRows.length;

            var popover = $('<div/>')
                .addClass('jqplot-seriespicker-popover');

            // create headline element
            var title = this.multiSelect ? this.lang.metricsToPlot : this.lang.metricToPlot;
            popover.append($('<p/>').addClass('headline').html(title));

            // create selectable columns list
            if (hasColumns) {
                for (var i = 0; i < this.selectableColumns.length; i++) {
                    var column = this.selectableColumns[i];
                    popover.append(this._createPickerPopupItem(column, 'column'));
                }
            }

            // create selectable rows list
            if (hasRows) {
                // "records to plot" subheadline
                var header = $('<p/>').addClass('headline').addClass('recordsToPlot').html(this.lang.recordsToPlot);
                popover.append(header);

                // render the selectable rows
                for (var i = 0; i < this.selectableRows.length; i++) {
                    var row = this.selectableRows[i];
                    popover.append(this._createPickerPopupItem(row, 'row'));
                }
            }

            popover.hide();

            return popover;
        },

        /**
         * Positions the popover element.
         */
        _positionPopover: function () {
            var $body = $('body'),
                popover = this._pickerPopover,
                pickerLink = this.domElem,
                pickerLinkLeft = pickerLink.offset().left,
                bodyRight = $body.offset().left + $body.width()
                ;

            $body.prepend(popover);

            var neededSpace = popover.outerWidth() + 10;

            var linkOffset = pickerLink.offset();
            if (navigator.appVersion.indexOf("MSIE 7.") != -1) {
                linkOffset.left -= 10;
            }

            // try to display popover to the right
            var margin = parseInt(pickerLink.css('margin-left')) - 4;

            var popoverRight = pickerLinkLeft + margin + neededSpace;
            if (popoverRight < bodyRight
                // make sure it's not too far to the left
                || popoverRight < 0
            ) {
                popover.css('margin-left', (linkOffset.left - 4) + 'px').show();
            } else {
                // display to the left
                popover.addClass('alignright')
                    .css('margin-left', (linkOffset.left - neededSpace + 38) + 'px')
                    .css('background-position', (popover.outerWidth() - 25) + 'px 4px')
                    .show();
            }
            popover.css('margin-top', (linkOffset.top - 5) + 'px').show();
        }
    };

    var exports = require('piwik/DataTableVisualizations/Widgets');
    exports.SeriesPicker = SeriesPicker;

})(jQuery, document, require);
