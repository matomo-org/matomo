const vows = require('vows')
const assert = require('assert');
require('es6-shim');

const hcg2rgb = require('../src/io/hcg/hcg2rgb');

vows
    .describe('Testing HCG color conversions')
    .addBatch({
        'parse simple HCG colors': {
            topic: {
                black:      { in: [0,0,0],     out: [0,0,0,1]},
                white:      { in: [0,0,1],     out: [255,255,255,1]},
                gray:       { in: [0,0,0.5],   out: [127.5,127.5,127.5,1]},
                red:        { in: [0,1,0],     out: [255,0,0,1]},
                yellow:     { in: [60,1,0],    out: [255,255,0,1]},
                green:      { in: [120,1,0],   out: [0,255,0,1]},
                cyan:       { in: [180,1,0],   out: [0,255,255,1]},
                blue:       { in: [240,1,0],   out: [0,0,255,1]},
                magenta:    { in: [300,1,0],   out: [255,0,255,1]},
                red_again:  { in: [360,1,0],   out: [255,0,0,1]}
            },
            hcg_arr(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(hcg2rgb(topic[key].in), topic[key].out);
                });
            },
            hcg_args(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(hcg2rgb.apply(null, topic[key].in), topic[key].out, key);
                });
            },
            hcg_obj(topic) {
                Object.keys(topic).forEach(key => {
                    const [h,c,g] = topic[key].in;
                    assert.deepEqual(hcg2rgb({h,c,g}), topic[key].out, key);
                });
            },
        }
    })
    .export(module);
