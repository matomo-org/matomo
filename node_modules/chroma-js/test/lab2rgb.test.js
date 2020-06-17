const vows = require('vows')
const assert = require('assert');
require('es6-shim');


const limit = require('../src/utils/limit');
const lab2rgb = require('../src/io/lab/lab2rgb');

const round = v => limit(Math.round(v), 0, 255);

vows
    .describe('Testing CMYK color conversions')
    .addBatch({
        'parse simple HSI colors': {
            topic: {
                black:      { in: [0,0,0],              out: [0,0,0,1]},
                white:      { in: [100,0,0],            out: [255,255,255,1]},
                gray:       { in: [53.59,0,0],          out: [128,128,128,1]},
                red:        { in: [53.24,80.09,67.2],   out: [255,0,0,1]},
                yellow:     { in: [97.14,-21.55,94.48], out: [255,255,0,1]},
                green:      { in: [87.73,-86.18,83.18], out: [0,255,0,1]},
                cyan:       { in: [91.11,-48.09,-14.13], out: [0,255,255,1]},
                blue:       { in: [32.3,79.19,-107.86], out: [0,0,255,1]},
                magenta:    { in: [60.32,98.23,-60.82], out: [255,0,255,1]},
            },
            lab_arr(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(lab2rgb(topic[key].in).map(round), topic[key].out);
                });
            },
            lab_args(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(lab2rgb.apply(null, topic[key].in).map(round), topic[key].out, key);
                });
            },
            lab_obj(topic) {
                Object.keys(topic).forEach(key => {
                    const [l,a,b] = topic[key].in;
                    assert.deepEqual(lab2rgb({l,a,b}).map(round), topic[key].out, key);
                });
            },
        }
    })
    .export(module);
