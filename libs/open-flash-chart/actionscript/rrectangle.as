// JG - I copied this from :
//   http://www.actionscript.org/showMovie.php?id=1183
//
// Rounded rectangle made only with actionscript.
// Code taken and modified from http://www.actionscript-toolbox.com
// w = rectangle width
// h = rectangle height
// rad = rounded corner radius
// x = x  start point for rectangle
// y = y  start point for rectangle
// 
// 
// If you have any questions about this script mail me: janiss@cc.lv
// 
MovieClip.prototype.rrectangle = function(w, h, rad, x, y, stroke, fill) {
	// added by JG on 30th May 07
	x = Math.round(x);
	y = Math.round(y);
	w = Math.round(w);
	h = Math.round(h);
	//
	this.lineStyle(stroke.width, stroke.color, stroke.alpha);
	this.beginFill(fill.color, fill.alpha);
	this.moveTo(0+rad, 0);
	this.lineTo(w-rad, 0);
	this.curveTo(w, 0, w, rad);
	this.lineTo(w, h-rad);
	this.curveTo(w, h, w-rad, h);
	this.lineTo(0+rad, h);
	this.curveTo(0, h, 0, h-rad);
	this.lineTo(0, 0+rad);
	this.curveTo(0, 0, 0+rad, 0);
	this.endFill();
	this._x = x;
	this._y = y;
};