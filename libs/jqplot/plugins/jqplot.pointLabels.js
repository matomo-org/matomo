/**
 * jqPlot
 * Pure JavaScript plotting plugin using jQuery
 *
 * Version: @VERSION
 *
 * Copyright (c) 2009-2011 Chris Leonello
 * jqPlot is currently available for use in all personal or commercial projects 
 * under both the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL 
 * version 2.0 (http://www.gnu.org/licenses/gpl-2.0.html) licenses. This means that you can 
 * choose the license that best suits your project and use it accordingly. 
 *
 * Although not required, the author would appreciate an email letting him 
 * know of any substantial use of jqPlot.  You can reach the author at: 
 * chris at jqplot dot com or see http://www.jqplot.com/info.php .
 *
 * If you are feeling kind and generous, consider supporting the project by
 * making a donation at: http://www.jqplot.com/donate.php .
 *
 * sprintf functions contained in jqplot.sprintf.js by Ash Searle:
 *
 *     version 2007.04.27
 *     author Ash Searle
 *     http://hexmen.com/blog/2007/03/printf-sprintf/
 *     http://hexmen.com/js/sprintf.js
 *     The author (Ash Searle) has placed this code in the public domain:
 *     "This code is unrestricted: you are free to use it however you like."
 * 
 */
(function($) {
    
    /**
     * Class: $.jqplot.PointLabels
     * Plugin for putting labels at the data points.
     * 
     * To use this plugin, include the js
     * file in your source:
     * 
     * > <script type="text/javascript" src="plugins/jqplot.pointLabels.js"></script>
     * 
     * By default, the last value in the data ponit array in the data series is used
     * for the label.  For most series renderers, extra data can be added to the 
     * data point arrays and the last value will be used as the label.
     * 
     * For instance, 
     * this series:
     * 
     * > [[1,4], [3,5], [7,2]]
     * 
     * Would, by default, use the y values in the labels.
     * Extra data can be added to the series like so:
     * 
     * > [[1,4,'mid'], [3 5,'hi'], [7,2,'low']]
     * 
     * And now the point labels would be 'mid', 'low', and 'hi'.
     * 
     * Options to the point labels and a custom labels array can be passed into the
     * "pointLabels" option on the series option like so:
     * 
     * > series:[{pointLabels:{
     * >    labels:['mid', 'hi', 'low'],
     * >    location:'se',
     * >    ypadding: 12
     * >    }
     * > }]
     * 
     * A custom labels array in the options takes precendence over any labels
     * in the series data.  If you have a custom labels array in the options,
     * but still want to use values from the series array as labels, set the
     * "labelsFromSeries" option to true.
     * 
     * By default, html entities (<, >, etc.) are escaped in point labels.  
     * If you want to include actual html markup in the labels, 
     * set the "escapeHTML" option to false.
     * 
     */
    $.jqplot.PointLabels = function(options) {
        // Group: Properties
        //
        // prop: show
        // show the labels or not.
        this.show = $.jqplot.config.enablePlugins;
        // prop: location
        // compass location where to position the label around the point.
        // 'n', 'ne', 'e', 'se', 's', 'sw', 'w', 'nw'
        this.location = 'n';
        // prop: labelsFromSeries
        // true to use labels within data point arrays.
        this.labelsFromSeries = false;
        // prop: seriesLabelIndex
        // array index for location of labels within data point arrays.
        // if null, will use the last element of the data point array.
        this.seriesLabelIndex = null;
        // prop: labels
        // array of arrays of labels, one array for each series.
        this.labels = [];
        // actual labels that will get displayed.
        // needed to preserve user specified labels in labels array.
        this._labels = [];
        // prop: stackedValue
        // true to display value as stacked in a stacked plot.
        // no effect if labels is specified.
        this.stackedValue = false;
        // prop: ypadding
        // vertical padding in pixels between point and label
        this.ypadding = 6;
        // prop: xpadding
        // horizontal padding in pixels between point and label
        this.xpadding = 6;
        // prop: escapeHTML
        // true to escape html entities in the labels.
        // If you want to include markup in the labels, set to false.
        this.escapeHTML = true;
        // prop: edgeTolerance
        // Number of pixels that the label must be away from an axis
        // boundary in order to be drawn.  Negative values will allow overlap
        // with the grid boundaries.
        this.edgeTolerance = -5;
        // prop: formatter
        // A class of a formatter for the tick text.  sprintf by default.
        this.formatter = $.jqplot.DefaultTickFormatter;
        // prop: formatString
        // string passed to the formatter.
        this.formatString = '';
        // prop: hideZeros
        // true to not show a label for a value which is 0.
        this.hideZeros = false;
        this._elems = [];
        
        $.extend(true, this, options);
    };
    
    var locations = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'];
    var locationIndicies = {'nw':0, 'n':1, 'ne':2, 'e':3, 'se':4, 's':5, 'sw':6, 'w':7};
    var oppositeLocations = ['se', 's', 'sw', 'w', 'nw', 'n', 'ne', 'e'];
    
    // called with scope of a series
    $.jqplot.PointLabels.init = function (target, data, seriesDefaults, opts){
        var options = $.extend(true, {}, seriesDefaults, opts);
        options.pointLabels = options.pointLabels || {};
        if (this.renderer.constructor == $.jqplot.BarRenderer && this.barDirection == 'horizontal' && !options.pointLabels.location) {
            options.pointLabels.location = 'e';
        }
        // add a pointLabels attribute to the series plugins
        this.plugins.pointLabels = new $.jqplot.PointLabels(options.pointLabels);
        this.plugins.pointLabels.setLabels.call(this);
    };
    
    // called with scope of series
    $.jqplot.PointLabels.prototype.setLabels = function() {   
        var p = this.plugins.pointLabels; 
        var labelIdx;
        if (p.seriesLabelIndex != null) {
            labelIdx = p.seriesLabelIndex;
        }
        else if (this.renderer.constructor == $.jqplot.BarRenderer && this.barDirection == 'horizontal') {
            labelIdx = 0;
        }
        else {
            labelIdx = this._plotData[0].length -1;
        }
        p._labels = [];
        if (p.labels.length == 0 || p.labelsFromSeries) {    
            if (p.stackedValue) {
                if (this._plotData.length && this._plotData[0].length){
                    // var idx = p.seriesLabelIndex || this._plotData[0].length -1;
                    for (var i=0; i<this._plotData.length; i++) {
                        p._labels.push(this._plotData[i][labelIdx]);
                    }
                }
            }
            else {
                var d = this.data;
                if (this.renderer.constructor == $.jqplot.BarRenderer && this.waterfall) {
                    d = this._data;
                }
                if (d.length && d[0].length) {
                    // var idx = p.seriesLabelIndex || d[0].length -1;
                    for (var i=0; i<d.length; i++) {
                        p._labels.push(d[i][labelIdx]);
                    }
                }
            }
        }
        else if (p.labels.length){
            p._labels = p.labels;
        }
    };
    
    $.jqplot.PointLabels.prototype.xOffset = function(elem, location, padding) {
        location = location || this.location;
        padding = padding || this.xpadding;
        var offset;
        
        switch (location) {
            case 'nw':
                offset = -elem.outerWidth(true) - this.xpadding;
                break;
            case 'n':
                offset = -elem.outerWidth(true)/2;
                break;
            case 'ne':
                offset =  this.xpadding;
                break;
            case 'e':
                offset = this.xpadding;
                break;
            case 'se':
                offset = this.xpadding;
                break;
            case 's':
                offset = -elem.outerWidth(true)/2;
                break;
            case 'sw':
                offset = -elem.outerWidth(true) - this.xpadding;
                break;
            case 'w':
                offset = -elem.outerWidth(true) - this.xpadding;
                break;
            default: // same as 'nw'
                offset = -elem.outerWidth(true) - this.xpadding;
                break;
        }
        return offset; 
    };
    
    $.jqplot.PointLabels.prototype.yOffset = function(elem, location, padding) {
        location = location || this.location;
        padding = padding || this.xpadding;
        var offset;
        
        switch (location) {
            case 'nw':
                offset = -elem.outerHeight(true) - this.ypadding;
                break;
            case 'n':
                offset = -elem.outerHeight(true) - this.ypadding;
                break;
            case 'ne':
                offset = -elem.outerHeight(true) - this.ypadding;
                break;
            case 'e':
                offset = -elem.outerHeight(true)/2;
                break;
            case 'se':
                offset = this.ypadding;
                break;
            case 's':
                offset = this.ypadding;
                break;
            case 'sw':
                offset = this.ypadding;
                break;
            case 'w':
                offset = -elem.outerHeight(true)/2;
                break;
            default: // same as 'nw'
                offset = -elem.outerHeight(true) - this.ypadding;
                break;
        }
        return offset; 
    };
    
    // called with scope of series
    $.jqplot.PointLabels.draw = function (sctx, options) {
        var p = this.plugins.pointLabels;
        // set labels again in case they have changed.
        p.setLabels.call(this);
        // remove any previous labels
        for (var i=0; i<p._elems.length; i++) {
            // Memory Leaks patch
            // p._elems[i].remove();
            p._elems[i].emptyForce();
        }
        p._elems.splice(0, p._elems.length);

        if (p.show) {
            var ax = '_'+this._stackAxis+'axis';
        
            if (!p.formatString) {
                p.formatString = this[ax]._ticks[0].formatString;
                p.formatter = this[ax]._ticks[0].formatter;
            }
        
            var pd = this._plotData;
            var xax = this._xaxis;
            var yax = this._yaxis;
            var elem, helem;

            for (var i=0, l=p._labels.length; i < l; i++) {
                var label = p._labels[i];
                
                if (p.hideZeros && parseInt(p._labels[i], 10) == 0) {
                    label = '';
                }
                
                if (label != null) {
                    label = p.formatter(p.formatString, label);
                } 

                helem = document.createElement('div');
                p._elems[i] = $(helem);

                elem = p._elems[i];


                elem.addClass('jqplot-point-label jqplot-series-'+this.index+' jqplot-point-'+i);
                elem.css('position', 'absolute');
                elem.insertAfter(sctx.canvas);

                if (p.escapeHTML) {
                    elem.text(label);
                }
                else {
                    elem.html(label);
                }
                var location = p.location;
                if ((this.fillToZero && pd[i][1] < 0) || (this.waterfall && parseInt(label, 10)) < 0) {
                    location = oppositeLocations[locationIndicies[location]];
                }
                var ell = xax.u2p(pd[i][0]) + p.xOffset(elem, location);
                var elt = yax.u2p(pd[i][1]) + p.yOffset(elem, location);
                if (this.renderer.constructor == $.jqplot.BarRenderer) {
                    if (this.barDirection == "vertical") {
                        ell += this._barNudge;
                    }
                    else {
                        elt -= this._barNudge;
                    }
                }
                elem.css('left', ell);
                elem.css('top', elt);
                var elr = ell + elem.width();
                var elb = elt + elem.height();
                var et = p.edgeTolerance;
                var scl = $(sctx.canvas).position().left;
                var sct = $(sctx.canvas).position().top;
                var scr = sctx.canvas.width + scl;
                var scb = sctx.canvas.height + sct;
                // if label is outside of allowed area, remove it
                if (ell - et < scl || elt - et < sct || elr + et > scr || elb + et > scb) {
                    elem.remove();
                }
                elem = null;
                helem = null;
            }
        }
    };
    
    $.jqplot.postSeriesInitHooks.push($.jqplot.PointLabels.init);
    $.jqplot.postDrawSeriesHooks.push($.jqplot.PointLabels.draw);
})(jQuery);