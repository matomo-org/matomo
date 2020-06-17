require('../io/hsi');
const interpolate_hsx = require('./_hsx');

const hsi = (col1, col2, f) => {
	return interpolate_hsx(col1, col2, f, 'hsi');
}

// register interpolator
require('./index').hsi = hsi;

module.exports = hsi;
