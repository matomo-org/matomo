const {unpack, type} = require('../../utils');
const chroma = require('../../chroma');
const Color = require('../../Color');
const input = require('../input');

const rgb2lab = require('./rgb2lab');

Color.prototype.lab = function() {
    return rgb2lab(this._rgb);
};

chroma.lab = (...args) => new Color(...args, 'lab');

input.format.lab = require('./lab2rgb');

input.autodetect.push({
    p: 2,
    test: (...args) => {
        args = unpack(args, 'lab');
        if (type(args) === 'array' && args.length === 3) {
            return 'lab';
        }
    }
});
