const {unpack} = require('../../utils');
const lch2lab = require('./lch2lab');
const lab2rgb = require('../lab/lab2rgb');

const lch2rgb = (...args) => {
    args = unpack(args, 'lch');
    const [l,c,h] = args;
    const [L,a,b_] = lch2lab (l,c,h);
    const [r,g,b] = lab2rgb (L,a,b_);
    return [r, g, b, args.length > 3 ? args[3] : 1];
}

module.exports = lch2rgb;
