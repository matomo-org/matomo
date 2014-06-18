/**
 * Piwik - free/libre analytics platform
 *
 * DataTable UI class for JqplotGraph/Pie.
 *
 * @link http://www.jqplot.com
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI'),
        JqplotGraphDataTable = exports.JqplotGraphDataTable;

    exports.JqplotPieGraphDataTable = function (element) {
        JqplotGraphDataTable.call(this, element);
    };

    $.extend(exports.JqplotPieGraphDataTable.prototype, JqplotGraphDataTable.prototype, {

        _setJqplotParameters: function (params) {
            JqplotGraphDataTable.prototype._setJqplotParameters.call(this, params);

            this.jqplotParams.seriesDefaults = {
                renderer: $.jqplot.PieRenderer,
                rendererOptions: {
                    shadow: false,
                    showDataLabels: false,
                    sliceMargin: 1,
                    startAngle: 35
                }
            };

            this.jqplotParams.piwikTicks = {
                showTicks: false,
                showGrid: false,
                showHighlight: false,
                tickColor: this.tickColor
            };

            this.jqplotParams.legend = {
                show: false
            };
            this.jqplotParams.pieLegend = {
                show: true,
                labelColor: this.singleMetricColor
            };
            this.jqplotParams.canvasLegend = {
                show: true,
                singleMetric: true,
                singleMetricColor: this.singleMetricColor
            };

            // pie charts have a different data format
            if (!(this.data[0][0] instanceof Array)) { // check if already in different format
                for (var i = 0; i < this.data[0].length; i++) {
                    this.data[0][i] = [this.jqplotParams.axes.xaxis.ticks[i], this.data[0][i]];
                }
            }
        },

        _showDataPointTooltip: function (element, seriesIndex, valueIndex) {
            var value = this.formatY(this.data[0][valueIndex][1], 0);
            var series = this.jqplotParams.series[0].label;
            var percentage = this.tooltip.percentages[0][valueIndex];

            var label = this.data[0][valueIndex][0];

            var text = '<strong>' + percentage + '%</strong> (' + value + ' ' + series + ')';
            $(element).tooltip({
                track:   true,
                items:   '*',
                content: '<h3>' + label + '</h3>' + text,
                show: false,
                hide: false
            }).trigger('mouseover');
        }
    });

})(jQuery, require);
