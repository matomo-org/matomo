const {unpack} = require('../../utils');
const rgb2lab = require('../lab/rgb2lab');
const lab2lch = require('./lab2lch');

const rgb2lch = (...args) => {
    const [r,g,b] = unpack(args, 'rgb');
    const [l,a,b_] = rgb2lab(r,g,b);
    return lab2lch(l,a,b_);
}

module.exports = rgb2lch;
