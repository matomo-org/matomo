require('../io/lch');
const interpolate_hsx = require('./_hsx');

const lch = (col1, col2, f) => {
	return interpolate_hsx(col1, col2, f, 'lch');
}

// register interpolator
require('./index').lch = lch;
require('./index').hcl = lch;

module.exports = lch;
