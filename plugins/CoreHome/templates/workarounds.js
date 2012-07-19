/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function($){

// monkey patch that works around bug in some arc function of some browsers where
// nothing gets drawn if angles are exactly 2 * PI apart.
// affects some versions of chrome & IE 8
var oldArc = CanvasRenderingContext2D.prototype.arc;
CanvasRenderingContext2D.prototype.arc = function(x, y, r, sAngle, eAngle, clockwise) {
	if (Math.abs(sAngle - eAngle) === Math.PI * 2)
		eAngle -= 0.000001;
	oldArc.call(this, x, y, r, sAngle, eAngle, clockwise);
};

}(jQuery));
