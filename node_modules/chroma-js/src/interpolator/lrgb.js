const Color = require('../Color');
const {sqrt,pow} = Math;

const lrgb = (col1, col2, f) => {
    const [x1,y1,z1] = col1._rgb;
    const [x2,y2,z2] = col2._rgb;
    return new Color(
        sqrt(pow(x1,2) * (1-f) + pow(x2,2) * f),
        sqrt(pow(y1,2) * (1-f) + pow(y2,2) * f),
        sqrt(pow(z1,2) * (1-f) + pow(z2,2) * f),
        'rgb'
    )
}

// register interpolator
require('./index').lrgb = lrgb;

module.exports = lrgb;
