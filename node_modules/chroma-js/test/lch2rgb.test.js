const vows = require('vows')
const assert = require('assert');
require('es6-shim');


const limit = require('../src/utils/limit');
const lch2rgb = require('../src/io/lch/lch2rgb');

const round = v => limit(Math.round(v), 0, 255);

vows
    .describe('Testing LCH conversions')
    .addBatch({
        'parse simple LCH colors': {
            topic: {
                black:      { in: [0,0,NaN],            out: [0,0,0,1]},
                white:      { in: [100,0,NaN],            out: [255,255,255,1]},
                gray:       { in: [53.59,0,NaN],          out: [128,128,128,1]},
                red:        { in: [53.24,104.55,40],   out: [255,0,0,1]},
                yellow:     { in: [97.14,96.91,102.85], out: [255,255,0,1]},
                green:      { in: [87.73,119.78,136.02], out: [0,255,0,1]},
                cyan:       { in: [91.11,50.12,196.38], out: [0,255,255,1]},
                blue:       { in: [32.3,133.81,306.28], out: [0,0,255,1]},
                magenta:    { in: [60.32,115.54,328.23], out: [255,0,255,1]},
            },
            lch_arr(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(lch2rgb(topic[key].in).map(round), topic[key].out);
                });
            },
            lch_args(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(lch2rgb.apply(null, topic[key].in).map(round), topic[key].out, key);
                });
            },
            lch_obj(topic) {
                Object.keys(topic).forEach(key => {
                    const [l,c,h] = topic[key].in;
                    assert.deepEqual(lch2rgb({l,c,h}).map(round), topic[key].out, key);
                });
            },
        }
    })
    .export(module);
