/**
 * Piwik - Web Analytics
 *
 * Adapter for jqplot
 *
 * @link http://www.jqplot.com
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, doc) {

    /**
     * This class creates and manages the Series Picker for certain DataTable visualizations.
     * 
     * @param {dataTable} The dataTable instance to add a series picker to.
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
            metricsToPlot: _pk_translate('General_MetricsToPlot_js'),
            metricToPlot: _pk_translate('General_MetricToPlot_js'),
            recordsToPlot: _pk_translate('General_RecordsToPlot_js')
        };

        this._pickerState = null;
        this._pickerPopover = null;
    };

    SeriesPicker.prototype = {

        /**
         * TODO
         */
        createElement: function () {
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
                            self.showPicker();
                        }
                    },
                    function () {
                        // do nothing on mouseout because using this event doesn't work properly.
                        // instead, the timeout check beneath is used (checkPickerLeave()).
                    }
                )
                .click(function (e) {
                    e.preventDefault();
                    return false;
                });

            $(this).trigger('placeSeriesPicker'); // TODO: document this & other events
        },

        /**
         * TODO
         */
        showPicker: function () {
            this._pickerState = {manipulated: false};
            this._pickerPopover = this._createPopover();

            this._positionPopover();

            // hide and replot on mouse leave
            var self = this;
            this.checkPickerLeave(function () {
                var replot = self._pickerState.manipulated;
                self.hidePicker(replot);
            });
        },

        /**
         * TODO
         */
        createPickerPopupItem: function (config, type) {
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
                self.unbindPickerLeaveCheck();
                self.hidePicker(true);
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
         * TODO
         */
        checkPickerLeave: function (onLeaveCallback) {
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
                    self.unbindPickerLeaveCheck();
                    onLeaveCallback();
                }
            };

            $(doc).mousemove(this._onMouseMove);
        },

        /**
         * TODO
         */
        unbindPickerLeaveCheck: function () {
            $(doc).unbind('mousemove', this._onMouseMove);
        },

        /**
         * TODO
         */
        hidePicker: function (replot) {
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
                    $(this).trigger('seriesPicked', [columns, rows]); // TODO: document this event

                    // inform dashboard widget about changed parameters (to be restored on reload)
                    $('#' + this.dataTableId).closest('[widgetId]').trigger('setParameters', {columns: columns, rows: rows});
                }
            }

            this._pickerPopover.remove();
        },

        _createPopover: function () {
            var popover = $('<div/>')
                .addClass('jqplot-seriespicker-popover');

            // create headline element
            var title = this.multiSelect ? this.lang.metricsToPlot : this.lang.metricToPlot;
            popover.append($('<p/>').addClass('headline').html(title));

            // create selectable columns list
            if (this.selectableColumns) {
                for (var i = 0; i < this.selectableColumns.length; i++) {
                    var column = this.selectableColumns[i];
                    popover.append(this.createPickerPopupItem(column, 'column'));
                }
            }

            // create selectable rows list
            if (this.selectableRows) {
                // "records to plot" subheadline
                var header = $('<p/>').addClass('headline').addClass('recordsToPlot').html(this.lang.recordsToPlot);
                popover.append(header);

                // render the selectable rows
                for (var i = 0; i < this.selectableRows.length; i++) {
                    var row = this.selectableRows[i];
                    popover.append(this.createPickerPopupItem(row, 'row'));
                }
            }

            popover.hide();

            return popover;
        },

        _positionPopover: function () {
            var popover = this._pickerPopover,
                pickerLink = this.domElem,
                plotWidth = pickerLink.parent().width();

            $('body').prepend(popover);
            
            var neededSpace = popover.outerWidth() + 10;

            var linkOffset = pickerLink.offset();
            if (navigator.appVersion.indexOf("MSIE 7.") != -1) {
                linkOffset.left -= 10;
            }

            // try to display popover to the right
            var margin = parseInt(pickerLink.css('margin-left')) - 4;
            if (margin + neededSpace < plotWidth
                // make sure it's not too far to the left
                || margin - neededSpace + 60 < 0
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
        },
    };

    piwik.SeriesPicker = SeriesPicker;

})(jQuery, document);