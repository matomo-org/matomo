const {unpack, type} = require('../../utils');
const chroma = require('../../chroma');
const Color = require('../../Color');
const input = require('../input');

const rgb2lch = require('./rgb2lch');

Color.prototype.lch = function() { return rgb2lch(this._rgb); };
Color.prototype.hcl = function() { return rgb2lch(this._rgb).reverse(); };

chroma.lch = (...args) => new Color(...args, 'lch');
chroma.hcl = (...args) => new Color(...args, 'hcl');

input.format.lch = require('./lch2rgb');
input.format.hcl = require('./hcl2rgb');

['lch','hcl'].forEach(m => input.autodetect.push({
    p: 2,
    test: (...args) => {
        args = unpack(args, m);
        if (type(args) === 'array' && args.length === 3) {
            return m;
        }
    }
}));

