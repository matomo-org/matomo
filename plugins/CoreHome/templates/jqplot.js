/**
 * Piwik - Web Analytics
 *
 * Adapter for jqplot
 *
 * @link http://www.jqplot.com
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var jqPlotTooltip = false;

/**
 * Constructor function
 * @param the data that would be passed to open flash chart
 */
function JQPlot(data) {
	this.init(data);
}

JQPlot.prototype = {

	/** Generic init function */
	init: function(data) {
		this.originalData = data;
		this.params = data.params;
		this.data = data.data;
		this.tooltip = data.tooltip;
		
		this.params.grid = {
			drawGridLines: false,
			background: '#ffffff',
			borderColor: '#e7e7e7',
			borderWidth: 0,
			shadow: false
		};
		
		this.params.title = {
			show: false
		};
		
		this.params.axesDefaults = {
			pad: 1.0,
			tickRenderer: $.jqplot.CanvasAxisTickRenderer,
			tickOptions: {
				showMark: false,
				fontSize: '11px',
				fontFamily: 'Arial'
			}
		};
		
		if (typeof this.params.axes.yaxis == 'undefined') {
			this.params.axes.yaxis = {};
		}
		if (typeof this.params.axes.yaxis.tickOptions == 'undefined') {
			this.params.axes.yaxis.tickOptions = {
				formatString: '%d'
			};
		}
	},
	
	/** Generic render function */
	render: function(type, targetDivId, lang) {
		// preapare the appropriate chart type
		switch (type) {
			case 'evolution':
				this.prepareEvolutionChart(targetDivId);
				break;
			case 'bar':
				this.prepareBarChart();
				break;
			case 'pie':
				this.preparePieChart();
				break;
			default:
				return;
		}
		
		// handle replot
		// this has be bound before the check for an empty graph.
		// otherwise clicking on sparklines won't work anymore after an empty
		// report has been displayed.
		var target = $('#' + targetDivId)
		.bind('replot', function(e, data) {
			target.trigger('piwikDestroyPlot');
			if (target.data('oldHeight') > 0) {
				// handle replot after empty report
				target.height(target.data('oldHeight'));
				target.data('oldHeight', 0);
				this.innerHTML = '';
			}
			
			(new JQPlot(data)).render(type, targetDivId, lang);
		});
		
		// this case happens when there is no data for a line chart
		if (this.data.length == 0) {
			target.addClass('pk-emptyGraph');
			target.data('oldHeight', target.height());
			target.css('height', 'auto').html(lang.noData);
			return;
		}
		
		// create jqplot chart
		try {
			var plot = $.jqplot(targetDivId, this.data, this.params);
		} catch(e) {
			// this is thrown when refreshing piwik in the browser
			if (e != "No plot target specified") {
				throw e;
			}
		}
		
		// bind tooltip
		var self = this;
		target.bind('jqplotDataHighlight', function(e, s, i, d) {
			if (type == 'bar') {
				self.showBarChartTooltip(i);
			} else if (type == 'pie') {
				self.showPieChartTooltip(i);
			}
		})
		.bind('jqplotDataUnhighlight', function(e, s, i, d){
			if (type != 'evolution') {
				self.hideTooltip();
			}
		});
		
		// handle window resize
		var plotWidth = target.innerWidth();
		var timeout = false;
		var resize = function() {
			var width = target.innerWidth();
			if (width > 0 && Math.abs(plotWidth - width) >= 5) {
				plotWidth = width;
				target.trigger('piwikDestroyPlot');
				(new JQPlot(self.originalData))
						.render(type, targetDivId, lang);
			}
		};
		var resizeListener = function() {
			if (timeout) {
				window.clearTimeout(timeout);
			}
			timeout = window.setTimeout(resize, 300);
		};
		$(window).bind('resize', resizeListener);
		
		// export as image
		target.bind('piwikExportAsImage', function(e) {
			self.exportAsImage(target, lang);
		});
		
		// manage resources
		target.bind('piwikDestroyPlot', function() {
			$(window).unbind('resize', resizeListener);
			plot.destroy();
			for (var i = 0; i < $.jqplot.visiblePlots.length; i++) {
				if ($.jqplot.visiblePlots[i] == plot) {
					$.jqplot.visiblePlots[i] = null;
				}
			}
			$(this).unbind();
		});
		
		if (typeof $.jqplot.visiblePlots == 'undefined') {
			$.jqplot.visiblePlots = [];
			$('ul.nav').bind('piwikSwitchPage', function() {
				for (var i = 0; i < $.jqplot.visiblePlots.length; i++) {
					if ($.jqplot.visiblePlots[i] == null) {
						continue;
					} 
					$.jqplot.visiblePlots[i].destroy();
				}
				$.jqplot.visiblePlots = [];
			});
		}
		
		if (typeof plot != 'undefined') {
			$.jqplot.visiblePlots.push(plot);
		}
	},
	
	/** Export the chart as an image */
	exportAsImage: function(container, lang) {
		var exportCanvas = document.createElement('canvas');
		exportCanvas.width = container.width();
		exportCanvas.height = container.height();
		
		if(!exportCanvas.getContext) { alert("Sorry, not supported in your browser. Please upgrade your browser :)"); return; }
		var exportCtx = exportCanvas.getContext('2d');
		
		var canvases = container.find('canvas');
		
		for (var i=0; i < canvases.length; i++) {
			var canvas = canvases.eq(i);
			var position = canvas.position();
			var parent = canvas.parent();
			if (parent.hasClass('jqplot-axis')) {
				var addPosition = parent.position();
				position.left += addPosition.left;
				position.top += addPosition.top + parseInt(parent.css('marginTop'), 10);
			}
			exportCtx.drawImage(canvas[0], Math.round(position.left), Math.round(position.top));
		}
		
		var exported = exportCanvas.toDataURL("image/png");
		
		var img = document.createElement('img');
		img.src = exported;
		
		img = $(img).css({
			width: exportCanvas.width + 'px',
			height: exportCanvas.height + 'px'
		});
		
		$(document.createElement('div'))
		.append('<div style="font-size: 13px; margin-bottom: 10px;">'
				+ lang.exportText + '</div>').append($(img))
		.dialog({
			title: lang.exportTitle,
			modal: true,
			width: 'auto',
			position: ['center', 'center'],
			resizable: false,
			autoOpen: true,
			close: function(event, ui) {
				$(this).dialog("destroy").remove();
			}
		});
	},
	
	
	// ------------------------------------------------------------
	//  EVOLTION CHART
	// ------------------------------------------------------------
	
	prepareEvolutionChart: function(targetDivId) {
		this.params.axes.xaxis.pad = 1.0;
		this.params.axes.xaxis.renderer = $.jqplot.CategoryAxisRenderer;
		this.params.axes.xaxis.tickOptions = {
			showGridline: false
		};
		
		this.params.seriesDefaults = {
			lineWidth: 1,
			markerOptions: {
				style: "filledCircle",
				size: 6,
				shadow: false
			}
		};
		
		this.params.piwikTicks = {
			showTicks: true,
			showGrid: true,
			showHighlight: true
		};
		
		var self = this;
		var lastTick = false;
		
		$('#' + targetDivId)
		.bind('jqplotMouseLeave', function(e, s, i, d){
			self.hideTooltip();
			$(this).css('cursor', 'default');
		})
		.bind('jqplotClick', function(e, s, i, d){
			if (lastTick !== false && typeof self.params.axes.xaxis.onclick != 'undefined'
					&& typeof self.params.axes.xaxis.onclick[lastTick] == 'string') {
				var url = self.params.axes.xaxis.onclick[lastTick];
				piwikHelper.redirectToUrl(url);
			}
		})
		.bind('jqplotPiwikTickOver', function(e, tick){
			lastTick = tick;
			self.showEvolutionChartTooltip(tick);
			if (typeof self.params.axes.xaxis.onclick != 'undefined'
					&& typeof self.params.axes.xaxis.onclick[lastTick] == 'string') {
				$(this).css('cursor', 'pointer');
			}
		});
		
		this.params.legend = {
			show: false
		};
		this.params.canvasLegend = {
			show: true
		};
	},
	
	showEvolutionChartTooltip: function(i) {
		var label;
		if (typeof this.params.axes.xaxis.labels != 'undefined') {
			label = this.params.axes.xaxis.labels[i];
		} else {
			label = this.params.axes.xaxis.ticks[i];
		}
		
		var text = [];
		for (var d = 0; d < this.data.length; d++) {
			var value = this.formatY(this.data[d][i]);
			var series = this.params.series[d].label;
			text.push('<b>' + value + '</b> ' + series);
		}
		
		this.showTooltip(label, text.join('<br />'));
	},
	
	
	// ------------------------------------------------------------
	//  PIE CHART
	// ------------------------------------------------------------
	
	preparePieChart: function() {
		this.params.seriesDefaults = {
			renderer: $.jqplot.PieRenderer,
			rendererOptions: {
				shadow: false,
				showDataLabels: false,
				sliceMargin: 1,
				startAngle: 35
			}
		};
		
		this.params.piwikTicks = {
			showTicks: false,
			showGrid: false,
			showHighlight: false
		};
		
		this.params.legend = {
			show: false
		};
		this.params.pieLegend = {
			show: true
		};
		
		// pie charts have a different data format
		for (var i = 0; i < this.data[0].length; i++) {
			this.data[0][i] = [this.params.axes.xaxis.ticks[i], this.data[0][i]];
		}
	},
	
	showPieChartTooltip: function(i) {
		var value = this.formatY(this.data[0][i][1]);
		var series = this.params.series[0].label;
		var percentage = this.tooltip.percentages[0][i];
		
		var label = this.data[0][i][0];
		var text = '<b>' + percentage + '%</b> (' + value + ' ' + series + ')';
		this.showTooltip(label, text);
	},
	
	
	// ------------------------------------------------------------
	//  BAR CHART
	// ------------------------------------------------------------
	
	prepareBarChart: function() {
		this.params.seriesDefaults = {
			renderer: $.jqplot.BarRenderer,
			rendererOptions: {
				shadowOffset: 1,
				shadowDepth: 2,
				shadowAlpha: .2,
				fillToZero: true,
				barMargin: this.data[0].length > 10 ? 2 : 10
			}
		};
		
		this.params.piwikTicks = {
			showTicks: true,
			showGrid: false,
			showHighlight: false
		};
		
		this.params.axes.xaxis.renderer = $.jqplot.CategoryAxisRenderer;
		this.params.axes.xaxis.tickOptions = {
			showGridline: false
		};
		
		this.params.canvasLegend = {
			show: true
		};
	},
	
	showBarChartTooltip: function(i) {
		var value = this.formatY(this.data[0][i]);
		var series = this.params.series[0].label;
		
		var percentage = '';
		if (typeof this.tooltip.percentages != 'undefined') {
			var percentage = this.tooltip.percentages[0][i];
			percentage = ' (' + percentage + '%)'; 
		}
		
		var label = this.params.axes.xaxis.labels[i];
		var text = '<b>' + value + '</b> ' + series + percentage;
		this.showTooltip(label, text);
	},
	
	
	// ------------------------------------------------------------
	//  HELPER METHODS
	// ------------------------------------------------------------
	
	/** Get a formatted y values (with unit) */
	formatY: function(value) {
		var floatVal = parseFloat(value);
		var intVal = parseInt(value, 10);
		if (Math.abs(floatVal - intVal) >= 0.005) {
			value = Math.round(floatVal * 100) / 100;
		} else if (parseFloat(intVal) == floatVal) {
			value = intVal;
		} else {
			value = floatVal;
		}

		if (typeof this.tooltip.yUnit != 'undefined') {
			value += this.tooltip.yUnit;
		}
		
		return value;
	},
	
	/** Show the tppltip. The DOM element is created on the fly. */
	showTooltip: function(head, text) {
		if (jqPlotTooltip === false) {
			this.initTooltip();
		}
		jqPlotTooltip.html('<span class="tip-title">' + head + '</span><br />' + text);
		jqPlotTooltip.show();
	},
	
	/** Hide the tooltip */
	hideTooltip: function() {
		if (jqPlotTooltip !== false) {
			jqPlotTooltip.hide();
		}
	},
	
	/** Create and initialize the tooltip */
	initTooltip: function() {
		jqPlotTooltip = $(document.createElement('div'));
		jqPlotTooltip.addClass('jqplot-tooltip');
		$('body').prepend(jqPlotTooltip);
		
		$(document).mousemove(function(e) {
			var tipWidth = jqPlotTooltip.outerWidth();
			var maxX = $('body').innerWidth() - tipWidth - 25;
			if (e.pageX < maxX) {
				// tooltip right of mouse
				jqPlotTooltip.css({
					top: (e.pageY - 15) + "px",
					left: (e.pageX + 15) + "px"
				});
			}
			else {
				// tooltip left of mouse
				jqPlotTooltip.css({
					top: (e.pageY - 15) + "px",
					left: (e.pageX - 15 - tipWidth) + "px"
				});
			}
		});
	}
	
};





// ------------------------------------------------------------
//  PIWIK TICKS PLUGIN FOR JQPLOT
//  Handle ticks the piwik way...
// ------------------------------------------------------------

(function($) {

	$.jqplot.PiwikTicks = function(options) {
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
	
	$.jqplot.PiwikTicks.init = function(target, data, opts) {
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
	$.jqplot.PiwikTicks.postDraw = function() {
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
				drawLine(ctx, pos, full, c.showGrid);
			}
		}
	};
	
	$.jqplot.preInitHooks.push($.jqplot.PiwikTicks.init);
	$.jqplot.postDrawHooks.push($.jqplot.PiwikTicks.postDraw);
	
	// draw a 1px line
	function drawLine(ctx, x, full, showGrid) {
		ctx.save();
		ctx.strokeStyle = '#cccccc';
		
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
	
	// tigger the event jqplotPiwikTickOver when the mosue enters
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
			c.markerRenderer.draw(position[0], position[1], c.piwikHighlightCanvas._ctx);
		}
	}
	
	function unHighlight(plot) {
		var ctx = plot.plugins.piwikTicks.piwikHighlightCanvas._ctx;
		ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
	}
	
})(jQuery);



// ------------------------------------------------------------
//  LEGEND PLUGIN FOR JQPLOT
//  Render legend on canvas
// ------------------------------------------------------------

(function($) {
	
	$.jqplot.CanvasLegendRenderer = function(options) {
		// canvas for the legend
		this.legendCanvas = null;
		// render the legend?
		this.show = false;
		
		$.extend(true, this, options);
	};
	
	$.jqplot.CanvasLegendRenderer.init = function(target, data, opts) {
		// add plugin as an attribute to the plot
		var options = opts || {};
		this.plugins.canvasLegend = new $.jqplot.CanvasLegendRenderer(options.canvasLegend);
		
		// add padding above the grid
		// legend will be put there
		if (this.plugins.canvasLegend.show) {
			options.gridPadding = {
				top: 21
			};
		}
		
	};
	
	// render the legend
	$.jqplot.CanvasLegendRenderer.postDraw = function() {
		var plot = this;
		var legend = plot.plugins.canvasLegend;
		
		if (!legend.show) {
			return;
		}
		
		// initialize legend canvas
		var padding = {top: 0, right: 0, bottom: 0, left: this._gridPadding.left};
		var dimensions = {width: this._plotDimensions.width, height: this._gridPadding.top};
		
		legend.legendCanvas = new $.jqplot.GenericCanvas();
		this.eventCanvas._elem.before(legend.legendCanvas.createElement(
				padding, 'jqplot-legend-canvas', dimensions, plot));
		legend.legendCanvas.setContext();
		
		var ctx = legend.legendCanvas._ctx;
		ctx.save();
		ctx.font = '11px Arial';
		
		// render series names
		var x = 0;
		var series = plot.legend._series;
		for (i = 0; i < series.length; i++) {
			var s = series[i];
			var label;
			if (legend.labels && legend.labels[i]) {
				label = legend.labels[i];
			} else {
				label = s.label.toString(); 
			} 
			
			ctx.fillStyle = s.color;
			
			ctx.fillRect(x, 10, 10, 2);
			x += 15;
			
			ctx.fillText(label, x, 15);
			x += ctx.measureText(label).width + 20;
		}
		
		ctx.restore();
	};
	
	$.jqplot.preInitHooks.push($.jqplot.CanvasLegendRenderer.init);
	$.jqplot.postDrawHooks.push($.jqplot.CanvasLegendRenderer.postDraw);
	
})(jQuery);



// ------------------------------------------------------------
//  PIE CHART LEGEND PLUGIN FOR JQPLOT
//  Render legend inside the pie graph
// ------------------------------------------------------------

(function($) {
	
	$.jqplot.PieLegend = function(options) {
		// canvas for the legend
		this.pieLegendCanvas = null;
		// render the legend?
		this.show = false;
		
		$.extend(true, this, options);
	};
	
	$.jqplot.PieLegend.init = function(target, data, opts) {
		// add plugin as an attribute to the plot
		var options = opts || {};
		this.plugins.pieLegend = new $.jqplot.PieLegend(options.pieLegend);
	};
	
	// render the legend
	$.jqplot.PieLegend.postDraw = function() {
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
		
		ctx.font = '11px Arial';
		
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
				x = x2 + 9;
			} else {
				x = x2 - 9 - ctx.measureText(label).width;
			}
			
			ctx.fillStyle = '#666666';
			ctx.fillText(label, x, y2 + 3);
		}
		
		ctx.restore();
	};
	
	$.jqplot.preInitHooks.push($.jqplot.PieLegend.init);
	$.jqplot.postDrawHooks.push($.jqplot.PieLegend.postDraw);
	
})(jQuery);

