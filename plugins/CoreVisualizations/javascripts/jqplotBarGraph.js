/**
 * Piwik - free/libre analytics platform
 *
 * DataTable UI class for JqplotGraph/Bar.
 *
 * @link http://www.jqplot.com
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var exports = require('piwik/UI'),
        JqplotGraphDataTable = exports.JqplotGraphDataTable;

    exports.JqplotBarGraphDataTable = function (element) {
        JqplotGraphDataTable.call(this, element);
    };

    $.extend(exports.JqplotBarGraphDataTable.prototype, JqplotGraphDataTable.prototype, {

        _setJqplotParameters: function (params) {
            JqplotGraphDataTable.prototype._setJqplotParameters.call(this, params);

            this.jqplotParams.seriesDefaults = {
                renderer: $.jqplot.BarRenderer,
                rendererOptions: {
                    shadowOffset: 1,
                    shadowDepth: 2,
                    shadowAlpha: .2,
                    fillToZero: true,
                    barMargin: this.data[0].length > 10 ? 2 : 10
                }
            };

            this.jqplotParams.piwikTicks = {
                showTicks: true,
                showGrid: false,
                showHighlight: false,
                tickColor: this.tickColor
            };

            this.jqplotParams.axes.xaxis.renderer = $.jqplot.CategoryAxisRenderer;
            this.jqplotParams.axes.xaxis.tickOptions = {
                showGridline: false
            };

            this.jqplotParams.canvasLegend = {
                show: true
            };
        },

        _bindEvents: function () {
            this.setYTicks();
            JqplotGraphDataTable.prototype._bindEvents.call(this);
        },

        _showDataPointTooltip: function (element, seriesIndex, valueIndex) {
            var value = this.formatY(this.data[seriesIndex][valueIndex], seriesIndex);
            var series = this.jqplotParams.series[seriesIndex].label;

            var percentage = '';
            if (typeof this.tooltip.percentages != 'undefined') {
                percentage = this.tooltip.percentages[seriesIndex][valueIndex];
                percentage = ' (' + percentage + '%)';
            }

            var label = this.jqplotParams.axes.xaxis.labels[valueIndex];
            var text = '<strong>' + value + '</strong> ' + series + percentage;
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