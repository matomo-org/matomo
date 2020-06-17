const vows = require('vows')
const assert = require('assert');
require('es6-shim');

const rgb2lab = require('../src/io/lab/rgb2lab');

const tests = {
    black:      { lab: [0,0,0],               rgb: [0,0,0] },
    white:      { lab: [100,0,0],             rgb: [255,255,255] },
    gray:       { lab: [53.59,0,0],           rgb: [128,128,128] },
    red:        { lab: [53.24,80.09,67.2],    rgb: [255,0,0] },
    yellow:     { lab: [97.14,-21.55,94.48],  rgb: [255,255,0] },
    green:      { lab: [87.73,-86.18,83.18],  rgb: [0,255,0] },
    cyan:       { lab: [91.11,-48.09,-14.13], rgb: [0,255,255] },
    blue:       { lab: [32.3,79.19,-107.86],  rgb: [0,0,255] },
    magenta:    { lab: [60.32,98.23,-60.82],  rgb: [255,0,255] },
};

const round = (digits) => {
    const d = Math.pow(10,digits);
    return (v) => {
        if (v > -1e-3 && v < 1e-3) v = 0;
        return Math.round(v*d) / d;
    }
}
const rnd = round(2);

const batch = {};

Object.keys(tests).forEach(key => {
    batch[`rgb2lab ${key}`] = {
        topic: tests[key],
        array(topic) {
            assert.deepStrictEqual(rgb2lab(topic.rgb).map(rnd), topic.lab);
        },
        obj(topic) {
            let [r,g,b] = topic.rgb;
            assert.deepStrictEqual(rgb2lab({r,g,b}).map(rnd), topic.lab);
        },
        args(topic) {
            assert.deepStrictEqual(rgb2lab.apply(null, topic.rgb).map(rnd), topic.lab);
        }
    }
});

vows
    .describe('Test rgb2lab color conversions')
    .addBatch(batch)
    .export(module);
