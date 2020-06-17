const {unpack, type} = require('../../utils');
const chroma = require('../../chroma');
const Color = require('../../Color');
const input = require('../input');

const rgb2hsi = require('./rgb2hsi');

Color.prototype.hsi = function() {
    return rgb2hsi(this._rgb);
};

chroma.hsi = (...args) => new Color(...args, 'hsi');

input.format.hsi = require('./hsi2rgb');

input.autodetect.push({
    p: 2,
    test: (...args) => {
        args = unpack(args, 'hsi');
        if (type(args) === 'array' && args.length === 3) {
            return 'hsi';
        }
    }
});
