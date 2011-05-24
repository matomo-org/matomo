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
    * The following code was generaously given to me a while back by Scott Prahl.
    * He did a good job at computing axes min, max and number of ticks for the 
    * case where the user has not set any scale related parameters (tickInterval,
    * numberTicks, min or max).  I had ignored this use case for a long time,
    * focusing on the more difficult case where user has set some option controlling
    * tick generation.  Anyway, about time I got this into jqPlot.
    * Thanks Scott!!
    */
    
    /**
    * Copyright (c) 2010 Scott Prahl
    * The next three routines are currently available for use in all personal 
    * or commercial projects under both the MIT and GPL version 2.0 licenses. 
    * This means that you can choose the license that best suits your project 
    * and use it accordingly. 
    */

    // A good format string depends on the interval. If the interval is greater 
    // than 1 then there is no need to show any decimal digits. If it is < 1.0, then
    // use the magnitude of the interval to determine the number of digits to show.
    function bestFormatString (interval)
    {
        interval = Math.abs(interval);
        if (interval > 1) {return '%d';}

        var expv = -Math.floor(Math.log(interval)/Math.LN10);
        return '%.' + expv + 'f'; 
    }

    // This will return an interval of form 2 * 10^n, 5 * 10^n or 10 * 10^n
    function bestLinearInterval(range, scalefact) {
        var expv = Math.floor(Math.log(range)/Math.LN10);
        var magnitude = Math.pow(10, expv);
        // 0 < f < 10
        var f = range / magnitude;
        // console.log('f: %s, scaled: %s ', f, f/scalefact);
        // for large plots, scalefact will decrease f and increase number of ticks.
        // for small plots, scalefact will increase f and decrease number of ticks.
        f = f/scalefact;

        // for large plots, smaller interval, more ticks.
        if (f<=0.38) {return 0.1*magnitude;}
        if (f<=1.6) {return 0.2*magnitude;}
        if (f<=4.0) {return 0.5*magnitude;}
        if (f<=8.0) {return magnitude;}
        // for very small plots, larger interval, less ticks in number ticks
        if (f<=16.0) {return 2*magnitude;}
        return 5*magnitude; 
    }

    // Given the min and max for a dataset, return suitable endpoints
    // for the graphing, a good number for the number of ticks, and a
    // format string so that extraneous digits are not displayed.
    // returned is an array containing [min, max, nTicks, format]
    $.jqplot.LinearTickGenerator = function(axis_min, axis_max, scalefact) {
        // if endpoints are equal try to include zero otherwise include one
        if (axis_min == axis_max) {
        axis_max = (axis_max) ? 0 : 1;
        }

        scalefact = scalefact || 1.0;

        // make sure range is positive
        if (axis_max < axis_min) {
        var a = axis_max;
        axis_max = axis_min;
        axis_min = a;
        }

        var ss = bestLinearInterval(axis_max - axis_min, scalefact);
        var r = [];

        // Figure out the axis min, max and number of ticks
        // the min and max will be some multiple of the tick interval,
        // 1*10^n, 2*10^n or 5*10^n.  This gaurantees that, if the
        // axis min is negative, 0 will be a tick.
        r[0] = Math.floor(axis_min / ss) * ss;  // min
        r[1] = Math.ceil(axis_max / ss) * ss;   // max
        r[2] = Math.round((r[1]-r[0])/ss+1.0);    // number of ticks
        r[3] = bestFormatString(ss);            // format string
        r[4] = ss;                              // tick Interval
        return r;
    };

})(jQuery);