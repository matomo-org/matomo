// cubehelix interpolation
// based on D.A. Green "A colour scheme for the display of astronomical intensity images"
// http://astron-soc.in/bulletin/11June/289392011.pdf

const {type, clip_rgb, TWOPI} = require('../utils');
const {pow,sin,cos} = Math;
const chroma = require('../chroma');

module.exports = function(start=300, rotations=-1.5, hue=1, gamma=1, lightness=[0,1]) {
    let dh = 0, dl;
    if (type(lightness) === 'array') {
        dl = lightness[1] - lightness[0];
    } else {
        dl = 0;
        lightness = [lightness, lightness];
    }

    const f = function(fract) {
        const a = TWOPI * (((start+120)/360) + (rotations * fract));
        const l = pow(lightness[0] + (dl * fract), gamma);
        const h = dh !== 0 ? hue[0] + (fract * dh) : hue;
        const amp = (h * l * (1-l)) / 2;
        const cos_a = cos(a);
        const sin_a = sin(a);
        const r = l + (amp * ((-0.14861 * cos_a) + (1.78277* sin_a)));
        const g = l + (amp * ((-0.29227 * cos_a) - (0.90649* sin_a)));
        const b = l + (amp * (+1.97294 * cos_a));
        return chroma(clip_rgb([r*255,g*255,b*255,1]));
    };

    f.start = function(s) {
        if ((s == null)) { return start; }
        start = s;
        return f;
    };

    f.rotations = function(r) {
        if ((r == null)) { return rotations; }
        rotations = r;
        return f;
    };

    f.gamma = function(g) {
        if ((g == null)) { return gamma; }
        gamma = g;
        return f;
    };

    f.hue = function(h) {
        if ((h == null)) { return hue; }
        hue = h;
        if (type(hue) === 'array') {
            dh = hue[1] - hue[0];
            if (dh === 0) { hue = hue[1]; }
        } else {
            dh = 0;
        }
        return f;
    };

    f.lightness = function(h) {
        if ((h == null)) { return lightness; }
        if (type(h) === 'array') {
            lightness = h;
            dl = h[1] - h[0];
        } else {
            lightness = [h,h];
            dl = 0;
        }
        return f;
    };

    f.scale = () => chroma.scale(f);

    f.hue(hue);

    return f;
};
