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
    // class $.jqplot.TableLegendRenderer
    // The default legend renderer for jqPlot.
    $.jqplot.TableLegendRenderer = function(){
        //
    };
    
    $.jqplot.TableLegendRenderer.prototype.init = function(options) {
        $.extend(true, this, options);
    };
        
    $.jqplot.TableLegendRenderer.prototype.addrow = function (label, color, pad, reverse) {
        var rs = (pad) ? this.rowSpacing+'px' : '0px';
        var tr;
        var td;
        var elem;
        var div0;
        var div1;
        elem = document.createElement('tr');
        tr = $(elem);
        tr.addClass('jqplot-table-legend');
        elem = null;

        if (reverse){
            tr.prependTo(this._elem);
        }

        else{
            tr.appendTo(this._elem);
        }

        if (this.showSwatches) {
            td = $(document.createElement('td'));
            td.addClass('jqplot-table-legend');
            td.css({textAlign: 'center', paddingTop: rs});

            div0 = $(document.createElement('div'));
            div1 = $(document.createElement('div'));
            div1.addClass('jqplot-table-legend-swatch');
            div1.css({backgroundColor: color, borderColor: color});

            tr.append(td.append(div0.append(div1)));

            // $('<td class="jqplot-table-legend" style="text-align:center;padding-top:'+rs+';">'+
            // '<div><div class="jqplot-table-legend-swatch" style="background-color:'+color+';border-color:'+color+';"></div>'+
            // '</div></td>').appendTo(tr);
        }
        if (this.showLabels) {
            td = $(document.createElement('td'));
            td.addClass('jqplot-table-legend');
            td.css('paddingTop', rs);
            tr.append(td);

            // elem = $('<td class="jqplot-table-legend" style="padding-top:'+rs+';"></td>');
            // elem.appendTo(tr);
            if (this.escapeHtml) {
                td.text(label);
            }
            else {
                td.html(label);
            }
        }
        td = null;
        div0 = null;
        div1 = null;
        tr = null;
        elem = null;
    };
    
    // called with scope of legend
    $.jqplot.TableLegendRenderer.prototype.draw = function() {
        if (this._elem) {
            this._elem.emptyForce();
            this._elem = null;
        }

        if (this.show) {
            var series = this._series;
            // make a table.  one line label per row.
            var elem = document.createElement('table');
            this._elem = $(elem);
            this._elem.addClass('jqplot-table-legend');

            var ss = {position:'absolute'};
            if (this.background) {
                ss['background'] = this.background;
            }
            if (this.border) {
                ss['border'] = this.border;
            }
            if (this.fontSize) {
                ss['fontSize'] = this.fontSize;
            }
            if (this.fontFamily) {
                ss['fontFamily'] = this.fontFamily;
            }
            if (this.textColor) {
                ss['textColor'] = this.textColor;
            }
            if (this.marginTop != null) {
                ss['marginTop'] = this.marginTop;
            }
            if (this.marginBottom != null) {
                ss['marginBottom'] = this.marginBottom;
            }
            if (this.marginLeft != null) {
                ss['marginLeft'] = this.marginLeft;
            }
            if (this.marginRight != null) {
                ss['marginRight'] = this.marginRight;
            }
            
        
            var pad = false, 
                reverse = false,
				s;
            for (var i = 0; i< series.length; i++) {
                s = series[i];
                if (s._stack || s.renderer.constructor == $.jqplot.BezierCurveRenderer){
                    reverse = true;
                }
                if (s.show && s.showLabel) {
                    var lt = this.labels[i] || s.label.toString();
                    if (lt) {
                        var color = s.color;
                        if (reverse && i < series.length - 1){
                            pad = true;
                        }
                        else if (reverse && i == series.length - 1){
                            pad = false;
                        }
                        this.renderer.addrow.call(this, lt, color, pad, reverse);
                        pad = true;
                    }
                    // let plugins add more rows to legend.  Used by trend line plugin.
                    for (var j=0; j<$.jqplot.addLegendRowHooks.length; j++) {
                        var item = $.jqplot.addLegendRowHooks[j].call(this, s);
                        if (item) {
                            this.renderer.addrow.call(this, item.label, item.color, pad);
                            pad = true;
                        } 
                    }
                    lt = null;
                }
            }
        }
        return this._elem;
    };
    
    $.jqplot.TableLegendRenderer.prototype.pack = function(offsets) {
        if (this.show) {       
            if (this.placement == 'insideGrid') {
                switch (this.location) {
                    case 'nw':
                        var a = offsets.left;
                        var b = offsets.top;
                        this._elem.css('left', a);
                        this._elem.css('top', b);
                        break;
                    case 'n':
                        var a = (offsets.left + (this._plotDimensions.width - offsets.right))/2 - this.getWidth()/2;
                        var b = offsets.top;
                        this._elem.css('left', a);
                        this._elem.css('top', b);
                        break;
                    case 'ne':
                        var a = offsets.right;
                        var b = offsets.top;
                        this._elem.css({right:a, top:b});
                        break;
                    case 'e':
                        var a = offsets.right;
                        var b = (offsets.top + (this._plotDimensions.height - offsets.bottom))/2 - this.getHeight()/2;
                        this._elem.css({right:a, top:b});
                        break;
                    case 'se':
                        var a = offsets.right;
                        var b = offsets.bottom;
                        this._elem.css({right:a, bottom:b});
                        break;
                    case 's':
                        var a = (offsets.left + (this._plotDimensions.width - offsets.right))/2 - this.getWidth()/2;
                        var b = offsets.bottom;
                        this._elem.css({left:a, bottom:b});
                        break;
                    case 'sw':
                        var a = offsets.left;
                        var b = offsets.bottom;
                        this._elem.css({left:a, bottom:b});
                        break;
                    case 'w':
                        var a = offsets.left;
                        var b = (offsets.top + (this._plotDimensions.height - offsets.bottom))/2 - this.getHeight()/2;
                        this._elem.css({left:a, top:b});
                        break;
                    default:  // same as 'se'
                        var a = offsets.right;
                        var b = offsets.bottom;
                        this._elem.css({right:a, bottom:b});
                        break;
                }
                
            }
            else if (this.placement == 'outside'){
                switch (this.location) {
                    case 'nw':
                        var a = this._plotDimensions.width - offsets.left;
                        var b = offsets.top;
                        this._elem.css('right', a);
                        this._elem.css('top', b);
                        break;
                    case 'n':
                        var a = (offsets.left + (this._plotDimensions.width - offsets.right))/2 - this.getWidth()/2;
                        var b = this._plotDimensions.height - offsets.top;
                        this._elem.css('left', a);
                        this._elem.css('bottom', b);
                        break;
                    case 'ne':
                        var a = this._plotDimensions.width - offsets.right;
                        var b = offsets.top;
                        this._elem.css({left:a, top:b});
                        break;
                    case 'e':
                        var a = this._plotDimensions.width - offsets.right;
                        var b = (offsets.top + (this._plotDimensions.height - offsets.bottom))/2 - this.getHeight()/2;
                        this._elem.css({left:a, top:b});
                        break;
                    case 'se':
                        var a = this._plotDimensions.width - offsets.right;
                        var b = offsets.bottom;
                        this._elem.css({left:a, bottom:b});
                        break;
                    case 's':
                        var a = (offsets.left + (this._plotDimensions.width - offsets.right))/2 - this.getWidth()/2;
                        var b = this._plotDimensions.height - offsets.bottom;
                        this._elem.css({left:a, top:b});
                        break;
                    case 'sw':
                        var a = this._plotDimensions.width - offsets.left;
                        var b = offsets.bottom;
                        this._elem.css({right:a, bottom:b});
                        break;
                    case 'w':
                        var a = this._plotDimensions.width - offsets.left;
                        var b = (offsets.top + (this._plotDimensions.height - offsets.bottom))/2 - this.getHeight()/2;
                        this._elem.css({right:a, top:b});
                        break;
                    default:  // same as 'se'
                        var a = offsets.right;
                        var b = offsets.bottom;
                        this._elem.css({right:a, bottom:b});
                        break;
                }
            }
            else {
                switch (this.location) {
                    case 'nw':
                        this._elem.css({left:0, top:offsets.top});
                        break;
                    case 'n':
                        var a = (offsets.left + (this._plotDimensions.width - offsets.right))/2 - this.getWidth()/2;
                        this._elem.css({left: a, top:offsets.top});
                        break;
                    case 'ne':
                        this._elem.css({right:0, top:offsets.top});
                        break;
                    case 'e':
                        var b = (offsets.top + (this._plotDimensions.height - offsets.bottom))/2 - this.getHeight()/2;
                        this._elem.css({right:offsets.right, top:b});
                        break;
                    case 'se':
                        this._elem.css({right:offsets.right, bottom:offsets.bottom});
                        break;
                    case 's':
                        var a = (offsets.left + (this._plotDimensions.width - offsets.right))/2 - this.getWidth()/2;
                        this._elem.css({left: a, bottom:offsets.bottom});
                        break;
                    case 'sw':
                        this._elem.css({left:offsets.left, bottom:offsets.bottom});
                        break;
                    case 'w':
                        var b = (offsets.top + (this._plotDimensions.height - offsets.bottom))/2 - this.getHeight()/2;
                        this._elem.css({left:offsets.left, top:b});
                        break;
                    default:  // same as 'se'
                        this._elem.css({right:offsets.right, bottom:offsets.bottom});
                        break;
                }
            }
        } 
    };
})(jQuery);