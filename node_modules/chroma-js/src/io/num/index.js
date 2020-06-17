const chroma = require('../../chroma');
const Color = require('../../Color');
const input = require('../input');
const {type} = require('../../utils');

const rgb2num = require('./rgb2num');

Color.prototype.num = function() {
    return rgb2num(this._rgb);
};

chroma.num = (...args) => new Color(...args, 'num');

input.format.num = require('./num2rgb');

input.autodetect.push({
    p: 5,
    test: (...args) => {
        if (args.length === 1 && type(args[0]) === 'number' && args[0] >= 0 && args[0] <= 0xFFFFFF) {
            return 'num';
        }
    }
});

