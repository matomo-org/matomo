/**
 * Matomo - free/libre analytics platform
 *
 * DataTable UI class for JqplotGraph/StackedBarEvolution.
 *
 * @link http://www.jqplot.com
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI'),
        JqplotGraphDataTable = exports.JqplotGraphDataTable,
        JqplotGraphDataTablePrototype = JqplotGraphDataTable.prototype;

    exports.JqplotStackedBarEvolutionGraphDataTable = function (element) {
        JqplotGraphDataTable.call(this, element);
    };

    $.extend(exports.JqplotStackedBarEvolutionGraphDataTable.prototype, JqplotGraphDataTablePrototype, {

        _setJqplotParameters: function (params) {
            JqplotGraphDataTablePrototype._setJqplotParameters.call(this, params);

            var defaultParams = {
                axes: {
                    xaxis: {
                        pad: 1.0,
                        renderer: $.jqplot.CategoryAxisRenderer,
                        tickOptions: {
                            showGridline: false
                        }
                    }
                },
                piwikTicks: {
                    showTicks: true,
                    showGrid: true,
                    showHighlight: false,
                    tickColor: this.tickColor
                }
            };

            defaultParams.seriesDefaults = {
                renderer: $.jqplot.BarRenderer,
                rendererOptions: {
                    shadowOffset: 1,
                    shadowDepth: 2,
                    shadowAlpha: .2,
                    fillToZero: true,
                    barMargin: this.data[0].length > 10 ? 2 : 10
                }
            };

            defaultParams.stackSeries = true;

            var overrideParams = {
                legend: {
                    show: false
                },
                canvasLegend: {
                    show: true
                }
            };
            this.jqplotParams = $.extend(true, {}, defaultParams, this.jqplotParams, overrideParams);
        },

        _bindEvents: function () {
            JqplotGraphDataTablePrototype._bindEvents.call(this);

            var self = this;
            var lastTick = false;

            $('#' + this.targetDivId)
                .on('jqplotMouseLeave', function (e, s, i, d) {
                    $(this).css('cursor', 'default');
                    JqplotGraphDataTablePrototype._destroyDataPointTooltip.call(this, $(this));
                })
                .on('jqplotClick', function (e, s, i, d) {
                    if (lastTick !== false && typeof self.jqplotParams.axes.xaxis.onclick != 'undefined'
                        && typeof self.jqplotParams.axes.xaxis.onclick[lastTick] == 'string') {
                        var url = self.jqplotParams.axes.xaxis.onclick[lastTick];

                        broadcast.propagateNewPage(url);
                    }
                })
                .on('jqplotPiwikTickOver', function (e, tick) {
                    lastTick = tick;
                    var label;

                    var dataByAxis = {};
                    var totalValue = 0;
                    for (var d = 0; d < self.data.length; ++d) {
                        var valueUnformatted = self.data[d][tick];
                        if (typeof valueUnformatted === 'undefined' || valueUnformatted === null) {
                            continue;
                        }

                        totalValue += valueUnformatted;

                        var axis = self.jqplotParams.series[d]._xaxis || 'xaxis';
                        if (!dataByAxis[axis]) {
                            dataByAxis[axis] = [];
                        }

                        var value = self.formatY(valueUnformatted, d);
                        var series = self.jqplotParams.series[d].label;

                        var seriesColor = self.jqplotParams.seriesColors[d];

                        dataByAxis[axis].push('<span class="tooltip-series-color" style="background-color: ' + seriesColor + ';"></span>' + '<strong>' + value + '</strong> ' + piwikHelper.htmlEntities(series));
                    }

                    dataByAxis[axis].push('<span class="tooltip-series-color" style="background-color: #000;"></span>' + '<strong>' + self.formatY(totalValue, 0) + '</strong> ' + _pk_translate('General_Total'));


                    var xAxisCount = 0;
                    Object.keys(self.jqplotParams.axes).forEach(function (axis) {
                        if (axis.substring(0, 1) === 'x') {
                            ++xAxisCount;
                        }
                    });

                    var content = '';
                    for (var i = 0; i < xAxisCount; ++i) {
                        var axisName = i === 0 ? 'xaxis' : 'x' + (i + 1) + 'axis';
                        if (!dataByAxis[axisName] || !dataByAxis[axisName].length) {
                            continue;
                        }

                        if (typeof self.jqplotParams.axes[axisName].labels != 'undefined') {
                            label = self.jqplotParams.axes[axisName].labels[tick];
                        } else {
                            label = self.jqplotParams.axes[axisName].ticks[tick];
                        }

                        if (typeof label === 'undefined') { // sanity check
                            continue;
                        }

                        content += '<h3 class="evolution-tooltip-header">'+piwikHelper.htmlEntities(label)+'</h3>'+dataByAxis[axisName].join('<br />');
                    }

                    $(this).tooltip({
                        track:   true,
                        items:   'div',
                        content: content,
                        show: false,
                        hide: false
                    }).trigger('mouseover');
                    if (typeof self.jqplotParams.axes.xaxis.onclick != 'undefined'
                        && typeof self.jqplotParams.axes.xaxis.onclick[lastTick] == 'string') {
                        $(this).css('cursor', 'pointer');
                    }
                });

            this.setYTicks();
        },

        _destroyDataPointTooltip: function () {
            // do nothing, tooltips are destroyed in the jqplotMouseLeave event
        },

        render: function () {
            JqplotGraphDataTablePrototype.render.call(this);

            if (initializeSparklines) {
                initializeSparklines();
            }
        },

        setYTicksForAxis: function (axisName, axis) {
            // calculate maximum x value of all data sets
            var maxCrossDataSets = 0;

            for (var j = 0; j < this.data[0].length; j++) {
                var sum = 0;
                for (var i = 0; i < this.data.length; i++) {
                    if (this.jqplotParams.series[i].yaxis == axisName) {
                        sum += this.data[i][j];
                    }
                }
                sum = parseFloat(sum);
                if (sum > maxCrossDataSets) {
                    maxCrossDataSets = sum;
                }
            }

            // add little padding on top
            maxCrossDataSets += Math.round(maxCrossDataSets * .03);

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
              && axis.tickOptions.formatString.substring(2, 3) == '%'
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

    });

})(jQuery, require);
