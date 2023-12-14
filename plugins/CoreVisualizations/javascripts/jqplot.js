/**
 * Matomo - free/libre analytics platform
 *
 * DataTable UI class for JqplotGraph.
 *
 * @link http://www.jqplot.com
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function rowEvolutionGetMetricNameFromRow(tr)
{
    return $(tr).find('td [data-name]').text().trim();
}

(function ($, require) {
    var exports = require('piwik/UI'),
        DataTable = exports.DataTable,
        dataTablePrototype = DataTable.prototype,
        getLabelFontFamily = function () {
            if (!window.piwik.jqplotLabelFont) {
                window.piwik.jqplotLabelFont = $('<p/>').hide().appendTo('body').css('font-family');
            }

            return window.piwik.jqplotLabelFont || 'Arial';
        }
    ;

    exports.getLabelFontFamily = getLabelFontFamily;

    /**
     * DataTable UI class for jqPlot graph datatable visualizations.
     *
     * @constructor
     */
    exports.JqplotGraphDataTable = function (element) {
        DataTable.call(this, element);
    };

    $.extend(exports.JqplotGraphDataTable.prototype, dataTablePrototype, {

        /**
         * Initializes this class.
         */
        init: function () {
            dataTablePrototype.init.call(this);

            var graphElement = $('.piwik-graph', this.$element);
            if (!graphElement.length) {
                return;
            }

            this._lang = {
                noData: _pk_translate('General_NoDataForGraph'),
                exportTitle: _pk_translate('General_ExportAsImage'),
                exportText: _pk_translate('General_SaveImageOnYourComputer'),
                metricsToPlot: _pk_translate('General_MetricsToPlot'),
                metricToPlot: _pk_translate('General_MetricToPlot'),
                recordsToPlot: _pk_translate('General_RecordsToPlot'),
                incompletePeriod: _pk_translate('General_IncompletePeriod')
            };

            // set a unique ID for the graph element (required by jqPlot)
            this.targetDivId = this.workingDivId + 'Chart';
            graphElement.attr('id', this.targetDivId);

            try {
                var graphData = JSON.parse(graphElement.attr('data-data'));
            } catch (e) {
                console.error('JSON.parse Error: "' + e + "\" in:\n" + graphElement.attr('data-data'));
                return;
            }

            this.data = graphData.data;
            this._setJqplotParameters(graphData.params);
            this._setIncompleteDataPoints(graphData.incompleteDataPoints);

            if (this.props.display_percentage_in_tooltip) {
                this._setTooltipPercentages();
            }

            this._bindEvents();

            // add external series toggle if it should be added
            if (this.props.external_series_toggle) {
                this.addExternalSeriesToggle(
                    window[this.props.external_series_toggle], // get the function w/ string name
                    this.props.external_series_toggle_show_all == 1
                );
            }

            // render the graph (setTimeout is required, otherwise the graph will not
            // render initially)
            var self = this;
            setTimeout(function () { self.render(); }, 1);
        },

        _setIncompleteDataPoints: function (incompleteDataPoints) {
            this.jqplotParams.incompleteDataPoints = [];

            if (Array.isArray(incompleteDataPoints)) {
                this.jqplotParams.incompleteDataPoints = incompleteDataPoints;
            }

            let tickCount = 0;

            if (this.param.period) {
                // Fetch evolution graph tick count
                switch (this.param.period) {
                    case 'day':
                        tickCount = this.param.evolution_day_last_n;
                        break;
                    case 'week':
                        tickCount = this.param.evolution_week_last_n;
                        break;
                    case 'month':
                        tickCount = this.param.evolution_month_last_n;
                        break;
                    case 'year':
                        tickCount = this.param.evolution_year_last_n;
                        break;
                }
            }

            if (0 >= tickCount) {
                return;
            }

            // Mark today, if included in graph, as incomplete
            const piwikPeriods = window.CoreHome.Periods;
            let period = this.param.period;

            if (period === 'day' && this.param.date.indexOf(',') !== -1) {
                // If date is actually a range then adjust the period type for the containsToday check
                period = 'range';
            }

            try {
                if (piwikPeriods.parse(period, this.param.date).containsToday()) {
                    this.jqplotParams.incompleteDataPoints.push(tickCount - 1);
                }
            } catch (e) {
                // ignore period parsing error
            }
        },

        _setJqplotParameters: function (params) {
            defaultParams = {
                grid: {
                    drawGridLines: false,
                    borderWidth: 0,
                    shadow: false
                },
                title: {
                    show: false
                },
                axesDefaults: {
                    pad: 1.0,
                    tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                    tickOptions: {
                        showMark: false,
                        fontSize: '11px',
                        fontFamily: getLabelFontFamily()
                    },
                    rendererOptions: {
                        drawBaseline: false
                    }
                },
                axes: {
                    yaxis: {
                        tickOptions: {
                            formatString: '%s',
                            formatter: $.jqplot.NumberFormatter
                        }
                    },
                }
            };

            this.jqplotParams = $.extend(true, {}, defaultParams, params);

            for (var i = 2; typeof this.jqplotParams.axes['y' + i + 'axis'] != 'undefined'; i++) {
                this.jqplotParams.axes['y' + i + 'axis'].tickOptions = $.extend(true, {}, {
                  formatString: '%s',
                  formatter: $.jqplot.NumberFormatter
                }, this.jqplotParams.axes['y' + i + 'axis'].tickOptions);
            }

            this._setColors();
        },

        _setTooltipPercentages: function () {
            this.tooltip = {percentages: []};

            for (var seriesIdx = 0; seriesIdx != this.data.length; ++seriesIdx) {
                var series = this.data[seriesIdx];
                var sum = 0;

                $.each(series, function(index, value) {
                    if ($.isArray(value) && value[1]) {
                        sum = sum + value[1];
                    } else if (!$.isArray(value)) {
                        sum = sum + value;
                    }
                });

                var percentages = this.tooltip.percentages[seriesIdx] = [];
                for (var valueIdx = 0; valueIdx != series.length; ++valueIdx) {
                    var value = series[valueIdx];
                    if ($.isArray(value) && value[1]) {
                        value = value[1];
                    }

                    percentages[valueIdx] = sum > 0 ? Math.round(100 * value / sum) : 0;
                }
            }
        },

        _bindEvents: function () {
            var self = this;
            var target = $('#' + this.targetDivId);

            // tooltip show/hide
            target.on('jqplotDataHighlight', function (e, seriesIndex, valueIndex) {
                self._showDataPointTooltip(this, seriesIndex, valueIndex);
            })
                .on('jqplotDataUnhighlight', function () {
                    self._destroyDataPointTooltip($(this));
                });

            // handle window resize
            this._plotWidth = target.innerWidth();
            target.on('resizeGraph', function () { // TODO: shouldn't be a triggerable event.
                self._resizeGraph();
            });

            // export as image
            target.on('piwikExportAsImage', function () {
                self.exportAsImage(target, self._lang);
            });

            // manage resources
            target.on('piwikDestroyPlot', function () {
                if (self._resizeListener) {
                    $(window).off('resize', self._resizeListener);
                }
                self._plot.destroy();
                for (var i = 0; i < $.jqplot.visiblePlots.length; i++) {
                    if ($.jqplot.visiblePlots[i] === self) {
                        $.jqplot.visiblePlots[i] = null;
                    }
                }
            });

            this.$element.closest('.widgetContent').on('widget:resize', function () {
                self._resizeGraph();
            });
        },

        _resizeGraph: function () {
            var width = $('#' + this.targetDivId).innerWidth();
            if (width > 0 && Math.abs(this._plotWidth - width) >= 5) {
                this._plotWidth = width;
                this.render();
            }
        },

        _setWindowResizeListener: function () {
            var self = this;

            var timeout = false;
            this._resizeListener = function () {
                if (timeout) {
                    window.clearTimeout(timeout);
                }

                timeout = window.setTimeout(function () { $('#' + self.targetDivId).trigger('resizeGraph'); }, 300);
            };
            $(window).on('resize', this._resizeListener);
        },

        _destroyDataPointTooltip: function ($element) {
            if ($element.is( ":data('ui-tooltip')" )) {
                $element.tooltip('destroy');
            }
        },

        _showDataPointTooltip: function (element, seriesIndex, valueIndex) {
            // empty
        },

        changeSeries: function (columns, rows) {
            this.showLoading();

            columns = columns || [];
            if (typeof columns == 'string') {
                columns = columns.split(',');
            }

            rows = rows || [];
            if (typeof rows == 'string') {
                rows = rows.split(',');
            }

            var dataTable = $('#' + this.workingDivId).data('uiControlObject');
            dataTable.param.columns = columns.join(',');
            dataTable.param.rows = rows.join(',');
            delete dataTable.param.filter_limit;
            delete dataTable.param.totalRows;
            if (dataTable.param.filter_sort_column != 'label') {
                dataTable.param.filter_sort_column = columns[0];
            }
            dataTable.param.disable_generic_filters = '0';
            dataTable.reloadAjaxDataTable(false);
        },

        destroyPlot: function () {
            var target = $('#' + this.targetDivId);

            target.trigger('piwikDestroyPlot');
            if (target.data('oldHeight') > 0) {
                // handle replot after empty report
                target.height(target.data('oldHeight'));
                target.data('oldHeight', 0);
                target.innerHTML = '';
            }
        },

        showLoading: function () {
            var target = $('#' + this.targetDivId);

            var loading = $(document.createElement('div')).addClass('jqplot-loading');
            loading.css({
                width: target.innerWidth() + 'px',
                height: target.innerHeight() + 'px',
                opacity: 0
            });
            target.prepend(loading);
            loading.css({opacity: .7});
        },

        /**
         * This method sums up total width of all tick according to currently
         * set font-family, font-size and font-weight. It is achieved by
         * creating span elements with ticks and adding their width.
         * Rendered ticks have to be visible to get their real width. But it
         * is too fast for user to notice it. If total ticks width is bigger
         * than container width then half of ticks is being cut out and their
         * width is tested again. Until their total width is smaller than chart
         * div. There is a failsafe so check will be performed no more than 20
         * times, which is I think more than enough. Each tick have its own
         * gutter, by default width of 5 px from each side so they are more
         * readable.
         *
         * @param $targetDiv
         * @private
         */
        _checkTicksWidth: function($targetDiv){
            if(typeof this.jqplotParams.axes.xaxis.ticksOriginal === 'undefined' || this.jqplotParams.axes.xaxis.ticksOriginal === {}){
                this.jqplotParams.axes.xaxis.ticksOriginal = this.jqplotParams.axes.xaxis.ticks.slice();
            }

            var ticks = this.jqplotParams.axes.xaxis.ticks = this.jqplotParams.axes.xaxis.ticksOriginal.slice();

            var divWidth = $targetDiv.width();
            var tickOptions = $.extend(true, {}, this.jqplotParams.axesDefaults.tickOptions, this.jqplotParams.axes.xaxis.tickOptions);
            var gutter = tickOptions.gutter || 5;
            var sumWidthOfTicks = Number.MAX_VALUE;
            var $labelTestChamber = {};
            var tick = "";
            var $body = $("body");
            var maxRunsFailsafe = 20;
            var ticksCount = 0;
            var key = 0;

            while(sumWidthOfTicks > divWidth && maxRunsFailsafe > 0) {
                sumWidthOfTicks = 0;
                for (key = 0; key < ticks.length; key++) {
                    tick = ticks[key];
                    if (tick !== " " && tick !== "") {
                        $labelTestChamber = $("<span/>", {
                            style: 'font-size: ' + (tickOptions.fontSize || '11px') + '; font-family: ' + (tickOptions.fontFamily || 'Arial, Helvetica, sans-serif') + ';' + (tickOptions.fontWeight || 'normal') + ';' + 'clear: both; float: none;',
                            text: tick
                        }).appendTo($body);
                        sumWidthOfTicks += ($labelTestChamber.width() + gutter*2);
                        $labelTestChamber.remove();
                    }
                }

                ticksCount = 0;
                if (sumWidthOfTicks > divWidth) {
                    for (key = 0; key < ticks.length; key++) {
                        tick = ticks[key];
                        if (tick !== " " && tick !== "") {
                            if (ticksCount % 2 == 1) {
                                ticks[key] = " ";
                            }
                            ticksCount++;
                        }
                    }
                }
                maxRunsFailsafe--;
            }
        },

        /** Generic render function */
        render: function () {
            if (this.data.length == 0) { // sanity check
                return;
            }

            var targetDivId = this.workingDivId + 'Chart';
            var lang = this._lang;
            var dataTableDiv = $('#' + this.workingDivId);

            // if the plot has already been rendered, get rid of the existing plot
            var target = $('#' + targetDivId);
            if (target.find('canvas').length > 0) {
                this.destroyPlot();
            }

            // handle replot
            // this has be bound before the check for an empty graph.
            // otherwise clicking on sparklines won't work anymore after an empty
            // report has been displayed.
            var self = this;

            // before drawing a jqplot chart, check if all labels ticks will fit
            // into it
            if( this.param.viewDataTable === "graphBar"
                || this.param.viewDataTable === "graphVerticalBar"
                || this.param.viewDataTable === "graphEvolution" ) {
                self._checkTicksWidth(target);
            }

            // create jqplot chart
            try {
                var plot = self._plot = $.jqplot(targetDivId, this.data, this.jqplotParams);
            } catch (e) {
                // this is thrown when refreshing piwik in the browser
                if (e != "No plot target specified") {
                    throw e;
                }
            }

            self._setWindowResizeListener();

            var self = this;

            // TODO: this code destroys plots when a page is switched. there must be a better way of managing memory.
            if (typeof $.jqplot.visiblePlots == 'undefined') {
                $.jqplot.visiblePlots = [];
                window.CoreHome.Matomo.on('matomoPageChange', function () {
                    for (var i = 0; i < $.jqplot.visiblePlots.length; i++) {
                        if ($.jqplot.visiblePlots[i] == null) {
                            continue;
                        }
                        $.jqplot.visiblePlots[i].destroyPlot();
                    }
                    $.jqplot.visiblePlots = [];
                });
            }

            if (typeof plot != 'undefined') {
                $.jqplot.visiblePlots.push(self);
            }
        },

        /** Export the chart as an image */
        exportAsImage: function (container, lang) {
            var pixelRatio = window.devicePixelRatio || 1;
            var exportCanvas = document.createElement('canvas');
            exportCanvas.width = Math.round(container.width() * pixelRatio);
            exportCanvas.height = Math.round(container.height() * pixelRatio);

            if (!exportCanvas.getContext) {
                alert("Sorry, not supported in your browser. Please upgrade your browser :)");
                return;
            }
            var exportCtx = exportCanvas.getContext('2d');

            var canvases = container.find('canvas');

            for (var i = 0; i < canvases.length; i++) {
                var canvas = canvases.eq(i);
                var position = canvas.position();
                var parent = canvas.parent();
                if (parent.hasClass('jqplot-axis')) {
                    var addPosition = parent.position();
                    position.left += addPosition.left;
                    position.top += addPosition.top + parseInt(parent.css('marginTop'), 10);
                }
                exportCtx.drawImage(canvas[0], Math.round(position.left * pixelRatio), Math.round(position.top * pixelRatio));
            }

            var exported = exportCanvas.toDataURL("image/png");

            var img = document.createElement('img');
            img.src = exported;

            img = $(img).css({
                width: Math.round(exportCanvas.width / pixelRatio) + 'px',
                height: Math.round(exportCanvas.height / pixelRatio) + 'px'
            });

            var popover = $(document.createElement('div'));

            popover.append('<div style="font-size: 13px; margin-bottom: 10px;">'
                + lang.exportText + '</div>').append($(img));

            popover.dialog({
                title: lang.exportTitle,
                modal: true,
                width: 'auto',
                resizable: false,
                autoOpen: true,
                open: function (event, ui) {
                    $('.ui-widget-overlay').on('click.popover', function () {
                        popover.dialog('close');
                    });
                },
                close: function (event, ui) {
                    $(this).dialog("destroy").remove();
                }
            });
        },

        // ------------------------------------------------------------
        //  HELPER METHODS
        // ------------------------------------------------------------

        /** Generate ticks in y direction */
        setYTicks: function () {
            var $tempAxisElement = $('<div>').attr('class', 'jqplot-axis jqplot-y2axis').css({'visibility': 'hidden', 'display': 'inline-block'});
            $('<span>')
                .css('font-size', this.jqplotParams.axesDefaults.fontSize)
                .css('font-family', this.jqplotParams.axesDefaults.fontFamily)
                .appendTo($tempAxisElement);
            $('body').append($tempAxisElement);

            // default axis
            this.setYTicksForAxis('yaxis', this.jqplotParams.axes.yaxis);

            // other axes: y2axis, y3axis...
            var axisLength = 10;
            for (var i = 2; typeof this.jqplotParams.axes['y' + i + 'axis'] != 'undefined'; i++) {
                this.setYTicksForAxis('y' + i + 'axis', this.jqplotParams.axes['y' + i + 'axis']);

                axisLength += getAxisWidth(this.jqplotParams.axes['y' + i + 'axis']);
            }

            var axesShown = {};
            this.jqplotParams.series.forEach(function (series) {
                axesShown[series.yaxis] = true;
            });
            var hasMultipleAxes = Object.keys(axesShown).length > 1;

            // only adjust width if more than one axis exists AND more than one series shown
            if (hasMultipleAxes) {
                $('.piwik-graph', this.$element).css('width', 'calc(100% - ' + axisLength + 'px)');
            } else {
                $('.piwik-graph', this.$element).css('width', '');
            }

            $tempAxisElement.remove();

            function getAxisWidth(axis) {
                var maxWidth = 0;
                axis.ticks.forEach(function (tick) {
                    var tickFormatted = $.jqplot.NumberFormatter(axis.tickOptions.formatString || '%s', tick);
                    $tempAxisElement.find('span').text(tickFormatted);
                    maxWidth = Math.max(maxWidth, $tempAxisElement.width());
                });
                return maxWidth;
            }
        },

        setYTicksForAxis: function (axisName, axis) {
            // calculate maximum x value of all data sets
            var maxCrossDataSets = 0;
            for (var i = 0; i < this.data.length; i++) {
                if (this.jqplotParams.series[i].yaxis == axisName) {
                    var maxValue = Math.max.apply(Math, this.data[i]);
                    if (maxValue > maxCrossDataSets) {
                        maxCrossDataSets = maxValue;
                    }
                    maxCrossDataSets = parseFloat(maxCrossDataSets);
                }
            }

            // add little padding on top
            maxCrossDataSets += Math.max(1, Math.round(maxCrossDataSets * .03));

            // round to the nearest multiple of ten
            if (maxCrossDataSets > 15) {
                maxCrossDataSets = maxCrossDataSets + 10 - maxCrossDataSets % 10;
            }

            if (maxCrossDataSets == 0) {
                maxCrossDataSets = 1;
            }

            // make sure percent axes don't go above 100%
            if (
              axis.tickOptions
              && axis.tickOptions.formatString
              && axis.tickOptions.formatString.endsWith('%')
              && maxCrossDataSets > 100
            ) {
                maxCrossDataSets = 100;
            }

            // calculate y-values for ticks
            var ticks = [];
            var numberOfTicks = 2;
            var tickDistance = Math.ceil(maxCrossDataSets / numberOfTicks);
            for (var i = 0; i <= numberOfTicks; i++) {
                ticks.push(i * tickDistance);
            }
            axis.ticks = ticks;
        },

        /** Get a formatted y values (with unit) */
        formatY: function (value, seriesIndex) {
            var floatVal = parseFloat(value);
            var intVal = parseInt(value, 10);
            if (Math.abs(floatVal - intVal) >= 0.005) {
                value = Math.round(floatVal * 100) / 100;
            } else if (parseFloat(intVal) == floatVal) {
                value = intVal;
            } else {
                value = floatVal;
            }

            var axisId = this.jqplotParams.series[seriesIndex].yaxis;
            var formatString = this.jqplotParams.axes[axisId].tickOptions.formatString;

            return $.jqplot.NumberFormatter(formatString, value);
        },

        /**
         * Add an external series toggle.
         * As opposed to addSeriesPicker, the external series toggle can only show/hide
         * series that are already loaded.
         *
         * @param seriesPickerClass a subclass of JQPlotExternalSeriesToggle
         * @param initiallyShowAll
         */
        addExternalSeriesToggle: function (seriesPickerClass, initiallyShowAll) {
            new seriesPickerClass(this.targetDivId, this, initiallyShowAll);

            if (!initiallyShowAll) {

                var initialMetrics = 0;
                var $rowEvolution = $('#'+this.targetDivId).closest('.rowevolution');

                var newData = [];
                var newSeries = [];
                if ($rowEvolution.data('initialMetrics')) {
                    initialMetrics = $rowEvolution.data('initialMetrics');

                    if (Array.isArray(initialMetrics)) {
                        for (var j = 0; j < initialMetrics.length; j++) {
                            // find index of series and data
                            for (var k = 0; k < this.jqplotParams.series.length; k++) {
                                if (this.jqplotParams.series[k]
                                    && this.jqplotParams.series[k].label
                                    && this.jqplotParams.series[k].label === initialMetrics[j]) {

                                    newData.push(this.data[k]);
                                    newSeries.push(this.jqplotParams.series[k]);
                                    break;
                                }
                            }
                        }
                    }
                }

                if (newData.length) {
                    // restore original selection
                    this.data = newData;
                    this.jqplotParams.series = newSeries;
                } else {
                    // initially, show only the first series
                    this.data = [this.data[0]];
                    this.jqplotParams.series = [this.jqplotParams.series[0]];
                }

                this.setYTicks();
            }
        },

        /**
         * Sets the colors used to render this graph.
         */
        _setColors: function () {
            var colorManager = piwik.ColorManager;

            var viewDataTable = $('#' + this.workingDivId).data('uiControlObject').param['viewDataTable'];

            var graphType = '';
            if (viewDataTable == 'graphEvolution' || viewDataTable == 'graphStackedBarEvolution') {
                graphType = 'evolution';
            } else if (viewDataTable == 'graphPie') {
                graphType = 'pie';
            } else if (viewDataTable == 'graphVerticalBar') {
                graphType = 'bar';
            }

            var namespace = graphType + '-graph-colors';

            this._setSeriesColors(namespace);

            this.jqplotParams.grid.background = colorManager.getColor(namespace, 'grid-background');
            this.jqplotParams.grid.borderColor = colorManager.getColor(namespace, 'grid-border');
            this.tickColor = colorManager.getColor(namespace, 'ticks');
            this.singleMetricColor = colorManager.getColor(namespace, 'single-metric-label')
        },

        _setSeriesColors: function (namespace) {
            var colorManager = piwik.ColorManager,
                seriesColorNames = ['series0', 'series1', 'series2', 'series3', 'series4', 'series5',
                    'series6', 'series7', 'series8', 'series9', 'series10'];

            var comparisonService = window.CoreHome.ComparisonsStoreInstance;
            if (comparisonService.isComparing() && typeof this.jqplotParams.series[0].seriesIndex !== 'undefined') {
                namespace = 'comparison-series-color';

                seriesColorNames = [];
                this.jqplotParams.series.forEach(function (s) {
                    var seriesColorName = comparisonService.getSeriesColorName(s.seriesIndex, s.metricIndex);
                    seriesColorNames.push(seriesColorName);
                });
            }

            this.jqplotParams.seriesColors = colorManager.getColors(namespace, seriesColorNames, true);
        }
    });

    DataTable.registerFooterIconHandler('graphPie', DataTable.switchToGraph);
    DataTable.registerFooterIconHandler('graphVerticalBar', DataTable.switchToGraph);
    DataTable.registerFooterIconHandler('graphEvolution', DataTable.switchToGraph);

})(jQuery, require);

// ----------------------------------------------------------------
//  EXTERNAL SERIES TOGGLE
//  Use external dom elements and their events to show/hide series
// ----------------------------------------------------------------

function JQPlotExternalSeriesToggle(targetDivId, jqplotObject, initiallyShowAll) {
    this.init(targetDivId, originalConfig, initiallyShowAll);
}

JQPlotExternalSeriesToggle.prototype = {

    init: function (targetDivId, jqplotObject, initiallyShowAll) {
        this.targetDivId = targetDivId;
        this.jqplotObject = jqplotObject;
        this.originalData = jqplotObject.data;
        this.originalSeries = jqplotObject.jqplotParams.series;
        this.originalAxes = jqplotObject.jqplotParams.axes;
        this.originalParams = jqplotObject.jqplotParams;
        this.originalSeriesColors = jqplotObject.jqplotParams.seriesColors;
        this.initiallyShowAll = initiallyShowAll;

        this.activated = [];
        this.target = $('#' + targetDivId);

        this.attachEvents();
    },

    // can be overridden
    attachEvents: function () {},

    // show a single series
    showSeries: function (i) {
        this.activated = [i];
        this.replot();
    },

    // toggle a series (make plotting multiple series possible)
    toggleSeries: function (i) {
        if (this.activated.indexOf(i) > -1) {
            // need to remove the metric
            if (this.activated.length > 1) {
                // prevent removing the only visible metric
                this.activated.splice(this.activated.indexOf(i), 1);
            }
        } else {
            this.activated.push(i);
        }
        this.replot();
    },

    replot: function () {
        this.beforeReplot();

        // build new config and replot
        var usedAxes = [];
        var config = {data: this.originalData, params: this.originalParams};
        config.data = [];
        config.params.series = [];
        config.params.axes = {xaxis: this.originalAxes.xaxis};
        config.params.seriesColors = [];

        for (var j = 0; j < this.activated.length; j++) {
            // find index of series and data
            for (var k = 0; k < this.originalSeries.length; k++) {
                if (this.originalSeries[k]
                    && this.originalSeries[k].label
                    && (
                      this.originalSeries[k].label === this.activated[j]
                      || piwikHelper.htmlDecode(this.originalSeries[k].label) === this.activated[j]
                    )
                ) {
                    config.data.push(this.originalData[k]);
                    config.params.seriesColors.push(this.originalSeriesColors[k]);
                    config.params.series.push($.extend(true, {}, this.originalSeries[k]));
                    // build array of used axes
                    var axis = this.originalSeries[k].yaxis;
                    if ($.inArray(axis, usedAxes) == -1) {
                        usedAxes.push(axis);
                    }
                    break;
                }
            }
        }

        // build new axes config
        var replaceAxes = {};
        for (j = 0; j < usedAxes.length; j++) {
            var originalAxisName = usedAxes[j];
            var newAxisName = (j == 0 ? 'yaxis' : 'y' + (j + 1) + 'axis');
            replaceAxes[originalAxisName] = newAxisName;
            config.params.axes[newAxisName] = this.originalAxes[originalAxisName];
        }

        // replace axis names in series config
        for (j = 0; j < config.params.series.length; j++) {
            var series = config.params.series[j];
            series.yaxis = replaceAxes[series.yaxis];
        }

        this.jqplotObject.data = config.data;
        this.jqplotObject.jqplotParams = config.params;
        this.jqplotObject.setYTicks();
        this.jqplotObject.render();
    },

    // can be overridden
    beforeReplot: function () {}

};

// ROW EVOLUTION SERIES TOGGLE

function RowEvolutionSeriesToggle(targetDivId, jqplotData, initiallyShowAll) {
    this.init(targetDivId, jqplotData, initiallyShowAll);
}

RowEvolutionSeriesToggle.prototype = JQPlotExternalSeriesToggle.prototype;

RowEvolutionSeriesToggle.prototype.attachEvents = function () {
    var self = this;

    var $rowEvolution = this.target.closest('.rowevolution');
    this.seriesPickers = $rowEvolution.find('table.metrics tr');

    var initialMetrics = [];

    if ($rowEvolution.data('initialMetrics')) {
        initialMetrics = [];
        var savedMetrics = $rowEvolution.data('initialMetrics');
        var existingMetricsInSeries = [];
        var m = 0;
        for (m = 0; m < this.originalSeries.length; m++) {
            existingMetricsInSeries.push(this.originalSeries[m].label);
        }
        for (m = 0; m < savedMetrics.length; m++) {
            if (existingMetricsInSeries.indexOf(savedMetrics[m]) > -1) {
                // only if it exists... for example unique visitors etc might not be available for some metrics,
                // then we need to make sure to highlight the default first metric for example
                initialMetrics.push(savedMetrics[m]);
            }
        }
    }

    this.seriesPickers.each(function (i) {
        var el = $(this);

        el.off('click').on('click', function (e) {
            var metricName = rowEvolutionGetMetricNameFromRow(this);
            // we are storing this info on the element as the series picker and the jqplot object gets recreated whenever
            // we change a period so we cannot persist the selection there.
            if (e.shiftKey) {
                self.toggleSeries(metricName);
                document.getSelection().removeAllRanges(); // make sure chrome doesn't select text
            } else {
                self.showSeries(metricName);
            }
            $rowEvolution.data('initialMetrics', self.activated);
            return false;
        });

        var label = rowEvolutionGetMetricNameFromRow(el);
        var metricExists = false;
        for (var k = 0; k < self.originalSeries.length; k++) {
            if (self.originalSeries[k] && labelMatches(self.originalSeries[k].label, label)) {
                metricExists = true;
            }
        }

        if (!metricExists) {
            el.hide();
        } else if (
            (initialMetrics.length === 0 && i == 0)
            || (initialMetrics.length > 0 && initialMetrics.indexOf(label) > -1)
            || self.initiallyShowAll) {
            // show the active series
            // if initiallyShowAll, all are active; otherwise only the first one
            if (!el.hasClass('hiddenByDefault')) {
                el.show();
            }
            el.find('td').css('opacity', '');
            self.activated.push(rowEvolutionGetMetricNameFromRow(el));
        } else {
            if (!el.hasClass('hiddenByDefault')) {
                el.show();
            }
            // fade out the others
            el.find('td').css('opacity', .5);
        }

        // the API outputs the label double encoded when it shouldn't. so when looking for a matching label we have
        // to check if one is double encoded.
        function labelMatches(lhs, rhs) {
            return lhs === rhs || piwikHelper.htmlDecode(lhs) === rhs || lhs === piwikHelper.htmlDecode(rhs);
        }
    });
};

RowEvolutionSeriesToggle.prototype.beforeReplot = function () {
    var self = this;
    // fade out if not activated
    this.seriesPickers.find('td').css('opacity', .5);
    this.seriesPickers.each(function (i) {
        var name = rowEvolutionGetMetricNameFromRow(this);
        if (self.activated.indexOf(name) > -1) {
            $(this).find('td').css('opacity', 1);
        }
    });
};

// ------------------------------------------------------------
//  PIWIK NUMBERFORMATTER PLUGIN FOR JQPLOT
// ------------------------------------------------------------
(function($){

    $.jqplot.NumberFormatter = function (format, value) {

        if (!$.isNumeric(value)) {
            return format.replace(/%s/, value);
        }
        return format.replace(/%s/, NumberFormatter.formatNumber(value));
    }

})(jQuery);


// ------------------------------------------------------------
//  PIWIK TICKS PLUGIN FOR JQPLOT
//  Handle ticks the piwik way...
// ------------------------------------------------------------

(function ($) {

    $.jqplot.PiwikTicks = function (options) {
        // canvas for the grid
        this.piwikTicksCanvas = null;
        // canvas for the highlight
        this.piwikHighlightCanvas = null;
        // renderer used to draw the marker of the highlighted point
        this.markerRenderer = new $.jqplot.MarkerRenderer({
            shadow: false
        });
        // the x tick the mouse is over
        this.currentXTick = false;
        // show the highlight around markers
        this.showHighlight = false;
        // show the grid
        this.showGrid = false;
        // show the ticks
        this.showTicks = false;

        $.extend(true, this, options);
    };

    $.jqplot.PiwikTicks.init = function (target, data, opts) {
        // add plugin as an attribute to the plot
        var options = opts || {};
        this.plugins.piwikTicks = new $.jqplot.PiwikTicks(options.piwikTicks);

        if (typeof $.jqplot.PiwikTicks.init.eventsBound == 'undefined') {
            $.jqplot.PiwikTicks.init.eventsBound = true;
            $.jqplot.eventListenerHooks.push(['jqplotMouseMove', handleMouseMove]);
            $.jqplot.eventListenerHooks.push(['jqplotMouseLeave', handleMouseLeave]);
        }
    };

    // draw the grid
    // called with context of plot
    $.jqplot.PiwikTicks.postDraw = function () {
        var c = this.plugins.piwikTicks;

        // highligh canvas
        if (c.showHighlight) {
            c.piwikHighlightCanvas = new $.jqplot.GenericCanvas();

            this.eventCanvas._elem.before(c.piwikHighlightCanvas.createElement(
                this._gridPadding, 'jqplot-piwik-highlight-canvas', this._plotDimensions, this));
            c.piwikHighlightCanvas.setContext();
        }

        // grid canvas
        if (c.showTicks) {
            var dimensions = this._plotDimensions;
            dimensions.height += 6;
            c.piwikTicksCanvas = new $.jqplot.GenericCanvas();
            this.series[0].shadowCanvas._elem.before(c.piwikTicksCanvas.createElement(
                this._gridPadding, 'jqplot-piwik-ticks-canvas', dimensions, this));
            c.piwikTicksCanvas.setContext();

            var ctx = c.piwikTicksCanvas._ctx;

            var ticks = this.data[0];
            var totalWidth = ctx.canvas.width;
            var tickWidth = totalWidth / ticks.length;

            var xaxisLabels = this.axes.xaxis.ticks;

            for (var i = 0; i < ticks.length; i++) {
                var pos = Math.round(i * tickWidth + tickWidth / 2);
                var full = xaxisLabels[i] && xaxisLabels[i] != ' ';
                drawLine(ctx, pos, full, c.showGrid, c.tickColor);
            }
        }
    };

    $.jqplot.preInitHooks.push($.jqplot.PiwikTicks.init);
    $.jqplot.postDrawHooks.push($.jqplot.PiwikTicks.postDraw);

    // draw a 1px line
    function drawLine(ctx, x, full, showGrid, color) {
        ctx.save();
        ctx.strokeStyle = color;

        ctx.beginPath();
        ctx.lineWidth = 2;
        var top = 0;
        if ((full && !showGrid) || !full) {
            top = ctx.canvas.height - 5;
        }
        ctx.moveTo(x, top);
        ctx.lineTo(x, full ? ctx.canvas.height : ctx.canvas.height - 2);
        ctx.stroke();

        // canvas renders line slightly too large
        ctx.clearRect(x, 0, x + 1, ctx.canvas.height);

        ctx.restore();
    }

    // trigger the event jqplotPiwikTickOver when the mouse enters
    // and new tick. this is used for tooltips.
    function handleMouseMove(ev, gridpos, datapos, neighbor, plot) {
        var c = plot.plugins.piwikTicks;

        var tick = Math.floor(datapos.xaxis + 0.5) - 1;
        if (tick !== c.currentXTick) {
            c.currentXTick = tick;
            plot.target.trigger('jqplotPiwikTickOver', [tick]);
            highlight(plot, tick);
        }
    }

    function handleMouseLeave(ev, gridpos, datapos, neighbor, plot) {
        unHighlight(plot);
        plot.plugins.piwikTicks.currentXTick = false;
    }

    // highlight a marker
    function highlight(plot, tick) {
        var c = plot.plugins.piwikTicks;

        if (!c.showHighlight) {
            return;
        }

        unHighlight(plot);

        for (var i = 0; i < plot.series.length; i++) {
            var series = plot.series[i];
            var seriesMarkerRenderer = series.markerRenderer;

            c.markerRenderer.style = seriesMarkerRenderer.style;
            c.markerRenderer.size = seriesMarkerRenderer.size + 5;

            var rgba = $.jqplot.getColorComponents(seriesMarkerRenderer.color);
            var newrgb = [rgba[0], rgba[1], rgba[2]];
            var alpha = rgba[3] * .4;
            c.markerRenderer.color = 'rgba(' + newrgb[0] + ',' + newrgb[1] + ',' + newrgb[2] + ',' + alpha + ')';
            c.markerRenderer.init();

            var position = series.gridData[tick];
            if (typeof position !== 'undefined') {
                c.markerRenderer.draw(position[0], position[1], c.piwikHighlightCanvas._ctx);
            }
        }
    }

    function unHighlight(plot) {
        var canvas = plot.plugins.piwikTicks.piwikHighlightCanvas;
        if (canvas !== null) {
            var ctx = canvas._ctx;
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
        }
    }

})(jQuery);

// ------------------------------------------------------------
//  LEGEND PLUGIN FOR JQPLOT
//  Render legend on canvas
// ------------------------------------------------------------

(function ($) {

    $.jqplot.CanvasLegendRenderer = function (options) {
        // canvas for the legend
        this.legendCanvas = null;
        // is it a legend for a single metric only (pie chart)?
        this.singleMetric = false;
        // render the legend?
        this.show = false;

        $.extend(true, this, options);
    };

    $.jqplot.CanvasLegendRenderer.init = function (target, data, opts) {
        // add plugin as an attribute to the plot
        var options = opts || {};
        this.plugins.canvasLegend = new $.jqplot.CanvasLegendRenderer(options.canvasLegend);

        // add padding above the grid
        // legend will be put there
        if (this.plugins.canvasLegend.show) {
            options.gridPadding = {
                top: 21, right: 0
            };
        }

    };

    // render the legend
    $.jqplot.CanvasLegendRenderer.postDraw = function () {
        var plot = this;
        var legend = plot.plugins.canvasLegend;

        if (!legend.show) {
            return;
        }

        // initialize legend canvas
        var padding = {top: 0, right: this._gridPadding.right, bottom: 0, left: this._gridPadding.left};
        var dimensions = {width: this._plotDimensions.width, height: this._gridPadding.top};
        var width = this._plotDimensions.width - this._gridPadding.left - this._gridPadding.right;

        legend.legendCanvas = new $.jqplot.GenericCanvas();
        this.eventCanvas._elem.before(legend.legendCanvas.createElement(
            padding, 'jqplot-legend-canvas', dimensions, plot));
        legend.legendCanvas.setContext();

        var ctx = legend.legendCanvas._ctx;
        ctx.save();
        ctx.font = '11px ' + require('piwik/UI').getLabelFontFamily()

        // render series names
        var x = 0;
        var series = plot.legend._series;
        for (var i = 0; i < series.length; i++) {
            var s = series[i];
            var label;
            if (legend.labels && legend.labels[i]) {
                label = legend.labels[i];
            } else {
                label = s.label.toString();
            }

            ctx.fillStyle = s.color;
            if (legend.singleMetric) {
                ctx.fillStyle = legend.singleMetricColor;
            }

            ctx.fillRect(x, 10, 10, 2);
            x += 15;

            var nextX = x + ctx.measureText(label).width + 20;

            if (nextX + 70 > width) {
                ctx.fillText("[...]", x, 15);
                x += ctx.measureText("[...]").width + 20;
                break;
            }

            ctx.fillText(label, x, 15);
            x = nextX;
        }

        legend.width = x;

        ctx.restore();
    };

    $.jqplot.preInitHooks.push($.jqplot.CanvasLegendRenderer.init);
    $.jqplot.postDrawHooks.push($.jqplot.CanvasLegendRenderer.postDraw);

})(jQuery);

// ------------------------------------------------------------
//  SERIES PICKER
// ------------------------------------------------------------

(function ($, require) {
    $.jqplot.preInitHooks.push(function (target, data, options) {
        // create the series picker
        var dataTable = $('#' + target).closest('.dataTable').data('uiControlObject');
        if (!dataTable) { // if we're not dealing w/ a DataTable visualization, don't add the series picker
            return;
        }

        var SeriesPicker = require('piwik/DataTableVisualizations/Widgets').SeriesPicker;
        var seriesPicker = new SeriesPicker(dataTable);

        // handle placeSeriesPicker event
        var plot = this;
        $(seriesPicker).bind('placeSeriesPicker', function () {
            this.domElem.css('margin-left', plot._gridPadding.left + 'px');
            $('.jqplot-legend-canvas', $('#' + target)).css({paddingLeft: '34px'});
            plot.baseCanvas._elem.before(this.domElem);
        });

        // handle seriesPicked event
        $(seriesPicker).bind('seriesPicked', function (e, columns, rows) {
            dataTable.changeSeries(columns, rows);
        });

        this.plugins.seriesPicker = seriesPicker;
    });

    $.jqplot.postDrawHooks.push(function () {
        this.plugins.seriesPicker.init();
    });
})(jQuery, require);

// ------------------------------------------------------------
//  PIE CHART LEGEND PLUGIN FOR JQPLOT
//  Render legend inside the pie graph
// ------------------------------------------------------------

(function ($) {

    $.jqplot.PieLegend = function (options) {
        // canvas for the legend
        this.pieLegendCanvas = null;
        // render the legend?
        this.show = false;

        $.extend(true, this, options);
    };

    $.jqplot.PieLegend.init = function (target, data, opts) {
        // add plugin as an attribute to the plot
        var options = opts || {};
        this.plugins.pieLegend = new $.jqplot.PieLegend(options.pieLegend);
    };

    // render the legend
    $.jqplot.PieLegend.postDraw = function () {
        var plot = this;
        var legend = plot.plugins.pieLegend;

        if (!legend.show) {
            return;
        }

        var series = plot.series[0];
        var angles = series._sliceAngles;
        var radius = series._diameter / 2;
        var center = series._center;
        var colors = this.seriesColors;

        // concentric line angles
        var lineAngles = [];
        for (var i = 0; i < angles.length; i++) {
            lineAngles.push((angles[i][0] + angles[i][1]) / 2 + Math.PI / 2);
        }

        // labels
        var labels = [];
        var data = series._plotData;
        for (i = 0; i < data.length; i++) {
            labels.push(data[i][0]);
        }

        // initialize legend canvas
        legend.pieLegendCanvas = new $.jqplot.GenericCanvas();
        plot.series[0].canvas._elem.before(legend.pieLegendCanvas.createElement(
            plot._gridPadding, 'jqplot-pie-legend-canvas', plot._plotDimensions, plot));
        legend.pieLegendCanvas.setContext();

        var ctx = legend.pieLegendCanvas._ctx;
        ctx.save();

        ctx.font = '11px ' + require('piwik/UI').getLabelFontFamily()

        // render labels
        var height = legend.pieLegendCanvas._elem.height();
        var x1, x2, y1, y2, lastY2 = false, right, lastRight = false;
        for (i = 0; i < labels.length; i++) {
            var label = labels[i];

            ctx.strokeStyle = colors[i % colors.length];
            ctx.lineCap = 'round';
            ctx.lineWidth = 1;

            // concentric line
            x1 = center[0] + Math.sin(lineAngles[i]) * (radius);
            y1 = center[1] - Math.cos(lineAngles[i]) * (radius);

            x2 = center[0] + Math.sin(lineAngles[i]) * (radius + 7);
            y2 = center[1] - Math.cos(lineAngles[i]) * (radius + 7);

            right = x2 > center[0];

            // move close labels
            if (lastY2 !== false && lastRight == right && (
                (right && y2 - lastY2 < 13) ||
                (!right && lastY2 - y2 < 13))) {

                if (x1 > center[0]) {
                    // move down if the label is in the right half of the graph
                    y2 = lastY2 + 13;
                } else {
                    // move up if in left halt
                    y2 = lastY2 - 13;
                }
            }

            if (y2 < 4 || y2 + 4 > height) {
                continue;
            }

            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);

            ctx.closePath();
            ctx.stroke();

            // horizontal line
            ctx.beginPath();
            ctx.moveTo(x2, y2);
            if (right) {
                ctx.lineTo(x2 + 5, y2);
            } else {
                ctx.lineTo(x2 - 5, y2);
            }

            ctx.closePath();
            ctx.stroke();

            lastY2 = y2;
            lastRight = right;

            // text
            if (right) {
                var x = x2 + 9;
            } else {
                var x = x2 - 9 - ctx.measureText(label).width;
            }

            ctx.fillStyle = legend.labelColor;
            ctx.fillText(label, x, y2 + 3);
        }

        ctx.restore();
    };

    $.jqplot.preInitHooks.push($.jqplot.PieLegend.init);
    $.jqplot.postDrawHooks.push($.jqplot.PieLegend.postDraw);

})(jQuery, require);

// ------------------------------------------------------------
//  MATOMO INCOMPLETE DATA INDICATOR FOR JQPLOT
//  Show a dashed line to the last point to indicate incomplete
// ------------------------------------------------------------

(function ($) {

    $.jqplot.LineRenderer.prototype.draw = function(ctx, gd, options, plot) {
        var i;
        // get a copy of the options, so we don't modify the original object.
        var opts = $.extend(true, {}, options);
        var shadow = (opts.shadow != undefined) ? opts.shadow : this.shadow;
        var showLine = (opts.showLine != undefined) ? opts.showLine : this.showLine;
        var fill = (opts.fill != undefined) ? opts.fill : this.fill;
        var fillAndStroke = (opts.fillAndStroke != undefined) ? opts.fillAndStroke : this.fillAndStroke;
        var xmin, ymin, xmax, ymax;

        // Only change in this overridden method, to pass the option to the renderers
        if (plot.options.hasOwnProperty('incompleteDataPoints')) {
            opts.incompleteDataPoints = plot.options.incompleteDataPoints;
        }

        if (!Array.isArray(opts.incompleteDataPoints)) {
            opts.incompleteDataPoints = [];
        }

        ctx.save();
        if (gd.length) {
            if (showLine) {
                // if we fill, we'll have to add points to close the curve.
                if (fill) {
                    if (this.fillToZero) {
                        // have to break line up into shapes at axis crossings
                        var negativeColor = this.negativeColor;
                        if (! this.useNegativeColors) {
                            negativeColor = opts.fillStyle;
                        }
                        var isnegative = false;
                        var posfs = opts.fillStyle;

                        // if stoking line as well as filling, get a copy of line data.
                        if (fillAndStroke) {
                            var fasgd = gd.slice(0);
                        }
                        // if not stacked, fill down to axis
                        if (this.index == 0 || !this._stack) {

                            var tempgd = [];
                            var pd = (this.renderer.smooth) ? this.renderer._smoothedPlotData : this._plotData;
                            this._areaPoints = [];
                            var pyzero = this._yaxis.series_u2p(this.fillToValue);
                            var pxzero = this._xaxis.series_u2p(this.fillToValue);

                            opts.closePath = true;

                            if (this.fillAxis == 'y') {
                                tempgd.push([gd[0][0], pyzero]);
                                this._areaPoints.push([gd[0][0], pyzero]);

                                for (var i=0; i<gd.length-1; i++) {
                                    tempgd.push(gd[i]);
                                    this._areaPoints.push(gd[i]);
                                    // do we have an axis crossing?
                                    if (pd[i][1] * pd[i+1][1] <= 0) {
                                        if (pd[i][1] < 0) {
                                            isnegative = true;
                                            opts.fillStyle = negativeColor;
                                        }
                                        else {
                                            isnegative = false;
                                            opts.fillStyle = posfs;
                                        }

                                        var xintercept = gd[i][0] + (gd[i+1][0] - gd[i][0]) * (pyzero-gd[i][1])/(gd[i+1][1] - gd[i][1]);
                                        tempgd.push([xintercept, pyzero]);
                                        this._areaPoints.push([xintercept, pyzero]);
                                        // now draw this shape and shadow.
                                        if (shadow) {
                                            this.renderer.shadowRenderer.draw(ctx, tempgd, opts);
                                        }
                                        this.renderer.shapeRenderer.draw(ctx, tempgd, opts);
                                        // now empty temp array and continue
                                        tempgd = [[xintercept, pyzero]];
                                        // this._areaPoints = [[xintercept, pyzero]];
                                    }
                                }
                                if (pd[gd.length-1][1] < 0) {
                                    isnegative = true;
                                    opts.fillStyle = negativeColor;
                                }
                                else {
                                    isnegative = false;
                                    opts.fillStyle = posfs;
                                }
                                tempgd.push(gd[gd.length-1]);
                                this._areaPoints.push(gd[gd.length-1]);
                                tempgd.push([gd[gd.length-1][0], pyzero]);
                                this._areaPoints.push([gd[gd.length-1][0], pyzero]);
                            }
                            // now draw the last area.
                            if (shadow) {
                                this.renderer.shadowRenderer.draw(ctx, tempgd, opts);
                            }
                            this.renderer.shapeRenderer.draw(ctx, tempgd, opts);

                        }
                        // if stacked, fill to line below
                        else {
                            var prev = this._prevGridData;
                            for (var i=prev.length; i>0; i--) {
                                gd.push(prev[i-1]);
                                // this._areaPoints.push(prev[i-1]);
                            }
                            if (shadow) {
                                this.renderer.shadowRenderer.draw(ctx, gd, opts);
                            }
                            this._areaPoints = gd;
                            this.renderer.shapeRenderer.draw(ctx, gd, opts);
                        }
                    }
                    /////////////////////////
                    // Not filled to zero
                    ////////////////////////
                    else {
                        // if stoking line as well as filling, get a copy of line data.
                        if (fillAndStroke) {
                            var fasgd = gd.slice(0);
                        }
                        // if not stacked, fill down to axis
                        if (this.index == 0 || !this._stack) {
                            // var gridymin = this._yaxis.series_u2p(this._yaxis.min) - this.gridBorderWidth / 2;
                            var gridymin = ctx.canvas.height;
                            // IE doesn't return new length on unshift
                            gd.unshift([gd[0][0], gridymin]);
                            var len = gd.length;
                            gd.push([gd[len - 1][0], gridymin]);
                        }
                        // if stacked, fill to line below
                        else {
                            var prev = this._prevGridData;
                            for (var i=prev.length; i>0; i--) {
                                gd.push(prev[i-1]);
                            }
                        }
                        this._areaPoints = gd;

                        if (shadow) {
                            this.renderer.shadowRenderer.draw(ctx, gd, opts);
                        }

                        this.renderer.shapeRenderer.draw(ctx, gd, opts);
                    }
                    if (fillAndStroke) {
                        var fasopts = $.extend(true, {}, opts, {fill:false, closePath:false});
                        this.renderer.shapeRenderer.draw(ctx, fasgd, fasopts);
                        // now draw the markers
                        if (this.markerRenderer.show) {
                            if (this.renderer.smooth) {
                                fasgd = this.gridData;
                            }
                            for (i=0; i<fasgd.length; i++) {
                                this.markerRenderer.draw(fasgd[i][0], fasgd[i][1], ctx, opts.markerOptions);
                            }
                        }
                    }
                }
                else {

                    if (this.renderer.bands.show) {
                        var bdat;
                        var bopts = $.extend(true, {}, opts);

                        if (this.renderer.bands.showLines) {
                            bdat = (this.renderer.smooth) ? this.renderer._hiBandSmoothedData : this.renderer._hiBandGridData;
                            this.renderer.shapeRenderer.draw(ctx, bdat, opts);
                            bdat = (this.renderer.smooth) ? this.renderer._lowBandSmoothedData : this.renderer._lowBandGridData;
                            this.renderer.shapeRenderer.draw(ctx, bdat, bopts);
                        }

                        if (this.renderer.bands.fill) {
                            if (this.renderer.smooth) {
                                bdat = this.renderer._hiBandSmoothedData.concat(this.renderer._lowBandSmoothedData.reverse());
                            }
                            else {
                                bdat = this.renderer._hiBandGridData.concat(this.renderer._lowBandGridData.reverse());
                            }
                            this._areaPoints = bdat;
                            bopts.closePath = true;
                            bopts.fill = true;
                            bopts.fillStyle = this.renderer.bands.fillColor;
                            this.renderer.shapeRenderer.draw(ctx, bdat, bopts);
                        }
                    }

                    if (shadow) {
                        this.renderer.shadowRenderer.draw(ctx, gd, opts);
                    }

                    this.renderer.shapeRenderer.draw(ctx, gd, opts);
                }
            }
            // calculate the bounding box
            var xmin = xmax = ymin = ymax = null;
            for (i=0; i<this._areaPoints.length; i++) {
                var p = this._areaPoints[i];
                if (xmin > p[0] || xmin == null) {
                    xmin = p[0];
                }
                if (ymax < p[1] || ymax == null) {
                    ymax = p[1];
                }
                if (xmax < p[0] || xmax == null) {
                    xmax = p[0];
                }
                if (ymin > p[1] || ymin == null) {
                    ymin = p[1];
                }
            }

            if (this.type === 'line' && this.renderer.bands.show) {
                ymax = this._yaxis.series_u2p(this.renderer.bands._min);
                ymin = this._yaxis.series_u2p(this.renderer.bands._max);
            }

            this._boundingBox = [[xmin, ymax], [xmax, ymin]];

            // now draw the markers
            if (this.markerRenderer.show && !fill) {
                if (this.renderer.smooth) {
                    gd = this.gridData;
                }
                for (i=0; i<gd.length; i++) {
                    if (gd[i][0] != null && gd[i][1] != null) {
                        this.markerRenderer.draw(gd[i][0], gd[i][1], ctx, opts.markerOptions);
                    }
                }
            }
        }

        ctx.restore();
    };

    $.jqplot.ShapeRenderer.prototype.draw = function(ctx, points, options) {
        ctx.save();
        var opts = (options != null) ? options : {};
        var fill = (opts.fill != null) ? opts.fill : this.fill;
        var closePath = (opts.closePath != null) ? opts.closePath : this.closePath;
        var fillRect = (opts.fillRect != null) ? opts.fillRect : this.fillRect;
        var strokeRect = (opts.strokeRect != null) ? opts.strokeRect : this.strokeRect;
        var clearRect = (opts.clearRect != null) ? opts.clearRect : this.clearRect;
        var isarc = (opts.isarc != null) ? opts.isarc : this.isarc;
        var linePattern = (opts.linePattern != null) ? opts.linePattern : this.linePattern;
        var ctxPattern = $.jqplot.LinePattern(ctx, linePattern);
        ctx.lineWidth = opts.lineWidth || this.lineWidth;
        ctx.lineJoin = opts.lineJoin || this.lineJoin;
        ctx.lineCap = opts.lineCap || this.lineCap;
        ctx.strokeStyle = (opts.strokeStyle || opts.color) || this.strokeStyle;
        ctx.fillStyle = opts.fillStyle || this.fillStyle;
        ctx.beginPath();

        let incompleteDataPoints = [];

        if (!closePath && !fill && Array.isArray(opts.incompleteDataPoints)) {
            // only do the incomplete visualization for line charts
            incompleteDataPoints = opts.incompleteDataPoints;
        }

        if (isarc) {
            ctx.arc(points[0], points[1], points[2], points[3], points[4], true);
            if (closePath) {
                ctx.closePath();
            }
            if (fill) {
                ctx.fill();
            }
            else {
                ctx.stroke();
            }
            ctx.restore();
            return;
        }
        else if (clearRect) {
            ctx.clearRect(points[0], points[1], points[2], points[3]);
            ctx.restore();
            return;
        }
        else if (fillRect || strokeRect) {
            if (fillRect) {
                ctx.fillRect(points[0], points[1], points[2], points[3]);
            }
            if (strokeRect) {
                ctx.strokeRect(points[0], points[1], points[2], points[3]);
                ctx.restore();
                return;
            }
        }

        if (!points || !points.length) {
            return;
        }

        let move = true;

        for (let i = 0; i < points.length; i++) {
            // skip to the first non-null point and move to it.
            if (points[i][0] === null && points[i][1] === null) {
                continue;
            }

            if (move) {
                move = false;

                ctxPattern.moveTo(points[i][0], points[i][1]);
                continue;
            }

            // draw line to current point or skip if incomplete data point
            if (incompleteDataPoints.includes(i)) {
                ctxPattern.moveTo(points[i][0], points[i][1]);
            } else {
                ctxPattern.lineTo(points[i][0], points[i][1]);
            }
        }

        if (closePath) {
            ctxPattern.closePath();
        }

        if (fill) {
            ctx.fill();
        } else {
            ctx.stroke();
        }

        // draw dashed lines for incomplete data points
        ctx.beginPath();
        ctx.setLineDash([3, 3]);

        move = true;

        for (let i = 0; i < points.length; i++) {
            // skip to the first non-null point and move to it.
            if (points[i][0] === null && points[i][1] === null) {
                continue;
            }

            if (move) {
                move = false;

                ctxPattern.moveTo(points[i][0], points[i][1]);
                continue;
            }

            // draw dashed line to current point or skip if not incomplete data point
            if (!incompleteDataPoints.includes(i)) {
                ctxPattern.moveTo(points[i][0], points[i][1]);
            } else {
                ctxPattern.lineTo(points[i][0], points[i][1]);
            }
        }

        ctx.stroke();
        ctx.closePath();
        ctx.restore();
    };

    // Only overriding this method to prevent drawing the shadow for the last line segment
    $.jqplot.ShadowRenderer.prototype.draw = function(ctx, points, options) {
        ctx.save();
        var opts = (options != null) ? options : {};
        var fill = (opts.fill != null) ? opts.fill : this.fill;
        var fillRect = (opts.fillRect != null) ? opts.fillRect : this.fillRect;
        var closePath = (opts.closePath != null) ? opts.closePath : this.closePath;
        var offset = (opts.offset != null) ? opts.offset : this.offset;
        var alpha = (opts.alpha != null) ? opts.alpha : this.alpha;
        var depth = (opts.depth != null) ? opts.depth : this.depth;
        var isarc = (opts.isarc != null) ? opts.isarc : this.isarc;
        var linePattern = (opts.linePattern != null) ? opts.linePattern : this.linePattern;
        ctx.lineWidth = (opts.lineWidth != null) ? opts.lineWidth : this.lineWidth;
        ctx.lineJoin = (opts.lineJoin != null) ? opts.lineJoin : this.lineJoin;
        ctx.lineCap = (opts.lineCap != null) ? opts.lineCap : this.lineCap;
        ctx.strokeStyle = opts.strokeStyle || this.strokeStyle || 'rgba(0,0,0,'+alpha+')';
        ctx.fillStyle = opts.fillStyle || this.fillStyle || 'rgba(0,0,0,'+alpha+')';

        let incompleteDataPoints = [];

        if (!closePath && !fill && Array.isArray(opts.incompleteDataPoints)) {
            // only do the incomplete visualization for line charts
            incompleteDataPoints = opts.incompleteDataPoints;
        }

        for (let j= 0; j < depth; j++) {
            const ctxPattern = $.jqplot.LinePattern(ctx, linePattern);

            ctx.translate(Math.cos(this.angle*Math.PI/180)*offset, Math.sin(this.angle*Math.PI/180)*offset);
            ctxPattern.beginPath();

            if (isarc) {
                ctx.arc(points[0], points[1], points[2], points[3], points[4], true);
            }
            else if (fillRect) {
                ctx.fillRect(points[0], points[1], points[2], points[3]);
            }
            else if (points && points.length) {
                let move = true;

                for (let i = 0; i < points.length; i++) {
                    // skip to the first non-null point and move to it.
                    if (points[i][0] === null && points[i][1] === null) {
                        continue;
                    }

                    if (move) {
                        move = false;

                        ctxPattern.moveTo(points[i][0], points[i][1]);
                        continue;
                    }

                    // draw shadow line to current point or skip if incomplete data point
                    if (incompleteDataPoints.includes(i)) {
                        ctxPattern.moveTo(points[i][0], points[i][1]);
                    } else {
                        ctxPattern.lineTo(points[i][0], points[i][1]);
                    }
                }
            }

            if (closePath) {
                ctxPattern.closePath();
            }

            if (fill) {
                ctx.fill();
            }
            else {
                ctx.stroke();
            }
        }

        ctx.restore();
    };
})(jQuery);
