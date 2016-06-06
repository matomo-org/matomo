/**
 * Piwik - free/libre analytics platform
 *
 * DataTable UI class for JqplotGraph/Evolution.
 *
 * @link http://www.jqplot.com
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI'),
        JqplotGraphDataTable = exports.JqplotGraphDataTable,
        JqplotGraphDataTablePrototype = JqplotGraphDataTable.prototype;

    exports.JqplotEvolutionGraphDataTable = function (element) {
        JqplotGraphDataTable.call(this, element);
    };

    $.extend(exports.JqplotEvolutionGraphDataTable.prototype, JqplotGraphDataTablePrototype, {

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
                    showHighlight: true,
                    tickColor: this.tickColor
                }
            };

            if (this.props.show_line_graph) {
                defaultParams.seriesDefaults = {
                    lineWidth: 1,
                    markerOptions: {
                        style: "filledCircle",
                        size: 6,
                        shadow: false
                    }
                };
            } else {
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
            }

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

                        if (url && -1 === url.indexOf('#')) {
                            var module = broadcast.getValueFromHash('module');
                            var action = broadcast.getValueFromHash('action');
                            var idGoal = broadcast.getValueFromHash('idGoal');
                            var idDimension = broadcast.getValueFromHash('idDimension');
                            var idSite = broadcast.getValueFromUrl('idSite', url);
                            var period = broadcast.getValueFromUrl('period', url);
                            var date   = broadcast.getValueFromUrl('date', url);

                            if (module && action) {
                                url += '#module=' + module + '&action=' + action;

                                if (idSite) {
                                    url += '&idSite=' + idSite;
                                }

                                if (idGoal) {
                                    url += '&idGoal=' + idGoal;
                                }

                                if (idDimension) {
                                    url += '&idDimension=' + idDimension;
                                }

                                if (period) {
                                    url += '&period=' + period;
                                }

                                if (period) {
                                    url += '&date=' + date;
                                }
                            }
                        }

                        piwikHelper.redirectToUrl(url);
                    }
                })
                .on('jqplotPiwikTickOver', function (e, tick) {
                    lastTick = tick;
                    var label;
                    if (typeof self.jqplotParams.axes.xaxis.labels != 'undefined') {
                        label = self.jqplotParams.axes.xaxis.labels[tick];
                    } else {
                        label = self.jqplotParams.axes.xaxis.ticks[tick];
                    }

                    var text = [];
                    for (var d = 0; d < self.data.length; d++) {
                        var value = self.formatY(self.data[d][tick], d);
                        var series = self.jqplotParams.series[d].label;
                        text.push('<strong>' + value + '</strong> ' + piwikHelper.htmlEntities(series));
                    }
                    var content = '<h3>'+piwikHelper.htmlEntities(label)+'</h3>'+text.join('<br />');

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
        }
    });

})(jQuery, require);