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

  // Clockwise angle of the x-axis labels; negative slants them up towards the bar.
  var X_AXIS_LABEL_ANGLE = -45;

  var MAX_LABEL_CHARS_PER_LINE = 14;

  // The full label stays in the hover tooltip, so bounding the axis text is lossless.
  function truncateLine(text) {
    return text.length > MAX_LABEL_CHARS_PER_LINE
      ? text.slice(0, MAX_LABEL_CHARS_PER_LINE - 1) + '…'
      : text;
  }

  // Wrap onto two lines at the space nearest the middle; the newline is honoured by
  // the renderer patched in ensureMultilineTickLabels().
  function wrapLabelToTwoLines(label) {
    if (typeof label !== 'string' || label.length <= MAX_LABEL_CHARS_PER_LINE) {
      return label;
    }

    var spaces = [];
    for (var i = 0; i < label.length; i++) {
      if (label.charAt(i) === ' ') {
        spaces.push(i);
      }
    }

    if (!spaces.length) {
      return truncateLine(label);
    }

    var middle = label.length / 2;
    var splitAt = spaces[0];
    for (var k = 1; k < spaces.length; k++) {
      if (Math.abs(spaces[k] - middle) < Math.abs(splitAt - middle)) {
        splitAt = spaces[k];
      }
    }

    return truncateLine(label.slice(0, splitAt)) + '\n' + truncateLine(label.slice(splitAt + 1));
  }

  var originalFontDraw = null;
  var renderersPatched = false;

  // Teach jqPlot's single-line canvas text renderers to honour a newline, so wrapped
  // labels render on two lines. Labels without a newline keep the original path, so
  // other charts are unaffected. A single flag guards it because
  // CanvasFontRenderer.prototype is itself a CanvasTextRenderer instance, so a
  // per-prototype marker on the base would also read as set on the font prototype.
  function ensureMultilineTickLabels() {
    if (renderersPatched || typeof $.jqplot === 'undefined' || !$.jqplot.CanvasTextRenderer) {
      return;
    }

    var base = $.jqplot.CanvasTextRenderer.prototype;
    var baseMeasure = base.measure;
    base.measure = function (ctx, str) {
      return measureWidest.call(this, baseMeasure, ctx, str);
    };

    var baseSetText = base.setText;
    base.setText = function (t, ctx) {
      baseSetText.call(this, t, ctx);
      this.height = countLines(t) * this.normalizedFontSize * this.pt2px;
      return this;
    };

    var font = $.jqplot.CanvasFontRenderer && $.jqplot.CanvasFontRenderer.prototype;
    if (font) {
      var fontMeasure = font.measure;
      font.measure = function (ctx, str) {
        return measureWidest.call(this, fontMeasure, ctx, str);
      };

      originalFontDraw = font.draw;
      font.draw = drawMultilineFont;
    }

    renderersPatched = true;
  }

  function countLines(str) {
    return String(str == null ? '' : str).split('\n').length;
  }

  // Widest line when the label spans several lines; otherwise the original measure.
  function measureWidest(originalMeasure, ctx, str) {
    str = String(str == null ? '' : str);
    if (str.indexOf('\n') === -1) {
      return originalMeasure.call(this, ctx, str);
    }

    var lines = str.split('\n');
    var widest = 0;
    for (var i = 0; i < lines.length; i++) {
      widest = Math.max(widest, originalMeasure.call(this, ctx, lines[i]));
    }
    return widest;
  }

  // Replaces CanvasFontRenderer.draw: reuses its rotation set-up, then draws each
  // line top to bottom, right-aligned so line ends stay nearest the bar.
  function drawMultilineFont(ctx, str) {
    str = String(str == null ? '' : str);
    if (str.indexOf('\n') === -1) {
      return originalFontDraw.call(this, ctx, str);
    }

    var lines = str.split('\n');
    var lineHeight = this.height / lines.length;
    var tx = 0;
    var ty = 0;

    // 1st quadrant (our negative angle falls here)
    if ((-Math.PI / 2 <= this.angle && this.angle <= 0) || (Math.PI * 3 / 2 <= this.angle && this.angle <= Math.PI * 2)) {
      tx = 0;
      ty = -Math.sin(this.angle) * this.width;
    } else if ((0 < this.angle && this.angle <= Math.PI / 2) || (-Math.PI * 2 <= this.angle && this.angle <= -Math.PI * 3 / 2)) {
      tx = Math.sin(this.angle) * this.height;
      ty = 0;
    } else if ((-Math.PI < this.angle && this.angle < -Math.PI / 2) || (Math.PI <= this.angle && this.angle <= Math.PI * 3 / 2)) {
      tx = -Math.cos(this.angle) * this.width;
      ty = -Math.sin(this.angle) * this.width - Math.cos(this.angle) * this.height;
    } else {
      tx = Math.sin(this.angle) * this.height - Math.cos(this.angle) * this.width;
      ty = -Math.cos(this.angle) * this.height;
    }

    ctx.save();
    ctx.fillStyle = this.fillStyle;
    ctx.strokeStyle = this.fillStyle;
    ctx.font = this.fontSize + ' ' + this.fontFamily;
    ctx.translate(tx, ty);
    ctx.rotate(this.angle);

    for (var i = 0; i < lines.length; i++) {
      var lineWidth = ctx.measureText(lines[i]).width;
      ctx.fillText(lines[i], this.width - lineWidth, lineHeight * i + lineHeight * 0.72);
    }

    ctx.restore();
  }

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

      ensureMultilineTickLabels();

      this.jqplotParams.axes.xaxis.renderer = $.jqplot.CategoryAxisRenderer;
      this.jqplotParams.axes.xaxis.tickOptions = {
        showGridline: false,
        angle: X_AXIS_LABEL_ANGLE
      };

      var ticks = this.jqplotParams.axes.xaxis.ticks;
      if ($.isArray(ticks)) {
        for (var t = 0; t < ticks.length; t++) {
          ticks[t] = wrapLabelToTwoLines(ticks[t]);
        }
      }

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

    // Skip the base "blank every second label when too wide" behaviour: bar labels
    // are angled and wrapped instead, so they all fit.
    _checkTicksWidth: function () {
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
      var text = '<strong>' + value + '</strong> ' + piwikHelper.htmlEntities(series) + percentage;
      $(element).tooltip({
        track: true,
        items: '*',
        content: '<h3>' + label + '</h3>' + text,
        show: false,
        hide: false
      }).trigger('mouseover');
    }
  });

})(jQuery, require);
