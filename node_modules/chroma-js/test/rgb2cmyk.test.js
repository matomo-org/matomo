const vows = require('vows')
const assert = require('assert');
require('es6-shim');

const rgb2cmyk = require('../src/io/cmyk/rgb2cmyk');

vows
    .describe('Testing CMYK color conversions')
    .addBatch({
        'parse simple CMYK colors': {
            topic: {
                black: {    cmyk: [0,0,0,1], rgb: [0,0,0,1]},
                white: {    cmyk: [0,0,0,0], rgb: [255,255,255,1]},
                red: {      cmyk: [0,1,1,0], rgb: [255,0,0,1]},
                green: {    cmyk: [1,0,1,0], rgb: [0,255,0,1]},
                blue: {     cmyk: [1,1,0,0], rgb: [0,0,255,1]},
                yellow: {   cmyk: [0,0,1,0], rgb: [255,255,0,1]},
                cyan: {     cmyk: [1,0,0,0], rgb: [0,255,255,1]},
                magenta: {  cmyk: [0,1,0,0], rgb: [255,0,255,1]},
            },
            rgb2cmyk(topic) {
                Object.keys(topic).forEach(key => {
                    assert.deepEqual(rgb2cmyk(topic[key].rgb), topic[key].cmyk);
                });
            }
        }
    })
    .export(module);
