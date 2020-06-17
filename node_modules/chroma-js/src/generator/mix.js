const Color = require('../Color');
const {type} = require('../utils');
const interpolator = require('../interpolator');

module.exports = (col1, col2, f=0.5, ...rest) => {
    let mode = rest[0] || 'lrgb';
    if (!interpolator[mode] && !rest.length) {
        // fall back to the first supported mode
        mode = Object.keys(interpolator)[0];
    }
    if (!interpolator[mode]) {
        throw new Error(`interpolation mode ${mode} is not defined`);
    }
    if (type(col1) !== 'object') col1 = new Color(col1);
    if (type(col2) !== 'object') col2 = new Color(col2);
    return interpolator[mode](col1, col2, f)
        .alpha(col1.alpha() + f * (col2.alpha() - col1.alpha()));
}
