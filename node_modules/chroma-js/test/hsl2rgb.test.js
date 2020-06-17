const vows = require('vows')
const assert = require('assert');
require('es6-shim');

const hsl2rgb = require('../src/io/hsl/hsl2rgb');

vows
    .describe('Testing CMYK color conversions')
    .addBatch({
        'parse simple HSL colors': {
            topic: {
                black:      { in: [0,0,0],     out: [0,0,0,1]},
                white:      { in: [0,0,1],     out: [255,255,255,1]},
                gray:       { in: [0,0,0.5],   out: [127.5,127.5,127.5,1]},
                red:        { in: [0,1,0.5],   out: [255,0,0,1]},
                yellow:     { in: [60,1,0.5],  out: [255,255,0,1]},
                green:      { in: [120,1,0.5], out: [0,255,0,1]},
                cyan:       { in: [180,1,0.5], out: [0,255,255,1]},
                blue:       { in: [240,1,0.5], out: [0,0,255,1]},
                magenta:    { in: [300,1,0.5], out: [255,0,255,1]},
                red_again:  { in: [360,1,0.5], out: [255,0,0,1]}
            },
            hsl_arr(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(hsl2rgb(topic[key].in), topic[key].out, key);
                });
            },
            hsl_args(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(hsl2rgb.apply(null, topic[key].in), topic[key].out, key);
                });
            },
            hsl_obj(topic) {
                Object.keys(topic).forEach(key => {
                    const [h,s,l] = topic[key].in;
                    assert.deepEqual(hsl2rgb({h,s,l}), topic[key].out, key);
                });
            },
        },
        'make sure that alpha is 1': {

        }
    })
    .export(module);
