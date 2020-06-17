const Color = require('../../Color');
const chroma = require('../../chroma');
const input = require('../input');
const {unpack} = require('../../utils');

input.format.gl = (...args) => {
    const rgb = unpack(args, 'rgba');
    rgb[0] *= 255;
    rgb[1] *= 255;
    rgb[2] *= 255;
    return rgb;
}

chroma.gl = (...args) => new Color(...args, 'gl');

Color.prototype.gl = function() {
    const rgb = this._rgb;
    return [rgb[0]/255, rgb[1]/255, rgb[2]/255, rgb[3]];
};
