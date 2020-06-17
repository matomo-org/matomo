const Color = require('../../Color');
const input = require('../input');
const {type} = require('../../utils');

const w3cx11 = require('../../colors/w3cx11');
const hex2rgb = require('../hex/hex2rgb');
const rgb2hex = require('../hex/rgb2hex');

Color.prototype.name = function() {
    const hex = rgb2hex(this._rgb, 'rgb');
    for (let n of Object.keys(w3cx11)) {
        if (w3cx11[n] === hex) return n.toLowerCase();
    }
    return hex;
};

input.format.named = (name) => {
    name = name.toLowerCase();
    if (w3cx11[name]) return hex2rgb(w3cx11[name]);
    throw new Error('unknown color name: '+name);
}

input.autodetect.push({
    p: 5,
    test: (h, ...rest) => {
        if (!rest.length && type(h) === 'string' && w3cx11[h.toLowerCase()]) {
            return 'named';
        }
    }
});
