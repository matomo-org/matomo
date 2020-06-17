const {unpack, type} = require('../../utils');
const chroma = require('../../chroma');
const Color = require('../../Color');
const input = require('../input');

const rgb2hsv = require('./rgb2hsv');

Color.prototype.hsv = function() {
    return rgb2hsv(this._rgb);
};

chroma.hsv = (...args) => new Color(...args, 'hsv');

input.format.hsv = require('./hsv2rgb');

input.autodetect.push({
    p: 2,
    test: (...args) => {
        args = unpack(args, 'hsv');
        if (type(args) === 'array' && args.length === 3) {
            return 'hsv';
        }
    }
});
