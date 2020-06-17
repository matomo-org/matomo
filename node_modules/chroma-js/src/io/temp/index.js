const chroma = require('../../chroma');
const Color = require('../../Color');
const input = require('../input');

const rgb2temperature = require('./rgb2temperature');

Color.prototype.temp =
Color.prototype.kelvin =
Color.prototype.temperature = function() {
    return rgb2temperature(this._rgb);
};

chroma.temp =
chroma.kelvin =
chroma.temperature = (...args) => new Color(...args, 'temp');

input.format.temp =
input.format.kelvin =
input.format.temperature = require('./temperature2rgb');


