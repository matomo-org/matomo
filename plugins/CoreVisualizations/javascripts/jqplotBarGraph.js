/**
 * Matomo - free/libre analytics platform
 *
 * DataTable UI class for JqplotGraph/Bar.
 *
 * @link http://www.jqplot.com
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

            var barMargin = this.data[0].length > 10 ? 2 : 10;
            var minBarWidth = 10;

            this.jqplotParams.seriesDefaults = {
                renderer: $.jqplot.BarRenderer,
                rendererOptions: {
                    shadowOffset: 1,
                    shadowDepth: 2,
                    shadowAlpha: .2,
                    fillToZero: true,
                    barMargin: barMargin
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

            var comparisonService = window.CoreHome.ComparisonsStoreInstance;
            if (comparisonService.isComparing()) {
                var seriesCount = this.jqplotParams.series.length;
                var dataCount = this.data[0].length;

                var totalBars = seriesCount * dataCount;
                var totalMinWidth = (minBarWidth + barMargin) * totalBars + 50;

                this.$element.find('.piwik-graph').css('min-width', totalMinWidth + 'px');
                this.$element.css('overflow-x', 'scroll');
                this.$element.addClass('isComparingBarViz');
            } else {
                this.$element.removeClass('isComparingBarViz');
            }
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
                percentage = ' (' + NumberFormatter.formatPercent(percentage) + ')';
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
