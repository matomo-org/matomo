require('../io/hsl');
const interpolate_hsx = require('./_hsx');

const hsl = (col1, col2, f) => {
	return interpolate_hsx(col1, col2, f, 'hsl');
}

// register interpolator
require('./index').hsl = hsl;

module.exports = hsl;
