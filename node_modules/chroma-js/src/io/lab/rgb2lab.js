const LAB_CONSTANTS = require('./lab-constants');
const {unpack} = require('../../utils');
const {pow} = Math;

const rgb2lab = (...args) => {
    const [r,g,b] = unpack(args, 'rgb');
    const [x,y,z] = rgb2xyz(r,g,b);
    const l = 116 * y - 16;
    return [l < 0 ? 0 : l, 500 * (x - y), 200 * (y - z)];
}

const rgb_xyz = (r) => {
    if ((r /= 255) <= 0.04045) return r / 12.92;
    return pow((r + 0.055) / 1.055, 2.4);
}

const xyz_lab = (t) => {
    if (t > LAB_CONSTANTS.t3) return pow(t, 1 / 3);
    return t / LAB_CONSTANTS.t2 + LAB_CONSTANTS.t0;
}

const rgb2xyz = (r,g,b) => {
    r = rgb_xyz(r);
    g = rgb_xyz(g);
    b = rgb_xyz(b);
    const x = xyz_lab((0.4124564 * r + 0.3575761 * g + 0.1804375 * b) / LAB_CONSTANTS.Xn);
    const y = xyz_lab((0.2126729 * r + 0.7151522 * g + 0.0721750 * b) / LAB_CONSTANTS.Yn);
    const z = xyz_lab((0.0193339 * r + 0.1191920 * g + 0.9503041 * b) / LAB_CONSTANTS.Zn);
    return [x,y,z];
}

module.exports = rgb2lab;
