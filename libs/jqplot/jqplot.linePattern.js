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

 /**
  * The following dashed line support contributed by Cory Sharp.
  * After I implemented an inferior method, Cory responded with a generous
  * contribution of code and input which proved a more powerful and
  * elegant solution.
  */
    
(function($) {

    var dotlen = 0.1;

    $.jqplot.LinePattern = function (ctx, pattern) {

        var defaultLinePatterns = {
            dotted: [ dotlen, $.jqplot.config.dotGapLength ],
            dashed: [ $.jqplot.config.dashLength, $.jqplot.config.gapLength ],
            solid: null
        };

        if (typeof pattern === 'string') {
            if (pattern[0] === '.' || pattern[0] === '-') {
                var s = pattern;
                pattern = [];
                for (var i=0, imax=s.length; i<imax; i++) {
                    if (s[i] === '.') {
                        pattern.push( dotlen );
                    }
                    else if (s[i] === '-') {
                        pattern.push( $.jqplot.config.dashLength );
                    }
                    else {
                        continue;
                    }
                    pattern.push( $.jqplot.config.gapLength );
                }
            }
            else {
                pattern = defaultLinePatterns[pattern];
            }
        }

        if (!(pattern && pattern.length)) {
            return ctx;
        }

        var patternIndex = 0;
        var patternDistance = pattern[0];
        var px = 0;
        var py = 0;
        var pathx0 = 0;
        var pathy0 = 0;

        var moveTo = function (x, y) {
            ctx.moveTo( x, y );
            px = x;
            py = y;
            pathx0 = x;
            pathy0 = y;
        };

        var lineTo = function (x, y) {
            var scale = ctx.lineWidth;
            var dx = x - px;
            var dy = y - py;
            var dist = Math.sqrt(dx*dx+dy*dy);
            if ((dist > 0) && (scale > 0)) {
                dx /= dist;
                dy /= dist;
                while (true) {
                    var dp = scale * patternDistance;
                    if (dp < dist) {
                        px += dp * dx;
                        py += dp * dy;
                        if ((patternIndex & 1) == 0) {
                            ctx.lineTo( px, py );
                        }
                        else {
                            ctx.moveTo( px, py );
                        }
                        dist -= dp;
                        patternIndex++;
                        if (patternIndex >= pattern.length) {
                            patternIndex = 0;
                        }
                        patternDistance = pattern[patternIndex];
                    }
                    else {
                        px = x;
                        py = y;
                        if ((patternIndex & 1) == 0) {
                            ctx.lineTo( px, py );
                        }
                        else {
                            ctx.moveTo( px, py );
                        }
                        patternDistance -= dist / scale;
                        break;
                    }
                }
            }
        };

        var beginPath = function () {
            ctx.beginPath();
        };

        var closePath = function () {
            lineTo( pathx0, pathy0 );
        };

        return {
            moveTo: moveTo,
            lineTo: lineTo,
            beginPath: beginPath,
            closePath: closePath
        };
    };
})(jQuery);