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
    *  class: $.jqplot.LogAxisRenderer
    *  A plugin for a jqPlot to render a logarithmic axis.
    * 
    *  To use this renderer, include the plugin in your source
    *  > <script type="text/javascript" language="javascript" src="plugins/jqplot.logAxisRenderer.js"></script>
    *  
    *  and supply the appropriate options to your plot
    *  
    *  > {axes:{xaxis:{renderer:$.jqplot.LogAxisRenderer}}}
    **/ 
    $.jqplot.LogAxisRenderer = function() {
        $.jqplot.LinearAxisRenderer.call(this);
        // prop: axisDefaults
        // Default properties which will be applied directly to the series.
        //
        // Group: Properties
        //
        // Properties
        //
        /// base - the logarithmic base, commonly 2, 10 or Math.E
        // tickDistribution - 'even' or 'power'.  'even' gives equal pixel
        // spacing of the ticks on the plot.  'power' gives ticks in powers
        // of 10.
        this.axisDefaults = {
            base : 10,
            tickDistribution :'even'
        };
    };
    
    $.jqplot.LogAxisRenderer.prototype = new $.jqplot.LinearAxisRenderer();
    $.jqplot.LogAxisRenderer.prototype.constructor = $.jqplot.LogAxisRenderer;
    
    $.jqplot.LogAxisRenderer.prototype.init = function(options) {
        // prop: tickRenderer
        // A class of a rendering engine for creating the ticks labels displayed on the plot, 
        // See <$.jqplot.AxisTickRenderer>.
        // this.tickRenderer = $.jqplot.AxisTickRenderer;
        // this.labelRenderer = $.jqplot.AxisLabelRenderer;
        $.extend(true, this.renderer, options);
        for (var d in this.renderer.axisDefaults) {
            if (this[d] == null) {
                this[d] = this.renderer.axisDefaults[d];
            }
        }
        var db = this._dataBounds;
        // Go through all the series attached to this axis and find
        // the min/max bounds for this axis.
        for (var i=0; i<this._series.length; i++) {
            var s = this._series[i];
            var d = s.data;
            
            for (var j=0; j<d.length; j++) { 
                if (this.name == 'xaxis' || this.name == 'x2axis') {
                    if ((d[j][0] != null && d[j][0] < db.min) || db.min == null) {
                        db.min = d[j][0];
                    }
                    if ((d[j][0] != null && d[j][0] > db.max) || db.max == null) {
                        db.max = d[j][0];
                    }
                }              
                else {
                    if ((d[j][1] != null && d[j][1] < db.min) || db.min == null) {
                        db.min = d[j][1];
                    }
                    if ((d[j][1] != null && d[j][1] > db.max) || db.max == null) {
                        db.max = d[j][1];
                    }
                }               
            }
        }
    };
    
    $.jqplot.LogAxisRenderer.prototype.createTicks = function() {
        // we're are operating on an axis here
        var ticks = this._ticks;
        var userTicks = this.ticks;
        var name = this.name;
        var db = this._dataBounds;
        var dim, interval;
        var min, max;
        var pos1, pos2;
        var tt, i;

        // if we already have ticks, use them.
        // ticks must be in order of increasing value.
        if (userTicks.length) {
            // ticks could be 1D or 2D array of [val, val, ,,,] or [[val, label], [val, label], ...] or mixed
            for (i=0; i<userTicks.length; i++){
                var ut = userTicks[i];
                var t = new this.tickRenderer(this.tickOptions);
                if (ut.constructor == Array) {
                    t.value = ut[0];
                    t.label = ut[1];
                    if (!this.showTicks) {
                        t.showLabel = false;
                        t.showMark = false;
                    }
                    else if (!this.showTickMarks) {
                        t.showMark = false;
                    }
                    t.setTick(ut[0], this.name);
                    this._ticks.push(t);
                }
                
                else {
                    t.value = ut;
                    if (!this.showTicks) {
                        t.showLabel = false;
                        t.showMark = false;
                    }
                    else if (!this.showTickMarks) {
                        t.showMark = false;
                    }
                    t.setTick(ut, this.name);
                    this._ticks.push(t);
                }
            }
            this.numberTicks = userTicks.length;
            this.min = this._ticks[0].value;
            this.max = this._ticks[this.numberTicks-1].value;
        }
        
        // we don't have any ticks yet, let's make some!
        else {
            if (name == 'xaxis' || name == 'x2axis') {
                dim = this._plotDimensions.width;
            }
            else {
                dim = this._plotDimensions.height;
            }
        
            min = ((this.min != null) ? this.min : db.min);
            max = ((this.max != null) ? this.max : db.max);
            
            // if min and max are same, space them out a bit
            if (min == max) {
                var adj = 0.05;
                min = min*(1-adj);
                max = max*(1+adj);
            }
            
            // perform some checks
            if (this.min != null && this.min <= 0) {
                throw('log axis minimum must be greater than 0');
            }
            if (this.max != null && this.max <= 0) {
                throw('log axis maximum must be greater than 0');
            }
            // if (this.pad >1.99) this.pad = 1.99;
            var range = max - min;
            var rmin, rmax;

            if (this.tickDistribution == 'even') {                    
                rmin = (this.min != null) ? this.min : min - min*((this.padMin-1)/2);
                rmax = (this.max != null) ? this.max : max + max*((this.padMax-1)/2);
                this.min = rmin;
                this.max = rmax;
                range = this.max - this.min;            
        
                if (this.numberTicks == null){
                    if (dim > 100) {
                        this.numberTicks = parseInt(3+(dim-100)/75, 10);
                    }
                    else {
                        this.numberTicks = 2;
                    }
                }
    
                var u = Math.pow(this.base, (1/(this.numberTicks-1)*Math.log(this.max/this.min)/Math.log(this.base)));
                for (var i=0; i<this.numberTicks; i++){
                    tt = this.min * Math.pow(u, i);
                    var t = new this.tickRenderer(this.tickOptions);
                    if (!this.showTicks) {
                        t.showLabel = false;
                        t.showMark = false;
                    }
                    else if (!this.showTickMarks) {
                        t.showMark = false;
                    }
                    t.setTick(tt, this.name);
                    this._ticks.push(t);
                }
                
            }
            
            else if (this.tickDistribution == 'power'){
                // for power distribution, open up range to get a nice power of axis.renderer.base.
                // power distribution won't respect the user's min/max settings.
                rmin = Math.pow(this.base, Math.ceil(Math.log(min*(2-this.padMin))/Math.log(this.base))-1);
                rmax = Math.pow(this.base, Math.floor(Math.log(max*this.padMax)/Math.log(this.base))+1);
                this.min = rmin;
                this.max = rmax;
                range = this.max - this.min;            
        
                var fittedTicks = 0;
                var minorTicks = 0;
                if (this.numberTicks == null){
                    if (dim > 100) {
                        this.numberTicks = Math.round(Math.log(this.max/this.min)/Math.log(this.base) + 1);
                        if (this.numberTicks < 2) {
                            this.numberTicks = 2;
                        }
                        fittedTicks = parseInt(3+(dim-100)/75, 10);
                    }
                    else {
                        this.numberTicks = 2;
                        fittedTicks = 2;
                    }
                    // if we don't have enough ticks, add some intermediate ticks
                    // how many to have between major ticks.
                    if (this.numberTicks < fittedTicks-1) {
                        minorTicks = Math.floor(fittedTicks/this.numberTicks);
                    }
                }

                for (var i=0; i<this.numberTicks; i++){
                    tt = Math.pow(this.base, i - this.numberTicks + 1) * this.max;
                    var t = new this.tickRenderer(this.tickOptions);
                    if (!this.showTicks) {
                        t.showLabel = false;
                        t.showMark = false;
                    }
                    else if (!this.showTickMarks) {
                        t.showMark = false;
                    }
                    t.setTick(tt, this.name);
                    this._ticks.push(t);
            
                    if (minorTicks && i<this.numberTicks-1) {
                        var tt1 = Math.pow(this.base, i - this.numberTicks + 2) * this.max;
                        var spread = tt1 - tt;
                        var interval = tt1 / (minorTicks+1);
                        for (var j=minorTicks-1; j>=0; j--) {
                            var val = tt1-interval*(j+1);
                            var t = new this.tickRenderer(this.tickOptions);
                            if (!this.showTicks) {
                                t.showLabel = false;
                                t.showMark = false;
                            }
                            else if (!this.showTickMarks) {
                                t.showMark = false;
                            }
                            t.setTick(val, this.name);
                            this._ticks.push(t);
                        }
                    }       
                }                    
            }       
        }
    };
    
    $.jqplot.LogAxisRenderer.prototype.pack = function(pos, offsets) {
        var lb = parseInt(this.base, 10);
        var ticks = this._ticks;
        var trans = function (v) { return Math.log(v)/Math.log(lb); };
        var invtrans = function (v) { return Math.pow(Math.E, (Math.log(lb)*v)); };
        var max = trans(this.max);
        var min = trans(this.min);
        var offmax = offsets.max;
        var offmin = offsets.min;
        var lshow = (this._label == null) ? false : this._label.show;
        
        for (var p in pos) {
            this._elem.css(p, pos[p]);
        }
        
        this._offsets = offsets;
        // pixellength will be + for x axes and - for y axes becasue pixels always measured from top left.
        var pixellength = offmax - offmin;
        var unitlength = max - min;
        
        // point to unit and unit to point conversions references to Plot DOM element top left corner.
        this.p2u = function(p){
            return invtrans((p - offmin) * unitlength / pixellength + min);
        };
        
        this.u2p = function(u){
            return (trans(u) - min) * pixellength / unitlength + offmin;
        };
        
        if (this.name == 'xaxis' || this.name == 'x2axis'){
            this.series_u2p = function(u){
                return (trans(u) - min) * pixellength / unitlength;
            };
            this.series_p2u = function(p){
                return invtrans(p * unitlength / pixellength + min);
            };
        }
        // yaxis is max at top of canvas.
        else {
            this.series_u2p = function(u){
                return (trans(u) - max) * pixellength / unitlength;
            };
            this.series_p2u = function(p){
                return invtrans(p * unitlength / pixellength + max);
            };
        }
        
        if (this.show) {
            if (this.name == 'xaxis' || this.name == 'x2axis') {
                for (var i=0; i<ticks.length; i++) {
                    var t = ticks[i];
                    if (t.show && t.showLabel) {
                        var shim;
                        
                        if (t.constructor == $.jqplot.CanvasAxisTickRenderer && t.angle) {
                            switch (t.labelPosition) {
                                case 'auto':
                                    // position at end
                                    if (t.angle < 0) {
                                        shim = -t.getWidth() + t._textRenderer.height * Math.sin(-t._textRenderer.angle) / 2;
                                    }
                                    // position at start
                                    else {
                                        shim = -t._textRenderer.height * Math.sin(t._textRenderer.angle) / 2;
                                    }
                                    break;
                                case 'end':
                                    shim = -t.getWidth() + t._textRenderer.height * Math.sin(-t._textRenderer.angle) / 2;
                                    break;
                                case 'start':
                                    shim = -t._textRenderer.height * Math.sin(t._textRenderer.angle) / 2;
                                    break;
                                case 'middle':
                                    shim = -t.getWidth()/2 + t._textRenderer.height * Math.sin(-t._textRenderer.angle) / 2;
                                    break;
                                default:
                                    shim = -t.getWidth()/2 + t._textRenderer.height * Math.sin(-t._textRenderer.angle) / 2;
                                    break;
                            }
                        }
                        else {
                            shim = -t.getWidth()/2;
                        }
                        // var shim = t.getWidth()/2;
                        var val = this.u2p(t.value) + shim + 'px';
                        t._elem.css('left', val);
                        t.pack();
                    }
                }
                if (lshow) {
                    var w = this._label._elem.outerWidth(true);
                    this._label._elem.css('left', offmin + pixellength/2 - w/2 + 'px');
                    if (this.name == 'xaxis') {
                        this._label._elem.css('bottom', '0px');
                    }
                    else {
                        this._label._elem.css('top', '0px');
                    }
                    this._label.pack();
                }
            }
            else {
                for (var i=0; i<ticks.length; i++) {
                    var t = ticks[i];
                    if (t.show && t.showLabel) {                        
                        var shim;
                        if (t.constructor == $.jqplot.CanvasAxisTickRenderer && t.angle) {
                            switch (t.labelPosition) {
                                case 'auto':
                                    // position at end
                                case 'end':
                                    if (t.angle < 0) {
                                        shim = -t._textRenderer.height * Math.cos(-t._textRenderer.angle) / 2;
                                    }
                                    else {
                                        shim = -t.getHeight() + t._textRenderer.height * Math.cos(t._textRenderer.angle) / 2;
                                    }
                                    break;
                                case 'start':
                                    if (t.angle > 0) {
                                        shim = -t._textRenderer.height * Math.cos(-t._textRenderer.angle) / 2;
                                    }
                                    else {
                                        shim = -t.getHeight() + t._textRenderer.height * Math.cos(t._textRenderer.angle) / 2;
                                    }
                                    break;
                                case 'middle':
                                    // if (t.angle > 0) {
                                    //     shim = -t.getHeight()/2 + t._textRenderer.height * Math.sin(-t._textRenderer.angle) / 2;
                                    // }
                                    // else {
                                    //     shim = -t.getHeight()/2 - t._textRenderer.height * Math.sin(t._textRenderer.angle) / 2;
                                    // }
                                    shim = -t.getHeight()/2;
                                    break;
                                default:
                                    shim = -t.getHeight()/2;
                                    break;
                            }
                        }
                        else {
                            shim = -t.getHeight()/2;
                        }
                        
                        var val = this.u2p(t.value) + shim + 'px';
                        t._elem.css('top', val);
                        t.pack();
                    }
                }
                if (lshow) {
                    var h = this._label._elem.outerHeight(true);
                    this._label._elem.css('top', offmax - pixellength/2 - h/2 + 'px');
                    if (this.name == 'yaxis') {
                        this._label._elem.css('left', '0px');
                    }
                    else {
                        this._label._elem.css('right', '0px');
                    }   
                    this._label.pack();
                }
            }
        }        
    };
})(jQuery);