/*
 * interpolates between a set of colors uzing a bezier spline
 * blend mode formulas taken from http://www.venture-ware.com/kevin/coding/lets-learn-math-photoshop-blend-modes/
 */

require('../io/rgb');
const chroma = require('../chroma');

const blend = (bottom, top, mode) => {
    if (!blend[mode]) {
        throw new Error('unknown blend mode ' + mode);
    }
    return blend[mode](bottom, top);
}

const blend_f = (f) =>
    (bottom,top) => {
        const c0 = chroma(top).rgb();
        const c1 = chroma(bottom).rgb();
        return chroma.rgb(f(c0, c1));
    }

const each = (f) =>
    (c0, c1) => {
        const out = []
        out[0] = f(c0[0], c1[0]);
        out[1] = f(c0[1], c1[1]);
        out[2] = f(c0[2], c1[2]);
        return out;
    }

const normal = (a) => a
const multiply = (a,b) => a * b / 255
const darken = (a,b) => a > b ? b : a
const lighten = (a,b) => a > b ? a : b
const screen = (a,b) => 255 * (1 - (1-a/255) * (1-b/255))
const overlay = (a,b) => b < 128 ? 2 * a * b / 255 : 255 * (1 - 2 * (1 - a / 255 ) * ( 1 - b / 255 ))
const burn = (a,b) => 255 * (1 - (1 - b / 255) / (a/255))
const dodge = (a,b) => {
    if (a === 255) return 255;
    a = 255 * (b / 255) / (1 - a / 255);
    return a > 255 ? 255 : a
}

// # add = (a,b) ->
// #     if (a + b > 255) then 255 else a + b

blend.normal = blend_f(each(normal));
blend.multiply = blend_f(each(multiply));
blend.screen = blend_f(each(screen));
blend.overlay = blend_f(each(overlay));
blend.darken = blend_f(each(darken));
blend.lighten = blend_f(each(lighten));
blend.dodge = blend_f(each(dodge));
blend.burn = blend_f(each(burn));
// blend.add = blend_f(each(add));

module.exports = blend;
