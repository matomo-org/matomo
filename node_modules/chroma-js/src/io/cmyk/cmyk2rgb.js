const {unpack} = require('../../utils');

const cmyk2rgb = (...args) => {
    args = unpack(args, 'cmyk');
    const [c,m,y,k] = args;
    const alpha = args.length > 4 ? args[4] : 1;
    if (k === 1) return [0,0,0,alpha];
    return [
        c >= 1 ? 0 : 255 * (1-c) * (1-k), // r
        m >= 1 ? 0 : 255 * (1-m) * (1-k), // g
        y >= 1 ? 0 : 255 * (1-y) * (1-k), // b
        alpha
    ];
}

module.exports = cmyk2rgb;
