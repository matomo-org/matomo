const {unpack, type} = require('../../utils');
const chroma = require('../../chroma');
const Color = require('../../Color');
const input = require('../input');

const rgb2hsl = require('./rgb2hsl');

Color.prototype.hsl = function() {
    return rgb2hsl(this._rgb);
};

chroma.hsl = (...args) => new Color(...args, 'hsl');

input.format.hsl = require('./hsl2rgb');

input.autodetect.push({
    p: 2,
    test: (...args) => {
        args = unpack(args, 'hsl');
        if (type(args) === 'array' && args.length === 3) {
            return 'hsl';
        }
    }
});
