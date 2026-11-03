/**
 * jqPlot
 * Pure JavaScript plotting plugin using jQuery
 *
 * Version: @VERSION
 * Revision: @REVISION
 *
 * Copyright (c) 2009-2016 Chris Leonello
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
    // Class: $.jqplot.LineRenderer
    // The default line renderer for jqPlot, this class has no options beyond the <Series> class.
    // Draws series as a line.
    $.jqplot.LineRenderer = function(){
        this.shapeRenderer = new $.jqplot.ShapeRenderer();
        this.shadowRenderer = new $.jqplot.ShadowRenderer();
    };
    
    // called with scope of series.
    $.jqplot.LineRenderer.prototype.init = function(options, plot) {
        // Group: Properties
        //
        options = options || {};
        this._type='line';
        this.renderer.animation = {
            show: false,
            direction: 'left',
            speed: 2500,
            _supported: true
        };
        // prop: smooth
        // True to draw a smoothed (interpolated) line through the data points
        // with automatically computed number of smoothing points.
        // Set to an integer number > 2 to specify number of smoothing points
        // to use between each data point.
        this.renderer.smooth = false;  // true or a number > 2 for smoothing.
        this.renderer.tension = null; // null to auto compute or a number typically > 6.  Fewer points requires higher tension.
        // prop: constrainSmoothing
        // True to use a more accurate smoothing algorithm that will
        // not overshoot any data points.  False to allow overshoot but
        // produce a smoother looking line.
        this.renderer.constrainSmoothing = true;
        // this is smoothed data in grid coordinates, like gridData
        this.renderer._smoothedData = [];
        // this is smoothed data in plot units (plot coordinates), like plotData.
        this.renderer._smoothedPlotData = [];
        this.renderer._hiBandGridData = [];
        this.renderer._lowBandGridData = [];
        this.renderer._hiBandSmoothedData = [];
        this.renderer._lowBandSmoothedData = [];

        // prop: bandData
        // Data used to draw error bands or confidence intervals above/below a line.
        //
        // bandData can be input in 3 forms.  jqPlot will figure out which is the
        // low band line and which is the high band line for all forms:
        // 
        // A 2 dimensional array like [[yl1, yl2, ...], [yu1, yu2, ...]] where
        // [yl1, yl2, ...] are y values of the lower line and
        // [yu1, yu2, ...] are y values of the upper line.
        // In this case there must be the same number of y data points as data points
        // in the series and the bands will inherit the x values of the series.
        //
        // A 2 dimensional array like [[[xl1, yl1], [xl2, yl2], ...], [[xh1, yh1], [xh2, yh2], ...]]
        // where [xl1, yl1] are x,y data points for the lower line and
        // [xh1, yh1] are x,y data points for the high line.
        // x values do not have to correspond to the x values of the series and can
        // be of any arbitrary length.
        //
        // Can be of form [[yl1, yu1], [yl2, yu2], [yl3, yu3], ...] where
        // there must be 3 or more arrays and there must be the same number of arrays
        // as there are data points in the series.  In this case, 
        // [yl1, yu1] specifies the lower and upper y values for the 1st
        // data point and so on.  The bands will inherit the x
        // values from the series.
        this.renderer.bandData = [];

        // Group: bands
        // Banding around line, e.g error bands or confidence intervals.
        this.renderer.bands = {
            // prop: show
            // true to show the bands.  If bandData or interval is
            // supplied, show will be set to true by default.
            show: false,
            hiData: [],
            lowData: [],
            // prop: color
            // color of lines at top and bottom of bands [default: series color].
            color: this.color,
            // prop: showLines
            // True to show lines at top and bottom of bands [default: false].
            showLines: false,
            // prop: fill
            // True to fill area between bands [default: true].
            fill: true,
            // prop: fillColor
            // css color spec for filled area.  [default: series color].
            fillColor: null,
            _min: null,
            _max: null,
            // prop: interval
            // User specified interval above and below line for bands [default: '3%''].
            // Can be a value like 3 or a string like '3%' 
            // or an upper/lower array like [1, -2] or ['2%', '-1.5%']
            interval: '3%'
        };


        var lopts = {highlightMouseOver: options.highlightMouseOver, highlightMouseDown: options.highlightMouseDown, highlightColor: options.highlightColor};
        
        delete (options.highlightMouseOver);
        delete (options.highlightMouseDown);
        delete (options.highlightColor);
        
        $.extend(true, this.renderer, options);

        this.renderer.options = options;

        // if we are given some band data, and bands aren't explicity set to false in options, turn them on.
        if (this.renderer.bandData.length > 1 && (!options.bands || options.bands.show == null)) {
            this.renderer.bands.show = true;
        }

        // if we are given an interval, and bands aren't explicity set to false in options, turn them on.
        else if (options.bands && options.bands.show == null && options.bands.interval != null) {
            this.renderer.bands.show = true;
        }

        // if plot is filled, turn off bands.
        if (this.fill) {
            this.renderer.bands.show = false;
        }

        if (this.renderer.bands.show) {
            this.renderer.initBands.call(this, this.renderer.options, plot);
        }


        // smoothing is not compatible with stacked lines, disable
        if (this._stack) {
            this.renderer.smooth = false;
        }

        // set the shape renderer options
        var opts = {lineJoin:this.lineJoin, lineCap:this.lineCap, fill:this.fill, isarc:false, strokeStyle:this.color, fillStyle:this.fillColor, lineWidth:this.lineWidth, linePattern:this.linePattern, closePath:this.fill};
        this.renderer.shapeRenderer.init(opts);

        var shadow_offset = options.shadowOffset;
        // set the shadow renderer options
        if (shadow_offset == null) {
            // scale the shadowOffset to the width of the line.
            if (this.lineWidth > 2.5) {
                shadow_offset = 1.25 * (1 + (Math.atan((this.lineWidth/2.5))/0.785398163 - 1)*0.6);
                // var shadow_offset = this.shadowOffset;
            }
            // for skinny lines, don't make such a big shadow.
            else {
                shadow_offset = 1.25 * Math.atan((this.lineWidth/2.5))/0.785398163;
            }
        }
        
        var sopts = {lineJoin:this.lineJoin, lineCap:this.lineCap, fill:this.fill, isarc:false, angle:this.shadowAngle, offset:shadow_offset, alpha:this.shadowAlpha, depth:this.shadowDepth, lineWidth:this.lineWidth, linePattern:this.linePattern, closePath:this.fill};
        this.renderer.shadowRenderer.init(sopts);
        this._areaPoints = [];
        this._boundingBox = [[],[]];
        
        if (!this.isTrendline && this.fill || this.renderer.bands.show) {
            // Group: Properties
            //        
            // prop: highlightMouseOver
            // True to highlight area on a filled plot when moused over.
            // This must be false to enable highlightMouseDown to highlight when clicking on an area on a filled plot.
            this.highlightMouseOver = true;
            // prop: highlightMouseDown
            // True to highlight when a mouse button is pressed over an area on a filled plot.
            // This will be disabled if highlightMouseOver is true.
            this.highlightMouseDown = false;
            // prop: highlightColor
            // color to use when highlighting an area on a filled plot.
            this.highlightColor = null;
            // if user has passed in highlightMouseDown option and not set highlightMouseOver, disable highlightMouseOver
            if (lopts.highlightMouseDown && lopts.highlightMouseOver == null) {
                lopts.highlightMouseOver = false;
            }
        
            $.extend(true, this, {highlightMouseOver: lopts.highlightMouseOver, highlightMouseDown: lopts.highlightMouseDown, highlightColor: lopts.highlightColor});
            
            if (!this.highlightColor) {
                var fc = (this.renderer.bands.show) ? this.renderer.bands.fillColor : this.fillColor;
                this.highlightColor = $.jqplot.computeHighlightColors(fc);
            }
            // turn off (disable) the highlighter plugin
            if (this.highlighter) {
                this.highlighter.show = false;
            }
        }
        
        if (!this.isTrendline && plot) {
            plot.plugins.lineRenderer = {};
            plot.postInitHooks.addOnce(postInit);
            plot.postDrawHooks.addOnce(postPlotDraw);
            plot.eventListenerHooks.addOnce('jqplotMouseMove', handleMove);
            plot.eventListenerHooks.addOnce('jqplotMouseDown', handleMouseDown);
            plot.eventListenerHooks.addOnce('jqplotMouseUp', handleMouseUp);
            plot.eventListenerHooks.addOnce('jqplotClick', handleClick);
            plot.eventListenerHooks.addOnce('jqplotRightClick', handleRightClick);
        }

    };

    $.jqplot.LineRenderer.prototype.initBands = function(options, plot) {
        // use bandData if no data specified in bands option
        //var bd = this.renderer.bandData;
        var bd = options.bandData || [];
        var bands = this.renderer.bands;
        bands.hiData = [];
        bands.lowData = [];
        var data = this.data;
        bands._max = null;
        bands._min = null;
        // If 2 arrays, and each array greater than 2 elements, assume it is hi and low data bands of y values.
        if (bd.length == 2) {
            // Do we have an array of x,y values?
            // like [[[1,1], [2,4], [3,3]], [[1,3], [2,6], [3,5]]]
            if ($.isArray(bd[0][0])) {
                // since an arbitrary array of points, spin through all of them to determine max and min lines.

                var p;
                var bdminidx = 0, bdmaxidx = 0;
                for (var i = 0, l = bd[0].length; i<l; i++) {
                    p = bd[0][i];
                    if ((p[1] != null && p[1] > bands._max) || bands._max == null) {
                        bands._max = p[1];
                    }
                    if ((p[1] != null && p[1] < bands._min) || bands._min == null) {
                        bands._min = p[1];
                    }
                }
                for (var i = 0, l = bd[1].length; i<l; i++) {
                    p = bd[1][i];
                    if ((p[1] != null && p[1] > bands._max) || bands._max == null) {
                        bands._max = p[1];
                        bdmaxidx = 1;
                    }
                    if ((p[1] != null && p[1] < bands._min) || bands._min == null) {
                        bands._min = p[1];
                        bdminidx = 1;
                    }
                }

                if (bdmaxidx === bdminidx) {
                    bands.show = false;
                }

                bands.hiData = bd[bdmaxidx];
                bands.lowData = bd[bdminidx];
            }
            // else data is arrays of y values
            // like [[1,4,3], [3,6,5]]
            // must have same number of band data points as points in series
            else if (bd[0].length === data.length && bd[1].length === data.length) {
                var hi = (bd[0][0] > bd[1][0]) ? 0 : 1;
                var low = (hi) ? 0 : 1;
                for (var i=0, l=data.length; i < l; i++) {
                    bands.hiData.push([data[i][0], bd[hi][i]]);
                    bands.lowData.push([data[i][0], bd[low][i]]);
                }
            }

            // we don't have proper data array, don't show bands.
            else {
                bands.show = false;
            }
        }

        // if more than 2 arrays, have arrays of [ylow, yhi] values.
        // note, can't distinguish case of [[ylow, yhi], [ylow, yhi]] from [[ylow, ylow], [yhi, yhi]]
        // this is assumed to be of the latter form.
        else if (bd.length > 2 && !$.isArray(bd[0][0])) {
            var hi = (bd[0][0] > bd[0][1]) ? 0 : 1;
            var low = (hi) ? 0 : 1;
            for (var i=0, l=bd.length; i<l; i++) {
                bands.hiData.push([data[i][0], bd[i][hi]]);
                bands.lowData.push([data[i][0], bd[i][low]]);
            }
        }

        // don't have proper data, auto calculate
        else {
            var intrv = bands.interval;
            var a = null;
            var b = null;
            var afunc = null;
            var bfunc = null;

            if ($.isArray(intrv)) {
                a = intrv[0];
                b = intrv[1];
            }
            else {
                a = intrv;
            }

            if (isNaN(a)) {
                // we have a string
                if (a.charAt(a.length - 1) === '%') {
                    afunc = 'multiply';
                    a = parseFloat(a)/100 + 1;
                }
            }

            else {
                a = parseFloat(a);
                afunc = 'add';
            }

            if (b !== null && isNaN(b)) {
                // we have a string
                if (b.charAt(b.length - 1) === '%') {
                    bfunc = 'multiply';
                    b = parseFloat(b)/100 + 1;
                }
            }

            else if (b !== null) {
                b = parseFloat(b);
                bfunc = 'add';
            }

            if (a !== null) {
                if (b === null) {
                    b = -a;
                    bfunc = afunc;
                    if (bfunc === 'multiply') {
                        b += 2;
                    }
                }

                // make sure a always applies to hi band.
                if (a < b) {
                    var temp = a;
                    a = b;
                    b = temp;
                    temp = afunc;
                    afunc = bfunc;
                    bfunc = temp;
                }

                for (var i=0, l = data.length; i < l; i++) {
                    switch (afunc) {
                        case 'add':
                            bands.hiData.push([data[i][0], data[i][1] + a]);
                            break;
                        case 'multiply':
                            bands.hiData.push([data[i][0], data[i][1] * a]);
                            break;
                    }
                    switch (bfunc) {
                        case 'add':
                            bands.lowData.push([data[i][0], data[i][1] + b]);
                            break;
                        case 'multiply':
                            bands.lowData.push([data[i][0], data[i][1] * b]);
                            break;
                    }
                }
            }

            else {
                bands.show = false;
            }
        }

        var hd = bands.hiData;
        var ld = bands.lowData;
        for (var i = 0, l = hd.length; i<l; i++) {
            if ((hd[i][1] != null && hd[i][1] > bands._max) || bands._max == null) {
                bands._max = hd[i][1];
            }
        }
        for (var i = 0, l = ld.length; i<l; i++) {
            if ((ld[i][1] != null && ld[i][1] < bands._min) || bands._min == null) {
                bands._min = ld[i][1];
            }
        }

        // one last check for proper data
        // these don't apply any more since allowing arbitrary x,y values
        // if (bands.hiData.length != bands.lowData.length) {
        //     bands.show = false;
        // }

        // if (bands.hiData.length != this.data.length) {
        //     bands.show = false;
        // }

        if (bands.fillColor === null) {
            var c = $.jqplot.getColorComponents(bands.color);
            // now adjust alpha to differentiate fill
            c[3] = c[3] * 0.5;
            bands.fillColor = 'rgba(' + c[0] +', '+ c[1] +', '+ c[2] +', '+ c[3] + ')';
        }
    };

    function getSteps (d, f) {
        return (3.4182054+f) * Math.pow(d, -0.3534992);
    }

    function computeSteps (d1, d2) {
        var s = Math.sqrt(Math.pow((d2[0]- d1[0]), 2) + Math.pow ((d2[1] - d1[1]), 2));
        return 5.7648 * Math.log(s) + 7.4456;
    }

    function tanh (x) {
        var a = (Math.exp(2*x) - 1) / (Math.exp(2*x) + 1);
        return a;
    }

    //////////
    // computeConstrainedSmoothedData
    // An implementation of the constrained cubic spline interpolation
    // method as presented in:
    //
    // Kruger, CJC, Constrained Cubic Spine Interpolation for Chemical Engineering Applications
    // http://www.korf.co.uk/spline.pdf
    //
    // The implementation below borrows heavily from the sample Visual Basic
    // implementation by CJC Kruger found in http://www.korf.co.uk/spline.xls
    //
    /////////

    // called with scope of series
    function computeConstrainedSmoothedData (gd) {
        var smooth = this.renderer.smooth;
        var dim = this.canvas.getWidth();
        var xp = this._xaxis.series_p2u;
        var yp = this._yaxis.series_p2u; 
        var steps =null;
        var _steps = null;
        var dist = gd.length/dim;
        var _smoothedData = [];
        var _smoothedPlotData = [];

        if (!isNaN(parseFloat(smooth))) {
            steps = parseFloat(smooth);
        }
        else {
            steps = getSteps(dist, 0.5);
        }

        var yy = [];
        var xx = [];

        for (var i=0, l = gd.length; i<l; i++) {
            yy.push(gd[i][1]);
            xx.push(gd[i][0]);
        }

        function dxx(x1, x0) {
            if (x1 - x0 == 0) {
                return Math.pow(10,10);
            }
            else {
                return x1 - x0;
            }
        }

        var A, B, C, D;
        // loop through each line segment.  Have # points - 1 line segments.  Nmber segments starting at 1.
        var nmax = gd.length - 1;
        for (var num = 1, gdl = gd.length; num<gdl; num++) {
            var gxx = [];
            var ggxx = [];
            // point at each end of segment.
            for (var j = 0; j < 2; j++) {
                var i = num - 1 + j; // point number, 0 to # points.

                if (i == 0 || i == nmax) {
                    gxx[j] = Math.pow(10, 10);
                }
                else if (yy[i+1] - yy[i] == 0 || yy[i] - yy[i-1] == 0) {
                    gxx[j] = 0;
                }
                else if (((xx[i+1] - xx[i]) / (yy[i+1] - yy[i]) + (xx[i] - xx[i-1]) / (yy[i] - yy[i-1])) == 0 ) {
                    gxx[j] = 0;
                }
                else if ( (yy[i+1] - yy[i]) * (yy[i] - yy[i-1]) < 0 ) {
                    gxx[j] = 0;
                }

                else {
                    gxx[j] = 2 / (dxx(xx[i + 1], xx[i]) / (yy[i + 1] - yy[i]) + dxx(xx[i], xx[i - 1]) / (yy[i] - yy[i - 1]));
                }
            }

            // Reset first derivative (slope) at first and last point
            if (num == 1) {
                // First point has 0 2nd derivative
                gxx[0] = 3 / 2 * (yy[1] - yy[0]) / dxx(xx[1], xx[0]) - gxx[1] / 2;
            }
            else if (num == nmax) {
                // Last point has 0 2nd derivative
                gxx[1] = 3 / 2 * (yy[nmax] - yy[nmax - 1]) / dxx(xx[nmax], xx[nmax - 1]) - gxx[0] / 2;
            }   

            // Calc second derivative at points
            ggxx[0] = -2 * (gxx[1] + 2 * gxx[0]) / dxx(xx[num], xx[num - 1]) + 6 * (yy[num] - yy[num - 1]) / Math.pow(dxx(xx[num], xx[num - 1]), 2);
            ggxx[1] = 2 * (2 * gxx[1] + gxx[0]) / dxx(xx[num], xx[num - 1]) - 6 * (yy[num] - yy[num - 1]) / Math.pow(dxx(xx[num], xx[num - 1]), 2);

            // Calc constants for cubic interpolation
            D = 1 / 6 * (ggxx[1] - ggxx[0]) / dxx(xx[num], xx[num - 1]);
            C = 1 / 2 * (xx[num] * ggxx[0] - xx[num - 1] * ggxx[1]) / dxx(xx[num], xx[num - 1]);
            B = (yy[num] - yy[num - 1] - C * (Math.pow(xx[num], 2) - Math.pow(xx[num - 1], 2)) - D * (Math.pow(xx[num], 3) - Math.pow(xx[num - 1], 3))) / dxx(xx[num], xx[num - 1]);
            A = yy[num - 1] - B * xx[num - 1] - C * Math.pow(xx[num - 1], 2) - D * Math.pow(xx[num - 1], 3);

            var increment = (xx[num] - xx[num - 1]) / steps;
            var temp, tempx;

            for (var j = 0, l = steps; j < l; j++) {
                temp = [];
                tempx = xx[num - 1] + j * increment;
                temp.push(tempx);
                temp.push(A + B * tempx + C * Math.pow(tempx, 2) + D * Math.pow(tempx, 3));
                _smoothedData.push(temp);
                _smoothedPlotData.push([xp(temp[0]), yp(temp[1])]);
            }
        }

        _smoothedData.push(gd[i]);
        _smoothedPlotData.push([xp(gd[i][0]), yp(gd[i][1])]);

        return [_smoothedData, _smoothedPlotData];
    }

    ///////
    // computeHermiteSmoothedData
    // A hermite spline smoothing of the plot data.
    // This implementation is derived from the one posted
    // by krypin on the jqplot-users mailing list:
    //
    // http://groups.google.com/group/jqplot-users/browse_thread/thread/748be6a445723cea?pli=1
    //
    // with a blog post:
    //
    // http://blog.statscollector.com/a-plugin-renderer-for-jqplot-to-draw-a-hermite-spline/
    //
    // and download of the original plugin:
    //
    // http://blog.statscollector.com/wp-content/uploads/2010/02/jqplot.hermiteSplineRenderer.js
    //////////

    // called with scope of series
    function computeHermiteSmoothedData (gd) {
        var smooth = this.renderer.smooth;
        var tension = this.renderer.tension;
        var dim = this.canvas.getWidth();
        var xp = this._xaxis.series_p2u;
        var yp = this._yaxis.series_p2u; 
        var steps =null;
        var _steps = null;
        var a = null;
        var a1 = null;
        var a2 = null;
        var slope = null;
        var slope2 = null;
        var temp = null;
        var t, s, h1, h2, h3, h4;
        var TiX, TiY, Ti1X, Ti1Y;
        var pX, pY, p;
        var sd = [];
        var spd = [];
        var dist = gd.length/dim;
        var min, max, stretch, scale, shift;
        var _smoothedData = [];
        var _smoothedPlotData = [];
        if (!isNaN(parseFloat(smooth))) {
            steps = parseFloat(smooth);
        }
        else {
            steps = getSteps(dist, 0.5);
        }
        if (!isNaN(parseFloat(tension))) {
            tension = parseFloat(tension);
        }

        for (var i=0, l = gd.length-1; i < l; i++) {

            if (tension === null) {
                slope = Math.abs((gd[i+1][1] - gd[i][1]) / (gd[i+1][0] - gd[i][0]));

                min = 0.3;
                max = 0.6;
                stretch = (max - min)/2.0;
                scale = 2.5;
                shift = -1.4;

                temp = slope/scale + shift;

                a1 = stretch * tanh(temp) - stretch * tanh(shift) + min;

                // if have both left and right line segments, will use  minimum tension. 
                if (i > 0) {
                    slope2 = Math.abs((gd[i][1] - gd[i-1][1]) / (gd[i][0] - gd[i-1][0]));
                }
                temp = slope2/scale + shift;

                a2 = stretch * tanh(temp) - stretch * tanh(shift) + min;

                a = (a1 + a2)/2.0;

            }
            else {
                a = tension;
            }
            for (t=0; t < steps; t++) {
                s = t / steps;
                h1 = (1 + 2*s)*Math.pow((1-s),2);
                h2 = s*Math.pow((1-s),2);
                h3 = Math.pow(s,2)*(3-2*s);
                h4 = Math.pow(s,2)*(s-1);     
                
                if (gd[i-1]) {  
                    TiX = a * (gd[i+1][0] - gd[i-1][0]); 
                    TiY = a * (gd[i+1][1] - gd[i-1][1]);
                } else {
                    TiX = a * (gd[i+1][0] - gd[i][0]); 
                    TiY = a * (gd[i+1][1] - gd[i][1]);                                  
                }
                if (gd[i+2]) {  
                    Ti1X = a * (gd[i+2][0] - gd[i][0]); 
                    Ti1Y = a * (gd[i+2][1] - gd[i][1]);
                } else {
                    Ti1X = a * (gd[i+1][0] - gd[i][0]); 
                    Ti1Y = a * (gd[i+1][1] - gd[i][1]);                                 
                }
                
                pX = h1*gd[i][0] + h3*gd[i+1][0] + h2*TiX + h4*Ti1X;
                pY = h1*gd[i][1] + h3*gd[i+1][1] + h2*TiY + h4*Ti1Y;
                p = [pX, pY];

                _smoothedData.push(p);
                _smoothedPlotData.push([xp(pX), yp(pY)]);
            }
        }
        _smoothedData.push(gd[l]);
        _smoothedPlotData.push([xp(gd[l][0]), yp(gd[l][1])]);

        return [_smoothedData, _smoothedPlotData];
    }
    
    // setGridData
    // converts the user data values to grid coordinates and stores them
    // in the gridData array.
    // Called with scope of a series.
    $.jqplot.LineRenderer.prototype.setGridData = function(plot) {
        // recalculate the grid data
        var xp = this._xaxis.series_u2p;
        var yp = this._yaxis.series_u2p;
        var data = this._plotData;
        var pdata = this._prevPlotData;
        this.gridData = [];
        this._prevGridData = [];
        this.renderer._smoothedData = [];
        this.renderer._smoothedPlotData = [];
        this.renderer._hiBandGridData = [];
        this.renderer._lowBandGridData = [];
        this.renderer._hiBandSmoothedData = [];
        this.renderer._lowBandSmoothedData = [];
        var bands = this.renderer.bands;
        var hasNull = false;
        for (var i=0, l=data.length; i < l; i++) {
            // if not a line series or if no nulls in data, push the converted point onto the array.
            if (data[i][0] != null && data[i][1] != null) {
                this.gridData.push([xp.call(this._xaxis, data[i][0]), yp.call(this._yaxis, data[i][1])]);
            }
            // else if there is a null, preserve it.
            else if (data[i][0] == null) {
                hasNull = true;
                this.gridData.push([null, yp.call(this._yaxis, data[i][1])]);
            }
            else if (data[i][1] == null) {
                hasNull = true;
                this.gridData.push([xp.call(this._xaxis, data[i][0]), null]);
            }
            // if not a line series or if no nulls in data, push the converted point onto the array.
            if (pdata[i] != null && pdata[i][0] != null && pdata[i][1] != null) {
                this._prevGridData.push([xp.call(this._xaxis, pdata[i][0]), yp.call(this._yaxis, pdata[i][1])]);
            }
            // else if there is a null, preserve it.
            else if (pdata[i] != null && pdata[i][0] == null) {
                this._prevGridData.push([null, yp.call(this._yaxis, pdata[i][1])]);
            }  
            else if (pdata[i] != null && pdata[i][0] != null && pdata[i][1] == null) {
                this._prevGridData.push([xp.call(this._xaxis, pdata[i][0]), null]);
            }
        }

        // don't do smoothing or bands on broken lines.
        if (hasNull) {
            this.renderer.smooth = false;
            if (this._type === 'line') {
                bands.show = false;
            }
        }

        if (this._type === 'line' && bands.show) {
            for (var i=0, l=bands.hiData.length; i<l; i++) {
                this.renderer._hiBandGridData.push([xp.call(this._xaxis, bands.hiData[i][0]), yp.call(this._yaxis, bands.hiData[i][1])]);
            }
            for (var i=0, l=bands.lowData.length; i<l; i++) {
                this.renderer._lowBandGridData.push([xp.call(this._xaxis, bands.lowData[i][0]), yp.call(this._yaxis, bands.lowData[i][1])]);
            }
        }

        // calculate smoothed data if enough points and no nulls
        if (this._type === 'line' && this.renderer.smooth && this.gridData.length > 2) {
            var ret;
            if (this.renderer.constrainSmoothing) {
                ret = computeConstrainedSmoothedData.call(this, this.gridData);
                this.renderer._smoothedData = ret[0];
                this.renderer._smoothedPlotData = ret[1];

                if (bands.show) {
                    ret = computeConstrainedSmoothedData.call(this, this.renderer._hiBandGridData);
                    this.renderer._hiBandSmoothedData = ret[0];
                    ret = computeConstrainedSmoothedData.call(this, this.renderer._lowBandGridData);
                    this.renderer._lowBandSmoothedData = ret[0];
                }

                ret = null;
            }
            else {
                ret = computeHermiteSmoothedData.call(this, this.gridData);
                this.renderer._smoothedData = ret[0];
                this.renderer._smoothedPlotData = ret[1];

                if (bands.show) {
                    ret = computeHermiteSmoothedData.call(this, this.renderer._hiBandGridData);
                    this.renderer._hiBandSmoothedData = ret[0];
                    ret = computeHermiteSmoothedData.call(this, this.renderer._lowBandGridData);
                    this.renderer._lowBandSmoothedData = ret[0];
                }

                ret = null;
            }
        }
    };
    
    // makeGridData
    // converts any arbitrary data values to grid coordinates and
    // returns them.  This method exists so that plugins can use a series'
    // linerenderer to generate grid data points without overwriting the
    // grid data associated with that series.
    // Called with scope of a series.
    $.jqplot.LineRenderer.prototype.makeGridData = function(data, plot) {
        // recalculate the grid data
        var xp = this._xaxis.series_u2p;
        var yp = this._yaxis.series_u2p;
        var gd = [];
        var pgd = [];
        this.renderer._smoothedData = [];
        this.renderer._smoothedPlotData = [];
        this.renderer._hiBandGridData = [];
        this.renderer._lowBandGridData = [];
        this.renderer._hiBandSmoothedData = [];
        this.renderer._lowBandSmoothedData = [];
        var bands = this.renderer.bands;
        var hasNull = false;
        for (var i=0; i<data.length; i++) {
            // if not a line series or if no nulls in data, push the converted point onto the array.
            if (data[i][0] != null && data[i][1] != null) {
                if (this.step && i>0) {
                    gd.push([xp.call(this._xaxis, data[i][0]), yp.call(this._yaxis, data[i-1][1])]);
                }
                gd.push([xp.call(this._xaxis, data[i][0]), yp.call(this._yaxis, data[i][1])]);
            }
            // else if there is a null, preserve it.
            else if (data[i][0] == null) {
                hasNull = true;
                gd.push([null, yp.call(this._yaxis, data[i][1])]);
            }
            else if (data[i][1] == null) {
                hasNull = true;
                gd.push([xp.call(this._xaxis, data[i][0]), null]);
            }
        }

        // don't do smoothing or bands on broken lines.
        if (hasNull) {
            this.renderer.smooth = false;
            if (this._type === 'line') {
                bands.show = false;
            }
        }

        if (this._type === 'line' && bands.show) {
            for (var i=0, l=bands.hiData.length; i<l; i++) {
                this.renderer._hiBandGridData.push([xp.call(this._xaxis, bands.hiData[i][0]), yp.call(this._yaxis, bands.hiData[i][1])]);
            }
            for (var i=0, l=bands.lowData.length; i<l; i++) {
                this.renderer._lowBandGridData.push([xp.call(this._xaxis, bands.lowData[i][0]), yp.call(this._yaxis, bands.lowData[i][1])]);
            }
        }

        if (this._type === 'line' && this.renderer.smooth && gd.length > 2) {
            var ret;
            if (this.renderer.constrainSmoothing) {
                ret = computeConstrainedSmoothedData.call(this, gd);
                this.renderer._smoothedData = ret[0];
                this.renderer._smoothedPlotData = ret[1];

                if (bands.show) {
                    ret = computeConstrainedSmoothedData.call(this, this.renderer._hiBandGridData);
                    this.renderer._hiBandSmoothedData = ret[0];
                    ret = computeConstrainedSmoothedData.call(this, this.renderer._lowBandGridData);
                    this.renderer._lowBandSmoothedData = ret[0];
                }

                ret = null;
            }
            else {
                ret = computeHermiteSmoothedData.call(this, gd);
                this.renderer._smoothedData = ret[0];
                this.renderer._smoothedPlotData = ret[1];

                if (bands.show) {
                    ret = computeHermiteSmoothedData.call(this, this.renderer._hiBandGridData);
                    this.renderer._hiBandSmoothedData = ret[0];
                    ret = computeHermiteSmoothedData.call(this, this.renderer._lowBandGridData);
                    this.renderer._lowBandSmoothedData = ret[0];
                }

                ret = null;
            }
        }
        return gd;
    };
    

    // called within scope of series.
    $.jqplot.LineRenderer.prototype.draw = function(ctx, gd, options, plot) {
        var i;
        // get a copy of the options, so we don't modify the original object.
        var opts = $.extend(true, {}, options);
        var shadow = (opts.shadow != undefined) ? opts.shadow : this.shadow;
        var showLine = (opts.showLine != undefined) ? opts.showLine : this.showLine;
        var fill = (opts.fill != undefined) ? opts.fill : this.fill;
        var fillAndStroke = (opts.fillAndStroke != undefined) ? opts.fillAndStroke : this.fillAndStroke;
        var xmin, ymin, xmax, ymax;
        ctx.save();
        if (gd.length) {
            if (showLine) {
                // if we fill, we'll have to add points to close the curve.
                if (fill) {
                    if (this.fillToZero) { 
                        // have to break line up into shapes at axis crossings
                        var negativeColor = this.negativeColor;
                        if (! this.useNegativeColors) {
                            negativeColor = opts.fillStyle;
                        }
                        var isnegative = false;
                        var posfs = opts.fillStyle;
                    
                        // if stoking line as well as filling, get a copy of line data.
                        if (fillAndStroke) {
                            var fasgd = gd.slice(0);
                        }
                        // if not stacked, fill down to axis
                        if (this.index == 0 || !this._stack) {
                        
                            var tempgd = [];
                            var pd = (this.renderer.smooth) ? this.renderer._smoothedPlotData : this._plotData;
                            this._areaPoints = [];
                            var pyzero = this._yaxis.series_u2p(this.fillToValue);
                            var pxzero = this._xaxis.series_u2p(this.fillToValue);

                            opts.closePath = true;
                            
                            if (this.fillAxis == 'y') {
                                tempgd.push([gd[0][0], pyzero]);
                                this._areaPoints.push([gd[0][0], pyzero]);
                                
                                for (var i=0; i<gd.length-1; i++) {
                                    tempgd.push(gd[i]);
                                    this._areaPoints.push(gd[i]);
                                    // do we have an axis crossing?
                                    if (pd[i][1] * pd[i+1][1] <= 0) {
                                        if (pd[i][1] < 0) {
                                            isnegative = true;
                                            opts.fillStyle = negativeColor;
                                        }
                                        else {
                                            isnegative = false;
                                            opts.fillStyle = posfs;
                                        }
                                        
                                        var xintercept = gd[i][0] + (gd[i+1][0] - gd[i][0]) * (pyzero-gd[i][1])/(gd[i+1][1] - gd[i][1]);
                                        tempgd.push([xintercept, pyzero]);
                                        this._areaPoints.push([xintercept, pyzero]);
                                        // now draw this shape and shadow.
                                        if (shadow) {
                                            this.renderer.shadowRenderer.draw(ctx, tempgd, opts);
                                        }
                                        this.renderer.shapeRenderer.draw(ctx, tempgd, opts);
                                        // now empty temp array and continue
                                        tempgd = [[xintercept, pyzero]];
                                        // this._areaPoints = [[xintercept, pyzero]];
                                    }   
                                }
                                if (pd[gd.length-1][1] < 0) {
                                    isnegative = true;
                                    opts.fillStyle = negativeColor;
                                }
                                else {
                                    isnegative = false;
                                    opts.fillStyle = posfs;
                                }
                                tempgd.push(gd[gd.length-1]);
                                this._areaPoints.push(gd[gd.length-1]);
                                tempgd.push([gd[gd.length-1][0], pyzero]); 
                                this._areaPoints.push([gd[gd.length-1][0], pyzero]); 
                            }
                            // now draw the last area.
                            if (shadow) {
                                this.renderer.shadowRenderer.draw(ctx, tempgd, opts);
                            }
                            this.renderer.shapeRenderer.draw(ctx, tempgd, opts);
                            
                            
                            // var gridymin = this._yaxis.series_u2p(0);
                            // // IE doesn't return new length on unshift
                            // gd.unshift([gd[0][0], gridymin]);
                            // len = gd.length;
                            // gd.push([gd[len - 1][0], gridymin]);                   
                        }
                        // if stacked, fill to line below 
                        else {
                            var prev = this._prevGridData;
                            for (var i=prev.length; i>0; i--) {
                                gd.push(prev[i-1]);
                                // this._areaPoints.push(prev[i-1]);
                            }
                            if (shadow) {
                                this.renderer.shadowRenderer.draw(ctx, gd, opts);
                            }
                            this._areaPoints = gd;
                            this.renderer.shapeRenderer.draw(ctx, gd, opts);
                        }
                    }
                    /////////////////////////
                    // Not filled to zero
                    ////////////////////////
                    else {                    
                        // if stoking line as well as filling, get a copy of line data.
                        if (fillAndStroke) {
                            var fasgd = gd.slice(0);
                        }
                        // if not stacked, fill down to axis
                        if (this.index == 0 || !this._stack) {
                            // var gridymin = this._yaxis.series_u2p(this._yaxis.min) - this.gridBorderWidth / 2;
                            var gridymin = ctx.canvas.height;
                            // IE doesn't return new length on unshift
                            gd.unshift([gd[0][0], gridymin]);
                            var len = gd.length;
                            gd.push([gd[len - 1][0], gridymin]);                   
                        }
                        // if stacked, fill to line below 
                        else {
                            var prev = this._prevGridData;
                            for (var i=prev.length; i>0; i--) {
                                gd.push(prev[i-1]);
                            }
                        }
                        this._areaPoints = gd;
                        
                        if (shadow) {
                            this.renderer.shadowRenderer.draw(ctx, gd, opts);
                        }
            
                        this.renderer.shapeRenderer.draw(ctx, gd, opts);                        
                    }
                    if (fillAndStroke) {
                        var fasopts = $.extend(true, {}, opts, {fill:false, closePath:false});
                        this.renderer.shapeRenderer.draw(ctx, fasgd, fasopts);
                        //////////
                        // TODO: figure out some way to do shadows nicely
                        // if (shadow) {
                        //     this.renderer.shadowRenderer.draw(ctx, fasgd, fasopts);
                        // }
                        // now draw the markers
                        if (this.markerRenderer.show) {
                            if (this.renderer.smooth) {
                                fasgd = this.gridData;
                            }
                            for (i=0; i<fasgd.length; i++) {
                                var markerOptions = opts.markerOptions || {};
                                if (this.markerOptionsCallback) {
                                    markerOptions = $.extend(true, markerOptions, this.markerOptionsCallback(plot, this, i, this.data[i], gd[i]) || {});
                                }
                                this.markerRenderer.draw(fasgd[i][0], fasgd[i][1], ctx, markerOptions);
                            }
                        }
                    }
                }
                else {

                    if (this.renderer.bands.show) {
                        var bdat;
                        var bopts = $.extend(true, {}, opts);
                        if (this.renderer.bands.showLines) {
                            bdat = (this.renderer.smooth) ? this.renderer._hiBandSmoothedData : this.renderer._hiBandGridData;
                            this.renderer.shapeRenderer.draw(ctx, bdat, opts);
                            bdat = (this.renderer.smooth) ? this.renderer._lowBandSmoothedData : this.renderer._lowBandGridData;
                            this.renderer.shapeRenderer.draw(ctx, bdat, bopts);
                        }

                        if (this.renderer.bands.fill) {
                            if (this.renderer.smooth) {
                                bdat = this.renderer._hiBandSmoothedData.concat(this.renderer._lowBandSmoothedData.reverse());
                            }
                            else {
                                bdat = this.renderer._hiBandGridData.concat(this.renderer._lowBandGridData.reverse());
                            }
                            this._areaPoints = bdat;
                            bopts.closePath = true;
                            bopts.fill = true;
                            bopts.fillStyle = this.renderer.bands.fillColor;
                            this.renderer.shapeRenderer.draw(ctx, bdat, bopts);
                        }
                    }

                    if (shadow) {
                        this.renderer.shadowRenderer.draw(ctx, gd, opts);
                    }
    
                    this.renderer.shapeRenderer.draw(ctx, gd, opts);
                }
            }
            // calculate the bounding box
            var xmin = xmax = ymin = ymax = null;
            for (i=0; i<this._areaPoints.length; i++) {
                var p = this._areaPoints[i];
                if (xmin > p[0] || xmin == null) {
                    xmin = p[0];
                }
                if (ymax < p[1] || ymax == null) {
                    ymax = p[1];
                }
                if (xmax < p[0] || xmax == null) {
                    xmax = p[0];
                }
                if (ymin > p[1] || ymin == null) {
                    ymin = p[1];
                }
            }

            if (this.type === 'line' && this.renderer.bands.show) {
                ymax = this._yaxis.series_u2p(this.renderer.bands._min);
                ymin = this._yaxis.series_u2p(this.renderer.bands._max);
            }

            this._boundingBox = [[xmin, ymax], [xmax, ymin]];
        
            // now draw the markers
            if (this.markerRenderer.show && !fill) {
                if (this.renderer.smooth) {
                    gd = this.gridData;
                }
                for (i=0; i<gd.length; i++) {
                    var markerOptions = opts.markerOptions || {};
                    if (this.markerOptionsCallback) {
                        markerOptions = $.extend(true, markerOptions, this.markerOptionsCallback(plot, this, i, this.data[i], gd[i]) || {});
                    }
                    if (gd[i][0] != null && gd[i][1] != null) {
                        this.markerRenderer.draw(gd[i][0], gd[i][1], ctx, markerOptions);
                    }
                }
            }
        }
        
        ctx.restore();
    };  
    
    $.jqplot.LineRenderer.prototype.drawShadow = function(ctx, gd, options) {
        // This is a no-op, shadows drawn with lines.
    };
    
    // called with scope of plot.
    // make sure to not leave anything highlighted.
    function postInit(target, data, options) {
        for (var i=0; i<this.series.length; i++) {
            if (this.series[i].renderer.constructor == $.jqplot.LineRenderer) {
                // don't allow mouseover and mousedown at same time.
                if (this.series[i].highlightMouseOver) {
                    this.series[i].highlightMouseDown = false;
                }
            }
        }
    }  
    
    // called within context of plot
    // create a canvas which we can draw on.
    // insert it before the eventCanvas, so eventCanvas will still capture events.
    function postPlotDraw() {
        // Memory Leaks patch    
        if (this.plugins.lineRenderer && this.plugins.lineRenderer.highlightCanvas) {
          this.plugins.lineRenderer.highlightCanvas.resetCanvas();
          this.plugins.lineRenderer.highlightCanvas = null;
        }
        
        this.plugins.lineRenderer.highlightedSeriesIndex = null;
        this.plugins.lineRenderer.highlightCanvas = new $.jqplot.GenericCanvas();
        
        this.eventCanvas._elem.before(this.plugins.lineRenderer.highlightCanvas.createElement(this._gridPadding, 'jqplot-lineRenderer-highlight-canvas', this._plotDimensions, this));
        this.plugins.lineRenderer.highlightCanvas.setContext();
        this.eventCanvas._elem.bind('mouseleave', {plot:this}, function (ev) { unhighlight(ev.data.plot); });
    } 
    
    function highlight (plot, sidx, pidx, points) {
        var s = plot.series[sidx];
        var canvas = plot.plugins.lineRenderer.highlightCanvas;
        canvas._ctx.clearRect(0,0,canvas._ctx.canvas.width, canvas._ctx.canvas.height);
        s._highlightedPoint = pidx;
        plot.plugins.lineRenderer.highlightedSeriesIndex = sidx;
        var opts = {fillStyle: s.highlightColor};
        if (s.type === 'line' && s.renderer.bands.show) {
            opts.fill = true;
            opts.closePath = true;
        }
        s.renderer.shapeRenderer.draw(canvas._ctx, points, opts);
        canvas = null;
    }
    
    function unhighlight (plot) {
        var canvas = plot.plugins.lineRenderer.highlightCanvas;
        canvas._ctx.clearRect(0,0, canvas._ctx.canvas.width, canvas._ctx.canvas.height);
        for (var i=0; i<plot.series.length; i++) {
            plot.series[i]._highlightedPoint = null;
        }
        plot.plugins.lineRenderer.highlightedSeriesIndex = null;
        plot.target.trigger('jqplotDataUnhighlight');
        canvas = null;
    }
    
    
    function handleMove(ev, gridpos, datapos, neighbor, plot) {
        if (neighbor) {
            var ins = [neighbor.seriesIndex, neighbor.pointIndex, neighbor.data];
            var evt1 = jQuery.Event('jqplotDataMouseOver');
            evt1.pageX = ev.pageX;
            evt1.pageY = ev.pageY;
            plot.target.trigger(evt1, ins);
            if (plot.series[ins[0]].highlightMouseOver && !(ins[0] == plot.plugins.lineRenderer.highlightedSeriesIndex)) {
                var evt = jQuery.Event('jqplotDataHighlight');
                evt.which = ev.which;
                evt.pageX = ev.pageX;
                evt.pageY = ev.pageY;
                plot.target.trigger(evt, ins);
                highlight (plot, neighbor.seriesIndex, neighbor.pointIndex, neighbor.points);
            }
        }
        else if (neighbor == null) {
            unhighlight (plot);
        }
    }
    
    function handleMouseDown(ev, gridpos, datapos, neighbor, plot) {
        if (neighbor) {
            var ins = [neighbor.seriesIndex, neighbor.pointIndex, neighbor.data];
            if (plot.series[ins[0]].highlightMouseDown && !(ins[0] == plot.plugins.lineRenderer.highlightedSeriesIndex)) {
                var evt = jQuery.Event('jqplotDataHighlight');
                evt.which = ev.which;
                evt.pageX = ev.pageX;
                evt.pageY = ev.pageY;
                plot.target.trigger(evt, ins);
                highlight (plot, neighbor.seriesIndex, neighbor.pointIndex, neighbor.points);
            }
        }
        else if (neighbor == null) {
            unhighlight (plot);
        }
    }
    
    function handleMouseUp(ev, gridpos, datapos, neighbor, plot) {
        var idx = plot.plugins.lineRenderer.highlightedSeriesIndex;
        if (idx != null && plot.series[idx].highlightMouseDown) {
            unhighlight(plot);
        }
    }
    
    function handleClick(ev, gridpos, datapos, neighbor, plot) {
        if (neighbor) {
            var ins = [neighbor.seriesIndex, neighbor.pointIndex, neighbor.data];
            var evt = jQuery.Event('jqplotDataClick');
            evt.which = ev.which;
            evt.pageX = ev.pageX;
            evt.pageY = ev.pageY;
            plot.target.trigger(evt, ins);
        }
    }
    
    function handleRightClick(ev, gridpos, datapos, neighbor, plot) {
        if (neighbor) {
            var ins = [neighbor.seriesIndex, neighbor.pointIndex, neighbor.data];
            var idx = plot.plugins.lineRenderer.highlightedSeriesIndex;
            if (idx != null && plot.series[idx].highlightMouseDown) {
                unhighlight(plot);
            }
            var evt = jQuery.Event('jqplotDataRightClick');
            evt.which = ev.which;
            evt.pageX = ev.pageX;
            evt.pageY = ev.pageY;
            plot.target.trigger(evt, ins);
        }
    }
    
})(jQuery);    
