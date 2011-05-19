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
    // class $.jqplot.EnhancedLegendRenderer
    // Legend renderer which can specify the number of rows and/or columns in the legend.
    $.jqplot.EnhancedLegendRenderer = function(){
        $.jqplot.TableLegendRenderer.call(this);
    };
    
    $.jqplot.EnhancedLegendRenderer.prototype = new $.jqplot.TableLegendRenderer();
    $.jqplot.EnhancedLegendRenderer.prototype.constructor = $.jqplot.EnhancedLegendRenderer;
    
    // called with scope of legend.
    $.jqplot.EnhancedLegendRenderer.prototype.init = function(options) {
        // prop: numberRows
        // Maximum number of rows in the legend.  0 or null for unlimited.
        this.numberRows = null;
        // prop: numberColumns
        // Maximum number of columns in the legend.  0 or null for unlimited.
        this.numberColumns = null;
        // prop: seriesToggle
        // false to not enable series on/off toggling on the legend.
        // true or a fadein/fadeout speed (number of milliseconds or 'fast', 'normal', 'slow') 
        // to enable show/hide of series on click of legend item.
        this.seriesToggle = 'normal';
        // prop: disableIEFading
        // true to toggle series with a show/hide method only and not allow fading in/out.  
        // This is to overcome poor performance of fade in some versions of IE.
        this.disableIEFading = true;
        $.extend(true, this, options);
        
        if (this.seriesToggle) {
            $.jqplot.postDrawHooks.push(postDraw);
        }
    };
    
    // called with scope of legend
    $.jqplot.EnhancedLegendRenderer.prototype.draw = function() {
        var legend = this;
        if (this.show) {
            var series = this._series;
			var s;
            var ss = 'position:absolute;';
            ss += (this.background) ? 'background:'+this.background+';' : '';
            ss += (this.border) ? 'border:'+this.border+';' : '';
            ss += (this.fontSize) ? 'font-size:'+this.fontSize+';' : '';
            ss += (this.fontFamily) ? 'font-family:'+this.fontFamily+';' : '';
            ss += (this.textColor) ? 'color:'+this.textColor+';' : '';
            ss += (this.marginTop != null) ? 'margin-top:'+this.marginTop+';' : '';
            ss += (this.marginBottom != null) ? 'margin-bottom:'+this.marginBottom+';' : '';
            ss += (this.marginLeft != null) ? 'margin-left:'+this.marginLeft+';' : '';
            ss += (this.marginRight != null) ? 'margin-right:'+this.marginRight+';' : '';
            this._elem = $('<table class="jqplot-table-legend" style="'+ss+'"></table>');
            if (this.seriesToggle) {
                this._elem.css('z-index', '3');
            }
        
            var pad = false, 
                reverse = false,
                nr, nc;
            if (this.numberRows) {
                nr = this.numberRows;
                if (!this.numberColumns){
                    nc = Math.ceil(series.length/nr);
                }
                else{
                    nc = this.numberColumns;
                }
            }
            else if (this.numberColumns) {
                nc = this.numberColumns;
                nr = Math.ceil(series.length/this.numberColumns);
            }
            else {
                nr = series.length;
                nc = 1;
            }
                
            var i, j, tr, td1, td2, lt, rs;
            var idx = 0;
            // check to see if we need to reverse
            for (i=series.length-1; i>=0; i--) {
                if (series[i]._stack || series[i].renderer.constructor == $.jqplot.BezierCurveRenderer){
                    reverse = true;
                }
            }    
                
            for (i=0; i<nr; i++) {
                if (reverse){
                    tr = $('<tr class="jqplot-table-legend"></tr>').prependTo(this._elem);
                }
                else{
                    tr = $('<tr class="jqplot-table-legend"></tr>').appendTo(this._elem);
                }
                for (j=0; j<nc; j++) {
                    if (idx < series.length && series[idx].show && series[idx].showLabel){
                        s = series[idx];
                        lt = this.labels[idx] || s.label.toString();
                        if (lt) {
                            var color = s.color;
                            if (!reverse){
                                if (i>0){
                                    pad = true;
                                }
                                else{
                                    pad = false;
                                }
                            }
                            else{
                                if (i == nr -1){
                                    pad = false;
                                }
                                else{
                                    pad = true;
                                }
                            }
                            rs = (pad) ? this.rowSpacing : '0';
                    
                            td1 = $('<td class="jqplot-table-legend" style="text-align:center;padding-top:'+rs+';">'+
                                '<div><div class="jqplot-table-legend-swatch" style="background-color:'+color+';border-color:'+color+';"></div>'+
                                '</div></td>');
                            td2 = $('<td class="jqplot-table-legend" style="padding-top:'+rs+';"></td>');
                            if (this.escapeHtml){
                                td2.text(lt);
                            }
                            else {
                                td2.html(lt);
                            }
                            if (reverse) {
                                if (this.showLabels) {td2.prependTo(tr);}
                                if (this.showSwatches) {td1.prependTo(tr);}
                            }
                            else {
                                if (this.showSwatches) {td1.appendTo(tr);}
                                if (this.showLabels) {td2.appendTo(tr);}
                            }
                            
                            if (this.seriesToggle) {
                                var speed;
                                if (typeof(this.seriesToggle) == 'string' || typeof(this.seriesToggle) == 'number') {
                                    if (!$.browser.msie || !this.disableIEFading) {
                                        speed = this.seriesToggle;
                                    }
                                } 
                                if (this.showSwatches) {
                                    td1.bind('click', {series:s, speed:speed}, s.toggleDisplay);
                                    td1.addClass('jqplot-seriesToggle');
                                }
                                if (this.showLabels)  {
                                    td2.bind('click', {series:s, speed:speed}, s.toggleDisplay);
                                    td2.addClass('jqplot-seriesToggle');
                                }
                            }
                            
                            pad = true;
                        }
                    }
                    idx++;
                }   
            }
        }
        return this._elem;
    };
    
    // called with scope of plot.
    var postDraw = function () {
        if (this.legend.renderer.constructor == $.jqplot.EnhancedLegendRenderer && this.legend.seriesToggle){
            var e = this.legend._elem.detach();
            this.eventCanvas._elem.after(e);
        }
    };
})(jQuery);