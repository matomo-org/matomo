const {unpack} = require('../../utils');
const lch2rgb = require('./lch2rgb');

const hcl2rgb = (...args) => {
    const hcl = unpack(args, 'hcl').reverse();
    return lch2rgb(...hcl);
}

module.exports = hcl2rgb;
