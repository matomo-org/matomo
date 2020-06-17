const vows = require('vows')
const assert = require('assert');
require('es6-shim');

const lch2lab = require('../src/io/lch/lch2lab');

const tests = {
    black:      { lab: [0,0,0],               lch: [0,0,NaN]},
    white:      { lab: [100,0,0],             lch: [100,0,NaN]},
    gray:       { lab: [53.59,0,0],           lch: [53.59,0,NaN]},
    red:        { lab: [53.24,80.09,67.2],    lch: [53.24,104.55,40]},
    yellow:     { lab: [97.14,-21.55,94.48],  lch: [97.14,96.91,102.85]},
    green:      { lab: [87.73,-86.17,83.18],  lch: [87.73,119.77,136.01]},
    cyan:       { lab: [91.11,-48.09,-14.13], lch: [91.11,50.12,196.37]},
    blue:       { lab: [32.3,79.2,-107.86],  lch: [32.3,133.81,306.29]},
    magenta:    { lab: [60.32,98.23,-60.81],  lch: [60.32,115.53,328.24]},
};

const round = (digits) => {
    const d = Math.pow(10,digits);
    return (v) => Math.round(v*d) / d;
};

const rnd = round(2);
const batch = {};

Object.keys(tests).forEach(key => {
    batch[`lch2lab - ${key}`] = {
        topic: tests[key],
        array(topic) {
            assert.deepStrictEqual(lch2lab(topic.lch).map(rnd), topic.lab);
        },
        args(topic) {
            assert.deepStrictEqual(lch2lab.apply(null, topic.lch).map(rnd), topic.lab);
        },
        obj(topic) {
            let [l,c,h] = topic.lch;
            assert.deepStrictEqual(lch2lab({l,c,h}).map(rnd), topic.lab);
        }
    }
});

vows
    .describe('Testing lch2lab color conversions')
    .addBatch(batch)
    .export(module);
