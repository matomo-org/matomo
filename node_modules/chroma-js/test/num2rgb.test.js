const vows = require('vows')
const assert = require('assert');
require('es6-shim');

const num2rgb = require('../src/io/num/num2rgb');

vows
    .describe('Testing num2rgb color conversions')
    .addBatch({
        'parse simple numeric HEX colors': {
            topic: {
                black: {in: 0x000000,   out: [0,0,0,1]},
                white: {in: 0xFFFFFF,   out: [255,255,255,1]},
                red: {in: 0xff0000,     out: [255,0,0,1]},
                green: {in: 0x00ff00,   out: [0,255,0,1]},
                blue: {in: 0x0000ff,    out: [0,0,255,1]},
                yellow: {in: 0xffff00,  out: [255,255,0,1]},
                cyan: {in: 0x00ffff,    out: [0,255,255,1]},
                magenta: {in: 0xff00ff, out: [255,0,255,1]},
            },
            num(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(num2rgb(topic[key].in), topic[key].out);
                });
            }
        }
    })
    .export(module);
