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
function JQPlot(data, dataTableId) {
	this.init(data, dataTableId);
}

JQPlot.prototype = {

	/** Generic init function */
	init: function(data, dataTableId) {
		this.dataTableId = dataTableId;
		this.originalData = data;
		this.params = data.params;
		this.data = data.data;
		this.tooltip = data.tooltip;
		this.seriesPicker = data.seriesPicker;
		
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
				this.prepareEvolutionChart(targetDivId, lang);
				break;
			case 'bar':
				this.prepareBarChart(targetDivId, lang);
				break;
			case 'pie':
				this.preparePieChart(targetDivId, lang);
				break;
			default:
				return;
		}
		
		// handle replot
		// this has be bound before the check for an empty graph.
		// otherwise clicking on sparklines won't work anymore after an empty
		// report has been displayed.
		var self = this;
		var target = $('#' + targetDivId)
		.on('replot', function(e, data) {
			target.trigger('piwikDestroyPlot');
			if (target.data('oldHeight') > 0) {
				// handle replot after empty report
				target.height(target.data('oldHeight'));
				target.data('oldHeight', 0);
				this.innerHTML = '';
			}

			(new JQPlot(data, self.dataTableId)).render(type, targetDivId, lang);
		});
		
		// show loading
		target.bind('showLoading', function() {
			var loading = $(document.createElement('div')).addClass('jqplot-loading');
			loading.css({
				width: target.innerWidth()+'px',
				height: target.innerHeight()+'px',
				opacity: 0
			});
			target.prepend(loading);
			loading.css({opacity: .7});
		});
		
		// change series
		target.bind('changeColumns', function(e, columns) {
			target.trigger('changeSeries', [columns, []]);
		});
		target.bind('changeSeries', function(e, columns, rows) {
			target.trigger('showLoading');
			if (typeof columns == 'string') {
				columns = columns.split(',');
			}
			if (typeof rows == 'undefined') {
				rows = [];
			}
			else if (typeof rows == 'string') {
				rows = rows.split(',');
			}
			var dataTable = dataTables[self.dataTableId];
			dataTable.param.columns = columns.join(',');
			dataTable.param.rows = rows.join(',');
			delete dataTable.param.filter_limit;
			delete dataTable.param.totalRows;
			if( dataTable.param.filter_sort_column != 'label' ) {
				dataTable.param.filter_sort_column = columns[0];
			}
			dataTable.param.disable_generic_filters = '0';
			dataTable.reloadAjaxDataTable(false);
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
		target.on('jqplotDataHighlight', function(e, s, i, d) {
			if (type == 'bar') {
				self.showBarChartTooltip(s, i);
			} else if (type == 'pie') {
				self.showPieChartTooltip(i);
			}
		})
		.on('jqplotDataUnhighlight', function(e, s, i, d){
			if (type != 'evolution') {
				self.hideTooltip();
			}
		});
		
		// handle window resize
		var plotWidth = target.innerWidth();
		var timeout = false;
		target.on('resizeGraph', function() {
			var width = target.innerWidth();
			if (width > 0 && Math.abs(plotWidth - width) >= 5) {
				plotWidth = width;
				target.trigger('piwikDestroyPlot');
				(new JQPlot(self.originalData, self.dataTableId))
						.render(type, targetDivId, lang);
			}
		});
		var resizeListener = function() {
			if (timeout) {
				window.clearTimeout(timeout);
			}
			timeout = window.setTimeout(function() {
				target.trigger('resizeGraph');
			}, 300);
		};
		$(window).on('resize', resizeListener);
		
		// export as image
		target.on('piwikExportAsImage', function(e) {
			self.exportAsImage(target, lang);
		});
		
		// manage resources
		target.on('piwikDestroyPlot', function() {
			$(window).off('resize', resizeListener);
			plot.destroy();
			for (var i = 0; i < $.jqplot.visiblePlots.length; i++) {
				if ($.jqplot.visiblePlots[i] == plot) {
					$.jqplot.visiblePlots[i] = null;
				}
			}
			$(this).off();
		});
		
		if (typeof $.jqplot.visiblePlots == 'undefined') {
			$.jqplot.visiblePlots = [];
			$('ul.nav').on('piwikSwitchPage', function() {
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
	
	prepareEvolutionChart: function(targetDivId, lang) {
		this.setYTicks();
		this.addSeriesPicker(targetDivId, lang);

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
		.on('jqplotMouseLeave', function(e, s, i, d){
			self.hideTooltip();
			$(this).css('cursor', 'default');
		})
		.on('jqplotClick', function(e, s, i, d){
			if (lastTick !== false && typeof self.params.axes.xaxis.onclick != 'undefined'
					&& typeof self.params.axes.xaxis.onclick[lastTick] == 'string') {
				var url = self.params.axes.xaxis.onclick[lastTick];
				piwikHelper.redirectToUrl(url);
			}
		})
		.on('jqplotPiwikTickOver', function(e, tick){
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
			var value = this.formatY(this.data[d][i], d);
			var series = this.params.series[d].label;
			text.push('<b>' + value + '</b> ' + series);
		}
		
		this.showTooltip(label, text.join('<br />'));
	},
	
	
	// ------------------------------------------------------------
	//  PIE CHART
	// ------------------------------------------------------------
	
	preparePieChart: function(targetDivId, lang) {
		this.addSeriesPicker(targetDivId, lang);
		
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
		this.params.canvasLegend = {
			show: true,
			singleMetric: true
		};
		
		// pie charts have a different data format
		if (!(this.data[0][0] instanceof Array)) { // check if already in different format
			for (var i = 0; i < this.data[0].length; i++) {
				this.data[0][i] = [this.params.axes.xaxis.ticks[i], this.data[0][i]];
			}
		}
	},
	
	showPieChartTooltip: function(i) {
		var value = this.formatY(this.data[0][i][1], 1); // series index 1 because 0 is the label
		var series = this.params.series[0].label;
		var percentage = this.tooltip.percentages[0][i];
		
		var label = this.data[0][i][0];
		
		var text = '<b>' + percentage + '%</b> (' + value + ' ' + series + ')';
		this.showTooltip(label, text);
	},
	
	
	// ------------------------------------------------------------
	//  BAR CHART
	// ------------------------------------------------------------
	
	prepareBarChart: function(targetDivId, lang) {
		this.setYTicks();
		this.addSeriesPicker(targetDivId, lang);
				
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
	
	showBarChartTooltip: function(s, i) {
		var value = this.formatY(this.data[s][i], s);
		var series = this.params.series[s].label;
		
		var percentage = '';
		if (typeof this.tooltip.percentages != 'undefined') {
			var percentage = this.tooltip.percentages[s][i];
			percentage = ' (' + percentage + '%)'; 
		}
		
		var label = this.params.axes.xaxis.labels[i];
		var text = '<b>' + value + '</b> ' + series + percentage;
		this.showTooltip(label, text);
	},
	
	
	// ------------------------------------------------------------
	//  HELPER METHODS
	// ------------------------------------------------------------
	
	/** Generate ticks in y direction */
	setYTicks: function() {
		// default axis
		this.setYTicksForAxis('yaxis', this.params.axes.yaxis);
		// other axes: y2axis, y3axis...
		for (var i = 2; typeof this.params.axes['y'+i+'axis'] != 'undefined'; i++) {
			this.setYTicksForAxis('y'+i+'axis', this.params.axes['y'+i+'axis']);
		}
	},
	
	setYTicksForAxis: function(axisName, axis) {
		// calculate maximum x value of all data sets
		var maxCrossDataSets = 0;
		for (var i = 0; i < this.data.length; i++) {
			if (this.params.series[i].yaxis == axisName) {
				maxValue = Math.max.apply(Math, this.data[i]);
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
		if (axis.tickOptions.formatString.substring(2, 3) == '%' && maxCrossDataSets > 100) {
			maxCrossDataSets = 100;
		}

		// calculate y-values for ticks
		ticks = [];
		numberOfTicks = 2;
		tickDistance = Math.ceil(maxCrossDataSets / numberOfTicks);
		for (var i = 0; i <= numberOfTicks; i++) {
			ticks.push(i * tickDistance);
		}
		axis.ticks = ticks;
	},
	
	/** Get a formatted y values (with unit) */
	formatY: function(value, seriesIndex) {
		var floatVal = parseFloat(value);
		var intVal = parseInt(value, 10);
		if (Math.abs(floatVal - intVal) >= 0.005) {
			value = Math.round(floatVal * 100) / 100;
		} else if (parseFloat(intVal) == floatVal) {
			value = intVal;
		} else {
			value = floatVal;
		}
		if (typeof this.tooltip.yUnits[seriesIndex] != 'undefined') {
			value += this.tooltip.yUnits[seriesIndex];
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
	},
	
	addSeriesPicker: function(targetDivId, lang) {
		this.params.seriesPicker = {
			show: typeof this.seriesPicker.selectableColumns == 'object'
					|| typeof this.seriesPicker.selectableRows == 'object',
			selectableColumns: this.seriesPicker.selectableColumns,
			selectableRows: this.seriesPicker.selectableRows,
			multiSelect: this.seriesPicker.multiSelect,
			targetDivId: targetDivId,
			dataTableId: this.dataTableId,
			lang: lang
		};
	},

	/**
	 * Add an external series toggle.
	 * As opposed to addSeriesPicker, the external series toggle can only show/hide
	 * series that are already loaded.
	 * @param seriesPickerClass a subclass of JQPlotExternalSeriesToggle
	 */
	addExternalSeriesToggle: function(seriesPickerClass, targetDivId, initiallyShowAll) {
		new seriesPickerClass(targetDivId, this.originalData, initiallyShowAll);
		
		if (!initiallyShowAll) {
			// initially, show only the first series
			this.data = [this.data[0]];
			this.params.series = [this.params.series[0]];
		}
	}
	
};




// ----------------------------------------------------------------
//  EXTERNAL SERIES TOGGLE
//  Use external dom elements and their events to show/hide series
// ----------------------------------------------------------------

function JQPlotExternalSeriesToggle(targetDivId, originalConfig, initiallyShowAll) {
	this.init(targetDivId, originalConfig, initiallyShowAll);
}

JQPlotExternalSeriesToggle.prototype = {
	
	init: function(targetDivId, originalConfig, initiallyShowAll) {
		this.targetDivId = targetDivId;
		this.originalConfig = originalConfig;
		this.originalData = originalConfig.data;
		this.originalSeries = originalConfig.params.series;
		this.originalAxes = originalConfig.params.axes;
		this.originalTooltipUnits = originalConfig.tooltip.yUnits;
		this.originalSeriesColors = originalConfig.params.seriesColors;
		this.initiallyShowAll = initiallyShowAll;
		
		this.activated = [];
		this.target = $('#'+targetDivId);
		
		this.attachEvents();
	},
	
	// can be overridden
	attachEvents: function() {},
	
	// show a single series
	showSeries: function(i) {
		for (var j = 0; j < this.activated.length; j++) {
			this.activated[j] = (i == j);
		}
		this.replot();
	},
	
	// toggle a series (make plotting multiple series possible)
	toggleSeries: function(i) {
		var activatedCount = 0;
		for (var k = 0; k < this.activated.length; k++) {
			if (this.activated[k]) {
				activatedCount++;
			}
		}
		if (activatedCount == 1 && this.activated[i]) {
			// prevent removing the only visible metric
			return;
		}
		
		this.activated[i] = !this.activated[i];
		this.replot();
	},
	
	replot: function() {
		this.beforeReplot();
		
		// build new config and replot
		var usedAxes = [];
		var config = this.originalConfig;
		config.data = [];
		config.params.series = [];
		config.params.axes = {xaxis: this.originalAxes.xaxis};
		config.tooltip.yUnits = [];
		config.params.seriesColors = [];
		for (var j = 0; j < this.activated.length; j++) {
			if (!this.activated[j]) {
				continue;
			}
			config.data.push(this.originalData[j]);
			config.tooltip.yUnits.push(this.originalTooltipUnits[j]);
			config.params.seriesColors.push(this.originalSeriesColors[j]);
			config.params.series.push($.extend(true, {}, this.originalSeries[j]));
			// build array of used axes
			var axis = this.originalSeries[j].yaxis;
			if ($.inArray(axis, usedAxes) == -1) {
				usedAxes.push(axis);
			}
		}
		
		// build new axes config
		var replaceAxes = {};
		for (j = 0; j < usedAxes.length; j++) {
			var originalAxisName = usedAxes[j];
			var newAxisName = (j == 0 ? 'yaxis' : 'y' + (j+1) + 'axis');
			replaceAxes[originalAxisName] = newAxisName;
			config.params.axes[newAxisName] = this.originalAxes[originalAxisName];
		}
		
		// replace axis names in series config
		for (j = 0; j < config.params.series.length; j++) {
			var series = config.params.series[j];
			series.yaxis = replaceAxes[series.yaxis];
		}
		
		this.target.trigger('replot', config);
	},
	
	// can be overridden
	beforeReplot: function() {}

};


// ROW EVOLUTION SERIES TOGGLE

function RowEvolutionSeriesToggle(targetDivId, originalConfig, initiallyShowAll) {
	this.init(targetDivId, originalConfig, initiallyShowAll);
}

RowEvolutionSeriesToggle.prototype = JQPlotExternalSeriesToggle.prototype;

RowEvolutionSeriesToggle.prototype.attachEvents = function() {
	var self = this;
	this.seriesPickers = this.target.closest('.rowevolution').find('table.metrics tr');
	
	this.seriesPickers.each(function(i) {
		var el = $(this);
		el.click(function(e) {
			if (e.shiftKey) {
				self.toggleSeries(i);
			} else {
				self.showSeries(i);
			}
			return false;
		});
		
		if (i == 0 || self.initiallyShowAll) {
			// show the active series
			// if initiallyShowAll, all are active; otherwise only the first one
			self.activated.push(true);
		} else {
			// fade out the others
			el.find('td').css('opacity', .5);
			self.activated.push(false);
		}
		
		// prevent selecting in ie & opera (they don't support doing this via css)
		if ($.browser.msie) {
			this.ondrag = function() { return false; };
			this.onselectstart = function() { return false; };
		} else if ($.browser.opera) {
			$(this).attr('unselectable', 'on');
		}
	});
};

RowEvolutionSeriesToggle.prototype.beforeReplot = function() {
	// fade out if not activated
	for (var i = 0; i < this.activated.length; i++) {
		if (this.activated[i]) {
			this.seriesPickers.eq(i).find('td').css('opacity', 1);
		} else {
			this.seriesPickers.eq(i).find('td').css('opacity', .5);
		}
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

(function($) {
	
	$.jqplot.CanvasLegendRenderer = function(options) {
		// canvas for the legend
		this.legendCanvas = null;
		// is it a legend for a single metric only (pie chart)?
		this.singleMetric = false;
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
		var padding = {top: 0, right: this._gridPadding.right, bottom: 0, left: this._gridPadding.left};
		var dimensions = {width: this._plotDimensions.width, height: this._gridPadding.top};
		var width = this._plotDimensions.width - this._gridPadding.left - this._gridPadding.right;
		
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
			if (legend.singleMetric)
			{
				ctx.fillStyle = '#666666';
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
//  For line charts
// ------------------------------------------------------------

(function($) {
	
	$.jqplot.SeriesPicker = function(options) {
		// dom element
		this.domElem = null;
		// render the picker?
		this.show = false;
		// the columns that can be selected
		this.selectableColumns = null;
		// the rows that can be selected
		this.selectableRows = null;
		// can multiple rows we selected?
		this.multiSelect = true;
		// css id of the target div dom element
		this.targetDivId = "";
		// the id of the current data table (index for global dataTables)
		this.dataTableId = "";
		// language strings
		this.lang = {};
		
		$.extend(true, this, options);
	};
	
	$.jqplot.SeriesPicker.init = function(target, data, opts) {
		// add plugin as an attribute to the plot
		var options = opts || {};
		this.plugins.seriesPicker = new $.jqplot.SeriesPicker(options.seriesPicker);
	};
	
	// render the link to add series
	$.jqplot.SeriesPicker.postDraw = function() {
		var plot = this;
		var picker = plot.plugins.seriesPicker;
		
		if (!picker.show) {
			return;
		}
		
		// initialize dom element
		picker.domElem = $(document.createElement('a'))
			.addClass('jqplot-seriespicker')
			.attr('href', '#').html('+')
			.css('marginLeft', (plot._gridPadding.left + plot.plugins.canvasLegend.width - 1) + 'px');
		
		picker.domElem.on('hide', function() {
			$(this).css('opacity', .55);	
		}).trigger('hide');
		
		plot.baseCanvas._elem.before(picker.domElem);
		
		// show picker on hover
		picker.domElem.hover(function() {
			picker.domElem.css('opacity', 1);
			if (!picker.domElem.hasClass('open')) {
				picker.domElem.addClass('open');
				showPicker(picker, plot._width);
			}
		}, function() {
			// do nothing on mouseout because using this event doesn't work properly.
			// instead, the timeout check beneath is used (checkPickerLeave()).
		}).click(function() {
			return false;	
		});
	};
	
	// show the series picker
	function showPicker(picker, plotWidth) {
		var pickerLink = picker.domElem;
		var pickerPopover = $(document.createElement('div'))
			.addClass('jqplock-seriespicker-popover');
		
		var pickerState = {manipulated: false};
		
		// headline
		var title = picker.multiSelect ? picker.lang.metricsToPlot : picker.lang.metricToPlot;
		pickerPopover.append($(document.createElement('p'))
			.addClass('headline').html(title));
		
		if (picker.selectableColumns !== null) {
			// render the selectable columns
			for (var i = 0; i < picker.selectableColumns.length; i++) {
				var column = picker.selectableColumns[i];
				pickerPopover.append(createPickerPopupItem(picker, column, 'column', pickerState, pickerPopover, pickerLink));
			}
		}
		
		if (picker.selectableRows !== null) {
			// "records to plot" subheadline
			pickerPopover.append($(document.createElement('p'))
				.addClass('headline').addClass('recordsToPlot')
				.html(picker.lang.recordsToPlot));
			
			// render the selectable rows
			for (var i = 0; i < picker.selectableRows.length; i++) {
				var row = picker.selectableRows[i];
				pickerPopover.append(createPickerPopupItem(picker, row, 'row', pickerState, pickerPopover, pickerLink));
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
				.css('marginLeft', (linkOffset.left  - neededSpace + 38) + 'px')
				.css('backgroundPosition', (pickerPopover.outerWidth() - 25) + 'px 4px')
				.show();
		}
		pickerPopover.css('marginTop', (linkOffset.top - 5) + 'px').show();
		
		// hide and replot on mouse leave
		checkPickerLeave(pickerPopover, function() {
			var replot = pickerState.manipulated;
			hidePicker(picker, pickerPopover, pickerLink, replot);
		});
	}
	
	function createPickerPopupItem(picker, config, type, pickerState, pickerPopover, pickerLink) {
		var checkbox = $(document.createElement('input')).addClass('select')
				.attr('type', picker.multiSelect ? 'checkbox' : 'radio');

		if (config.displayed && !(!picker.multiSelect && pickerState.oneChecked)) {
			checkbox.prop('checked', true);
			pickerState.oneChecked = true;
		}
		
		// if we are rendering a column, remember the column name
		// if it's a row, remember the string that can be used to match the row
		checkbox.data('name', type == 'column' ? config.column : config.matcher);
		
		var el = $(document.createElement('p'))
			.append(checkbox)
			.append(type == 'column' ? config.translation : config.label)
			.addClass(type == 'column' ? 'pickColumn' : 'pickRow');
		
		var replot = function() {
			unbindPickerLeaveCheck();
			hidePicker(picker, pickerPopover, pickerLink, true);
		};
		
		var checkBox = function(box) {
			if (!picker.multiSelect) {
				pickerPopover.find('input.select:not(.current)').prop('checked', false);
			}
			box.prop('checked', true);
			replot();
		};
		
		el.click(function(e) {
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
	}
	
	// check whether the mouse has left the picker
	var onMouseMove;
	function checkPickerLeave(pickerPopover, onLeaveCallback) {
		var offset = pickerPopover.offset();
		var minX = offset.left;
		var minY = offset.top;
		var maxX = minX + pickerPopover.outerWidth();
		var maxY = minY + pickerPopover.outerHeight();
		var currentX, currentY;
		onMouseMove = function(e) {
			currentX = e.pageX;
			currentY = e.pageY;
			if (currentX < minX || currentX > maxX
					|| currentY < minY || currentY > maxY) {
				unbindPickerLeaveCheck();
				onLeaveCallback();
			}
		};
		$(document).mousemove(onMouseMove);
	}
	function unbindPickerLeaveCheck() {
		$(document).unbind('mousemove', onMouseMove);
	}
	
	function hidePicker(picker, pickerPopover, pickerLink, replot) {
		// hide picker
		pickerPopover.hide();
		pickerLink.trigger('hide').removeClass('open');
		
		// replot
		if (replot) {
			var columns = [];
			var rows = [];
			pickerPopover.find('input:checked').each(function() {
				if ($(this).closest('p').hasClass('pickRow')) {
					rows.push($(this).data('name'));
				} else {
					columns.push($(this).data('name'));
				}
			});
			var noRowSelected = pickerPopover.find('.pickRow').size() > 0
					&& pickerPopover.find('.pickRow input:checked').size() == 0;
			if (columns.length > 0 && !noRowSelected) {

				$('#'+picker.targetDivId).trigger('changeSeries', [columns, rows]);
				// inform dashboard widget about changed parameters (to be restored on reload)
				$('#'+picker.targetDivId).parents('[widgetId]').trigger('setParameters', {columns: columns, rows: rows});
			}
		}
		
		pickerPopover.remove();
	}
	
	$.jqplot.preInitHooks.push($.jqplot.SeriesPicker.init);
	$.jqplot.postDrawHooks.push($.jqplot.SeriesPicker.postDraw);
	
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
