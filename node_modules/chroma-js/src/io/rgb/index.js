const chroma = require('../../chroma');
const Color = require('../../Color');
const input = require('../input');
const {unpack, type} = require('../../utils');
const {round} = Math;

Color.prototype.rgb = function(rnd=true) {
    if (rnd === false) return this._rgb.slice(0,3);
    return this._rgb.slice(0,3).map(round);
}

Color.prototype.rgba = function(rnd=true) {
    return this._rgb.slice(0,4).map((v,i) => {
        return i<3 ? (rnd === false ? v : round(v)) : v;
    });
};

chroma.rgb = (...args) => new Color(...args, 'rgb');

input.format.rgb = (...args) => {
    const rgba = unpack(args, 'rgba');
    if (rgba[3] === undefined) rgba[3] = 1;
    return rgba;
};

input.autodetect.push({
    p: 3,
    test: (...args) => {
        args = unpack(args, 'rgba');
        if (type(args) === 'array' && (args.length === 3 ||
            args.length === 4 && type(args[3]) == 'number' && args[3] >= 0 && args[3] <= 1)) {
            return 'rgb';
        }
    }
});
