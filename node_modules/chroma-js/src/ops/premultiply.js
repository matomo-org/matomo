const Color = require('../Color');

Color.prototype.premultiply = function(mutate=false) {
	const rgb = this._rgb;
	const a = rgb[3];
	if (mutate) {
		this._rgb = [rgb[0]*a, rgb[1]*a, rgb[2]*a, a];
		return this;
	} else {
		return new Color([rgb[0]*a, rgb[1]*a, rgb[2]*a, a], 'rgb');
	}
}
