require('../io/num');

const Color = require('../Color');

const num = (col1, col2, f) => {
    const c1 = col1.num();
    const c2 = col2.num();
    return new Color(c1 + f * (c2-c1), 'num')
}

// register interpolator
require('./index').num = num;

module.exports = num;
