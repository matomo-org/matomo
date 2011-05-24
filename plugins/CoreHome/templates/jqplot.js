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
		this.elements = data.elements;
		this.element = data.elements[0];
		this.values = [];
		if (typeof this.element != 'undefined') {
			this.values = this.element.values;
		}
		
		this.data = [];
		this.params = {
			grid: {
				drawGridLines: false,
				background: '#ffffff',
				borderColor: '#e7e7e7',
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
					fontFamily: 'Arial'
				}
			}
		};
	},
	
	/** Generic render function */
	render: function(type, targetDivId, lang) {
		// preapare the appropriate chart type
		switch (type) {
			case 'graphEvolution':
				this.prepareEvolutionChart(targetDivId);
				break;
			case 'graphVerticalBar':
				this.prepareBarChart();
				break;
			case 'graphPie':
				this.preparePieChart();
				break;
			default:
				return;
		}
		
		// this case happens when there is no data for a line chart
		// TODO: this should already be noticed by php and the chart should
		// not even be generated
		if (this.data.length == 0) {
			$('#' + targetDivId).addClass('pk-emptyGraph').css('height', 'auto').html(lang.noData);
			return;
		}
		
		// create jqplot chart
		if (typeof this.data[0] != 'object') {
			this.data = [this.data];
		}
		
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
		
		var target = $('#' + targetDivId)
		.bind('jqplotDataHighlight', function(e, s, i, d) {
			var tip = self.prepareTooltip(self.values[i].tip);
			self.showTooltip(tip);
		})
		.bind('jqplotDataUnhighlight', function(e, s, i, d){
			self.hideTooltip();
		});
		
		// handle replot
		target.bind('replot', function(e, data) {
			$(this).trigger('piwikDestroyPlot');
			(new JQPlot(data)).render(type, targetDivId, lang);
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
		var xticks = this.originalData['x_axis'].labels.labels;
		for (var i = 0; i < xticks.length; i++) {
			if (xticks[i] == '') {
				xticks[i] = ' ';
			}
		}
		
		this.params.axes = {
			yaxis: this.getYAxis(),
			xaxis: {
				pad: 1.0,
				ticks: xticks,
				renderer: $.jqplot.CategoryAxisRenderer,
				tickOptions: {
					showGridline: false
				}
			}
		};
		
		this.params.seriesDefaults = {
			lineWidth: 1,
			markerOptions: {
				style: "filledCircle",
				size: 6,
				shadow: false
			}
		};
		
		var labels = [];
		this.params.series = [];
		for (i = 0; i < this.elements.length; i++) {
			var color = this.elements[i].colour.substring(2, 8);
			this.params.series.push({
				color: this.manipulateColor(color) 
			});
			labels.push(this.elements[i].text);
		}
		
		this.params.piwikTicks = {
			showTicks: true,
			showGrid: true,
			showHighlight: true
		};
		
		var self = this;
		var lastTick = false;
		
		$('#' + targetDivId)
		.bind('jqplotMouseLeave', function(e, s, i, d){
			self.hideTooltip(true);
		})
		.bind('jqplotClick', function(e, s, i, d){
			if (lastTick !== false) {
				if (typeof self.values[lastTick]['on-click'] == 'string') {
					eval(self.values[lastTick]['on-click']);
				}
			}
		})
		.bind('jqplotPiwikTickOver', function(e, tick, yFormatter){
			lastTick = tick;
			self.showEvolutionChartTooltip(tick, yFormatter);
		});
		
		this.params.legend = {
			show: false
		};
		this.params.canvasLegend = {
			show: true,
			labels: labels
		};
		
		for (var s = 0; s < this.elements.length; s++) {
			this.data[s] = [];
			for (var i = 0; i < this.values.length; i++) {
				this.data[s].push(this.elements[s].values[i].value);
			}
		}
	},
	
	showEvolutionChartTooltip: function(i, yFormatter) {
		var head = this.prepareTooltip(this.values[i].tip);
		head = head.substr(0, head.indexOf('<br>'));
		
		var values = [];
		for (var s = 0; s < this.elements.length; s++) {
			var element = this.elements[s];
			values.push('<b>' + yFormatter(element.values[i].value) + '</b> ' + element.text);
		}
		
		var html = head + '<br />' + values.join('<br />');
		this.showTooltip(html, true);
	},
	
	
	// ------------------------------------------------------------
	//  PIE CHART
	// ------------------------------------------------------------
	
	preparePieChart: function() {
		this.params.seriesColors = [];
		for (var i = 0; i < this.element.colours.length; i++) {
			this.params.seriesColors.push(
					this.manipulateColor(this.element.colours[i]));
		}
		
		this.params.seriesDefaults = {
			renderer: $.jqplot.PieRenderer,
			rendererOptions: {
				shadow: false,
				showDataLabels: false,
				sliceMargin: 1,
				startAngle: this.element['start-angle']
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
		
		for (var i = 0; i < this.values.length; i++) {
			var value = this.values[i];
			this.data.push([value.label, value.value]);
		}
		this.data = [this.data];
	},
	
	
	// ------------------------------------------------------------
	//  BAR CHART
	// ------------------------------------------------------------
	
	prepareBarChart: function() {
		this.params.seriesColors = [];
		var color = this.manipulateColor(this.element.colour);
		for (var i = 0; i < this.values.length; i++) {
			this.params.seriesColors.push(color);
		}
		
		this.params.seriesDefaults = {
			renderer: $.jqplot.BarRenderer,
			rendererOptions: {
				shadowOffset: 1,
				shadowDepth: 2,
				shadowAlpha: .2,
				fillToZero: true,
				barMargin: this.values.length > 10 ? 2 : 10
			}
		};
		
		this.params.piwikTicks = {
			showTicks: true,
			showGrid: false,
			showHighlight: false
		};
		
		var ticks = this.originalData.x_axis.labels.labels;
		for (var t = 0; t < ticks.length; t++) {
			if (ticks[t] == '') {
				ticks[t] = ' ';
			}
		}
		
		this.params.axes = {
			xaxis: {
				renderer: $.jqplot.CategoryAxisRenderer,
				ticks: ticks,
				tickOptions: {
					showGridline: false
				}
			},
			yaxis: this.getYAxis()
		};
		
		for (var i = 0; i < this.values.length; i++) {
			this.data.push(this.values[i].top);
		}
		
		this.params.canvasLegend = {
			show: true,
			labels: [this.element.text]
		};
	},
	
	
	// ------------------------------------------------------------
	//  HELPER METHODS
	// ------------------------------------------------------------
	
	/** Derive y axis and its ticks from configuration */
	getYAxis: function() {
		var config = {};
		
		var yaxis = this.originalData['y_axis'];
		if (yaxis.steps > 0) {
			var yticks = [];
			for (var y = yaxis.min; y < yaxis.max + yaxis.steps; y += yaxis.steps) {
				yticks.push(y);
			}
			config.ticks = yticks;
		}
		
		config.tickOptions = {
			formatString: '%d'
		};
		
		if (typeof this.originalData.y_axis.labels != 'undefined' &&
				typeof this.originalData.y_axis.labels.text == 'string') {
			var format = this.originalData['y_axis'].labels.text;
			format = format.replace('#val#', '%s');
			config.tickOptions.formatString = format;
		}
		
		return config;
	},
	
	/** Lighten colors */
	manipulateColor: function(color) {
		var rgb = $.jqplot.getColorComponents(color);
		for (var i = 0; i <= 2; i++) {
			rgb[i] += 0.15 * (255 - rgb[i]);
			rgb[i] = parseInt(rgb[i], 10);
		}
		return 'rgb('+rgb[0]+','+rgb[1]+','+rgb[2]+')';
	},
	
	/** Prepare tooltip html */
	prepareTooltip: function(tip) {
		return '<span class="tip-title">' + tip.replace('<br', '</span><br');
	},
	
	/** Show the tppltip. The DOM element is created on the fly. */
	showTooltip: function(html, force) {
		if (jqPlotTooltip === false) {
			this.initTooltip();
		}
		
		jqPlotTooltip.forced = force;
		jqPlotTooltip.html(html);
		jqPlotTooltip.stop(true, true).fadeIn(250);
	},
	
	/** Hide the tooltip */
	hideTooltip: function(force) {
		if (jqPlotTooltip !== false && (!jqPlotTooltip.forced || force)) {
			if (force) {
				jqPlotTooltip.hide();
			}
			else {
				jqPlotTooltip.stop(true, true).fadeOut(400);
			}
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
			
			if (typeof plot.axes.yaxis._ticks[0] == 'undefined') {
				// needed for pie charts
				var yFormatter = function(val){
					return val;
				};
			}
			else {
				var yf = plot.axes.yaxis._ticks[0].formatter;
				var yfstr = plot.axes.yaxis._ticks[0].formatString;
				var yFormatter = function(val){
					return yf(yfstr, val);
				}
			}
			
			plot.target.trigger('jqplotPiwikTickOver', [tick, yFormatter]);
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
			var label = legend.labels[i] || s.label.toString();
			
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
		var x1, x2, y1, y2, lastY2 = false, right, lastRight = false;
		for (i = 0; i < labels.length; i++) {
			var label = labels[i];
			
			ctx.strokeStyle = colors[i % colors.length];
			ctx.lineCap = 'round';
			ctx.lineWidth = 1;
			
			// concentric line
			ctx.beginPath();
			
			x1 = center[0] + Math.sin(lineAngles[i]) * (radius);
			y1 = center[1] - Math.cos(lineAngles[i]) * (radius);
			ctx.moveTo(x1, y1);
			
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
				x = x2 - 9 - ctx.measureText(labels[i]).width;
			}
			
			ctx.fillStyle = '#666666';
			ctx.fillText(label, x, y2 + 3);
		}
		
		ctx.restore();
	};
	
	$.jqplot.preInitHooks.push($.jqplot.PieLegend.init);
	$.jqplot.postDrawHooks.push($.jqplot.PieLegend.postDraw);
	
})(jQuery);

