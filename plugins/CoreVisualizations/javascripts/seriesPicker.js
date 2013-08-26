/**
 * Piwik - Web Analytics
 *
 * Adapter for jqplot
 *
 * @link http://www.jqplot.com
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($) {

    /**
     * This class creates and manages the Series Picker for certain DataTable visualizations.
     * 
     * @param {dataTable} The dataTable instance to add a series picker to.
     */
    var SeriesPicker = function (dataTable) {

        // dom element
        this.domElem = null;

        // the columns that can be selected
        this.selectableColumns = dataTable.props.selectable_columns;

        // the rows that can be selected
        this.selectableRows = dataTable.props.selectable_rows;

        // render the picker?
        this.show = !! dataTable.props.show_series_picker
                 && (this.selectableColumns || this.selectableRows);
        
        // can multiple rows we selected?
        this.multiSelect = !! dataTable.props.allow_multi_select_series_picker;

        this.dataTableId = dataTable.workingDivId;

        // language strings
        this.lang =
        {
            metricsToPlot: _pk_translate('General_MetricsToPlot_js'),
            metricToPlot: _pk_translate('General_MetricToPlot_js'),
            recordsToPlot: _pk_translate('General_RecordsToPlot_js')
        };
    };

    SeriesPicker.prototype = {

        /**
         * TODO
         */
        createElement: function (plot) {
            if (!this.show) {
                return;
            }

            // initialize dom element
            this.domElem = $(document.createElement('a'))
                .addClass('jqplot-seriespicker')
                .attr('href', '#').html('+');

            this.domElem.on('hide', function () {
                $(this).css('opacity', .55);
            }).trigger('hide');

            // show picker on hover
            var self = this;
            this.domElem.hover(
                function () {
                    self.domElem.css('opacity', 1);
                    if (!self.domElem.hasClass('open')) {
                        self.domElem.addClass('open');
                        self.showPicker(plot._width); // TODO: ???
                    }
                },
                function () {
                    // do nothing on mouseout because using this event doesn't work properly.
                    // instead, the timeout check beneath is used (checkPickerLeave()).
                }
            ).click(function (e) {
                e.preventDefault();
                return false;
            });

            $(this).trigger('placeSeriesPicker'); // TODO: document this & other events
        },

        /**
         * TODO
         */
        showPicker: function (plotWidth) {
            var pickerLink = this.domElem;
            var pickerPopover = $(document.createElement('div'))
                .addClass('jqplot-seriespicker-popover');

            var pickerState = {manipulated: false};

            // headline
            var title = this.multiSelect ? this.lang.metricsToPlot : this.lang.metricToPlot;
            pickerPopover.append($(document.createElement('p'))
                .addClass('headline').html(title));

            if (this.selectableColumns) {
                // render the selectable columns
                for (var i = 0; i < this.selectableColumns.length; i++) {
                    var column = this.selectableColumns[i];
                    pickerPopover.append(this.createPickerPopupItem(column, 'column', pickerState, pickerPopover, pickerLink));
                }
            }

            if (this.selectableRows) {
                // "records to plot" subheadline
                pickerPopover.append($(document.createElement('p'))
                    .addClass('headline').addClass('recordsToPlot')
                    .html(this.lang.recordsToPlot));

                // render the selectable rows
                for (var i = 0; i < this.selectableRows.length; i++) {
                    var row = this.selectableRows[i];
                    pickerPopover.append(this.createPickerPopupItem(row, 'row', pickerState, pickerPopover, pickerLink));
                }
            }

            $('body').prepend(pickerPopover.hide());
            var neededSpace = pickerPopover.outerWidth() + 10;

            // try to display popover to the right
            var linkOffset = pickerLink.offset();
            if (navigator.appVersion.indexOf("MSIE 7.") != -1) {
                linkOffset.left -= 10;
            }
            var margin = (parseInt(pickerLink.css('marginLeft'), 10) - 4);
            if (margin + neededSpace < plotWidth
                // make sure it's not too far to the left
                || margin - neededSpace + 60 < 0) {
                pickerPopover.css('marginLeft', (linkOffset.left - 4) + 'px').show();
            } else {
                // display to the left
                pickerPopover.addClass('alignright')
                    .css('marginLeft', (linkOffset.left - neededSpace + 38) + 'px')
                    .css('backgroundPosition', (pickerPopover.outerWidth() - 25) + 'px 4px')
                    .show();
            }
            pickerPopover.css('marginTop', (linkOffset.top - 5) + 'px').show();

            // hide and replot on mouse leave
            var self = this;
            this.checkPickerLeave(pickerPopover, function () {
                var replot = pickerState.manipulated;
                self.hidePicker(pickerPopover, pickerLink, replot);
            });
        },

        /**
         * TODO
         */
        createPickerPopupItem: function (config, type, pickerState, pickerPopover, pickerLink) {
            var self = this;
            var checkbox = $(document.createElement('input')).addClass('select')
                .attr('type', this.multiSelect ? 'checkbox' : 'radio');

            if (config.displayed && !(!this.multiSelect && pickerState.oneChecked)) {
                checkbox.prop('checked', true);
                pickerState.oneChecked = true;
            }

            // if we are rendering a column, remember the column name
            // if it's a row, remember the string that can be used to match the row
            checkbox.data('name', type == 'column' ? config.column : config.matcher);

            var el = $(document.createElement('p'))
                .append(checkbox)
                .append('<label>' + (type == 'column' ? config.translation : config.label) + '</label>')
                .addClass(type == 'column' ? 'pickColumn' : 'pickRow');

            var replot = function () {
                self.unbindPickerLeaveCheck();
                self.hidePicker(pickerPopover, pickerLink, true);
            };

            var checkBox = function (box) {
                if (!self.multiSelect) {
                    pickerPopover.find('input.select:not(.current)').prop('checked', false);
                }
                box.prop('checked', true);
                replot();
            };

            el.click(function (e) {
                pickerState.manipulated = true;
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
        checkPickerLeave: function (pickerPopover, onLeaveCallback) {
            var offset = pickerPopover.offset();
            var minX = offset.left;
            var minY = offset.top;
            var maxX = minX + pickerPopover.outerWidth();
            var maxY = minY + pickerPopover.outerHeight();
            var currentX, currentY;
            var self = this;
            this.onMouseMove = function (e) {
                currentX = e.pageX;
                currentY = e.pageY;
                if (currentX < minX || currentX > maxX
                    || currentY < minY || currentY > maxY) {
                    self.unbindPickerLeaveCheck();
                    onLeaveCallback();
                }
            };
            $(document).mousemove(this.onMouseMove);
        },

        /**
         * TODO
         */
        unbindPickerLeaveCheck: function () {
            $(document).unbind('mousemove', this.onMouseMove);
        },

        /**
         * TODO
         */
        hidePicker: function (pickerPopover, pickerLink, replot) {
            // hide picker
            pickerPopover.hide();
            pickerLink.trigger('hide').removeClass('open');

            // replot
            if (replot) {
                var columns = [];
                var rows = [];
                pickerPopover.find('input:checked').each(function () {
                    if ($(this).closest('p').hasClass('pickRow')) {
                        rows.push($(this).data('name'));
                    } else {
                        columns.push($(this).data('name'));
                    }
                });
                var noRowSelected = pickerPopover.find('.pickRow').size() > 0
                    && pickerPopover.find('.pickRow input:checked').size() == 0;
                if (columns.length > 0 && !noRowSelected) {

                    $('#' + this.dataTableId + ' .piwik-graph').trigger('changeSeries', [columns, rows]);
                    // inform dashboard widget about changed parameters (to be restored on reload)
                    $('#' + this.dataTableId + ' .piwik-graph').parents('[widgetId]').trigger('setParameters', {columns: columns, rows: rows});
                }
            }

            pickerPopover.remove();
        }
    };

    piwik.SeriesPicker = SeriesPicker;

})(jQuery);