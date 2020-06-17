const chroma = require('../../chroma');
const Color = require('../../Color');
const input = require('../input');
const {unpack, type} = require('../../utils');

const rgb2cmyk = require('./rgb2cmyk');

Color.prototype.cmyk = function() {
    return rgb2cmyk(this._rgb);
};

chroma.cmyk = (...args) => new Color(...args, 'cmyk');

input.format.cmyk = require('./cmyk2rgb');

input.autodetect.push({
    p: 2,
    test: (...args) => {
        args = unpack(args, 'cmyk');
        if (type(args) === 'array' && args.length === 4) {
            return 'cmyk';
        }
    }
});
