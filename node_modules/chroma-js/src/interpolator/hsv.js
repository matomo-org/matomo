require('../io/hsv');
const interpolate_hsx = require('./_hsx');

const hsv = (col1, col2, f) => {
	return interpolate_hsx(col1, col2, f, 'hsv');
}

// register interpolator
require('./index').hsv = hsv;

module.exports = hsv;
