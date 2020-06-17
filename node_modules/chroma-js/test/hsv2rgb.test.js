const vows = require('vows')
const assert = require('assert');
require('es6-shim');

const hsv2rgb = require('../src/io/hsv/hsv2rgb');

vows
    .describe('Testing hsv color conversions')
    .addBatch({
        'parse simple hsv colors': {
            topic: {
                black:      { in: [NaN,0,0],   out: [0,0,0,1]},
                white:      { in: [NaN,0,1],   out: [255,255,255,1]},
                gray:       { in: [0,0,0.5],   out: [127.5,127.5,127.5,1]},
                red:        { in: [0,1,1],     out: [255,0,0,1]},
                yellow:     { in: [60,1,1],    out: [255,255,0,1]},
                green:      { in: [120,1,1],   out: [0,255,0,1]},
                cyan:       { in: [180,1,1],   out: [0,255,255,1]},
                blue:       { in: [240,1,1],   out: [0,0,255,1]},
                magenta:    { in: [300,1,1],   out: [255,0,255,1]},
                red_again:  { in: [360,1,1],   out: [255,0,0,1]}
            },
            hsv_arr(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(hsv2rgb(topic[key].in), topic[key].out);
                });
            },
            hsv_args(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(hsv2rgb.apply(null, topic[key].in), topic[key].out, key);
                });
            },
            hsv_obj(topic) {
                Object.keys(topic).forEach(key => {
                    const [h,s,v] = topic[key].in;
                    assert.deepEqual(hsv2rgb({h,s,v}), topic[key].out, key);
                });
            },
        }
    })
    .export(module);
