require('../io/hcg');
const interpolate_hsx = require('./_hsx');

const hcg = (col1, col2, f) => {
	return interpolate_hsx(col1, col2, f, 'hcg');
}

// register interpolator
require('./index').hcg = hcg;

module.exports = hcg;
