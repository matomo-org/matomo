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
    // class: $.jqplot.MarkerRenderer
    // The default jqPlot marker renderer, rendering the points on the line.
    $.jqplot.MarkerRenderer = function(options){
        // Group: Properties
        
        // prop: show
        // whether or not to show the marker.
        this.show = true;
        // prop: style
        // One of diamond, circle, square, x, plus, dash, filledDiamond, filledCircle, filledSquare
        this.style = 'filledCircle';
        // prop: lineWidth
        // size of the line for non-filled markers.
        this.lineWidth = 2;
        // prop: size
        // Size of the marker (diameter or circle, length of edge of square, etc.)
        this.size = 9.0;
        // prop: color
        // color of marker.  Will be set to color of series by default on init.
        this.color = '#666666';
        // prop: shadow
        // whether or not to draw a shadow on the line
        this.shadow = true;
        // prop: shadowAngle
        // Shadow angle in degrees
        this.shadowAngle = 45;
        // prop: shadowOffset
        // Shadow offset from line in pixels
        this.shadowOffset = 1;
        // prop: shadowDepth
        // Number of times shadow is stroked, each stroke offset shadowOffset from the last.
        this.shadowDepth = 3;
        // prop: shadowAlpha
        // Alpha channel transparency of shadow.  0 = transparent.
        this.shadowAlpha = '0.07';
        // prop: shadowRenderer
        // Renderer that will draws the shadows on the marker.
        this.shadowRenderer = new $.jqplot.ShadowRenderer();
        // prop: shapeRenderer
        // Renderer that will draw the marker.
        this.shapeRenderer = new $.jqplot.ShapeRenderer();
        
        $.extend(true, this, options);
    };
    
    function getShadowRendererOptions(opts) {
        var sdopt = {angle:opts.shadowAngle, offset:opts.shadowOffset, alpha:opts.shadowAlpha, lineWidth:opts.lineWidth, depth:opts.shadowDepth, closePath:true};
        if (opts.style.indexOf('filled') != -1) {
            sdopt.fill = true;
        }
        if (opts.style.indexOf('ircle') != -1) {
            sdopt.isarc = true;
            sdopt.closePath = false;
        }
        return $.extend(true, {}, sdopt);
    }
    
    function getShapeRendererOptions(opts) {
        var shopt = {fill:false, isarc:false, strokeStyle:opts.color, fillStyle:opts.color, lineWidth:opts.lineWidth, closePath:true};
        if (opts.style.indexOf('filled') != -1) {
            shopt.fill = true;
        }
        if (opts.style.indexOf('ircle') != -1) {
            shopt.isarc = true;
            shopt.closePath = false;
        }
        return $.extend(true, {}, shopt);
    }
    
    $.jqplot.MarkerRenderer.prototype.init = function(options) {
        $.extend(true, this, options);
    };
    
    $.jqplot.MarkerRenderer.prototype.drawDiamond = function(x, y, ctx, fill, options) {
        var opts;
        if (options == null || $.isEmptyObject(options)) {
            opts = this;
        } else {
            opts = $.extend(true, {}, this, options);
        }
        var stretch = 1.2;
        var dx = this.size/2/stretch;
        var dy = this.size/2*stretch;
        var points = [[x-dx, y], [x, y+dy], [x+dx, y], [x, y-dy]];
        if (opts.shadow) {
            this.shadowRenderer.draw(ctx, points, getShadowRendererOptions(opts));
        }
        this.shapeRenderer.draw(ctx, points, getShapeRendererOptions(opts));
    };
    
    $.jqplot.MarkerRenderer.prototype.drawPlus = function(x, y, ctx, fill, options) {
        var opts = $.extend(true, {}, this, options, {closePath:false});
        var stretch = 1.0;
        var dx = opts.size/2*stretch;
        var dy = opts.size/2*stretch;
        var points1 = [[x, y-dy], [x, y+dy]];
        var points2 = [[x+dx, y], [x-dx, y]];
        if (opts.shadow) {
            this.shadowRenderer.draw(ctx, points1, getShadowRendererOptions(opts));
            this.shadowRenderer.draw(ctx, points2, getShadowRendererOptions(opts));
        }
        this.shapeRenderer.draw(ctx, points1, opts);
        this.shapeRenderer.draw(ctx, points2, opts);
    };
    
    $.jqplot.MarkerRenderer.prototype.drawX = function(x, y, ctx, fill, options) {
        var opts = $.extend(true, {}, this, options, {closePath:false});
        var stretch = 1.0;
        var dx = opts.size/2*stretch;
        var dy = opts.size/2*stretch;
        var points1 = [[x-dx, y-dy], [x+dx, y+dy]];
        var points2 = [[x-dx, y+dy], [x+dx, y-dy]];
        if (opts.shadow) {
            this.shadowRenderer.draw(ctx, points1, getShadowRendererOptions(opts));
            this.shadowRenderer.draw(ctx, points2, getShadowRendererOptions(opts));
        }
        this.shapeRenderer.draw(ctx, points1, getShapeRendererOptions(opts));
        this.shapeRenderer.draw(ctx, points2, getShapeRendererOptions(opts));
    };
    
    $.jqplot.MarkerRenderer.prototype.drawDash = function(x, y, ctx, fill, options) {
        var opts;
        if (options == null || $.isEmptyObject(options)) {
            opts = this;
        } else {
            opts = $.extend(true, {}, this, options);
        }
        var stretch = 1.0;
        var dx = this.size/2*stretch;
        var dy = this.size/2*stretch;
        var points = [[x-dx, y], [x+dx, y]];
        if (opts.shadow) {
            this.shadowRenderer.draw(ctx, points);
        }
        this.shapeRenderer.draw(ctx, points, getShapeRendererOptions(opts));
    };
    
    $.jqplot.MarkerRenderer.prototype.drawLine = function(p1, p2, ctx, fill, options) {
        var opts;
        if (options == null || $.isEmptyObject(options)) {
            opts = this;
        } else {
            opts = $.extend(true, {}, this, options);
        }
        var points = [p1, p2];
        if (opts.shadow) {
            this.shadowRenderer.draw(ctx, points, getShadowRendererOptions(opts));
        }
        this.shapeRenderer.draw(ctx, points, getShapeRendererOptions(opts));
    };
    
    $.jqplot.MarkerRenderer.prototype.drawSquare = function(x, y, ctx, fill, options) {
        var opts;
        if (options == null || $.isEmptyObject(options)) {
            opts = this;
        } else {
            opts = $.extend(true, {}, this, options);
        }
        var stretch = 1.0;
        var dx = this.size/2/stretch;
        var dy = this.size/2*stretch;
        var points = [[x-dx, y-dy], [x-dx, y+dy], [x+dx, y+dy], [x+dx, y-dy]];
        if (opts.shadow) {
            this.shadowRenderer.draw(ctx, points, getShadowRendererOptions(opts));
        }
        this.shapeRenderer.draw(ctx, points, getShapeRendererOptions(opts));
    };
    
    $.jqplot.MarkerRenderer.prototype.drawCircle = function(x, y, ctx, fill, options) {
        var opts;
        if (options == null || $.isEmptyObject(options)) {
            opts = this;
        } else {
            opts = $.extend(true, {}, this, options);
        }
        var radius = this.size/2;
        var end = 2*Math.PI;
        var points = [x, y, radius, 0, end, true];
        if (opts.shadow) {
            this.shadowRenderer.draw(ctx, points, getShadowRendererOptions(opts));
        }
        this.shapeRenderer.draw(ctx, points, getShapeRendererOptions(opts));
    };
    
    $.jqplot.MarkerRenderer.prototype.draw = function(x, y, ctx, options) {
        options = options || {};
        // hack here b/c shape renderer uses canvas based color style options
        // and marker uses css style names.
        if (options.show == null || options.show != false) {
            if (options.color && !options.fillStyle) {
                options.fillStyle = options.color;
            }
            if (options.color && !options.strokeStyle) {
                options.strokeStyle = options.color;
            }
            var style = options.style || this.style;
            switch (style) {
                case 'diamond':
                    this.drawDiamond(x,y,ctx, false, options);
                    break;
                case 'filledDiamond':
                    this.drawDiamond(x,y,ctx, true, options);
                    break;
                case 'circle':
                    this.drawCircle(x,y,ctx, false, options);
                    break;
                case 'filledCircle':
                    this.drawCircle(x,y,ctx, true, options);
                    break;
                case 'square':
                    this.drawSquare(x,y,ctx, false, options);
                    break;
                case 'filledSquare':
                    this.drawSquare(x,y,ctx, true, options);
                    break;
                case 'x':
                    this.drawX(x,y,ctx, true, options);
                    break;
                case 'plus':
                    this.drawPlus(x,y,ctx, true, options);
                    break;
                case 'dash':
                    this.drawDash(x,y,ctx, true, options);
                    break;
                case 'line':
                    this.drawLine(x, y, ctx, false, options);
                    break;
                default:
                    this.drawDiamond(x,y,ctx, false, options);
                    break;
            }
        }
    };
})(jQuery);    
