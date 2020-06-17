const chroma = require('../../chroma');
const Color = require('../../Color');
const input = require('../input');
const {type} = require('../../utils');

const rgb2css = require('./rgb2css');
const css2rgb = require('./css2rgb');

Color.prototype.css = function(mode) {
    return rgb2css(this._rgb, mode);
};

chroma.css = (...args) => new Color(...args, 'css');

input.format.css = css2rgb;

input.autodetect.push({
    p: 5,
    test: (h, ...rest) => {
        if (!rest.length && type(h) === 'string' && css2rgb.test(h)) {
            return 'css';
        }
    }
})


