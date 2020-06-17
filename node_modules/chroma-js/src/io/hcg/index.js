const {unpack, type} = require('../../utils');
const chroma = require('../../chroma');
const Color = require('../../Color');
const input = require('../input');

const rgb2hcg = require('./rgb2hcg');

Color.prototype.hcg = function() {
    return rgb2hcg(this._rgb);
};

chroma.hcg = (...args) => new Color(...args, 'hcg');

input.format.hcg = require('./hcg2rgb');

input.autodetect.push({
    p: 1,
    test: (...args) => {
        args = unpack(args, 'hcg');
        if (type(args) === 'array' && args.length === 3) {
            return 'hcg';
        }
    }
});
