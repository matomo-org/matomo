const vows = require('vows')
const assert = require('assert');
require('es6-shim');

const cmyk2rgb = require('../src/io/cmyk/cmyk2rgb');

vows
    .describe('Testing CMYK color conversions')
    .addBatch({
        'parse simple CMYK colors': {
            topic: [[0,0,0,1],[0,0,0,0],[0,1,1,0],[1,0,1,0],[1,1,0,0],[0,0,1,0],[1,0,0,0],[0,1,0,0]],
            black(t)    { return assert.deepEqual(cmyk2rgb(t[0]), [0,0,0,1]); },
            white(t)    { return assert.deepEqual(cmyk2rgb(t[1]), [255,255,255,1]); },
            red(t)      { return assert.deepEqual(cmyk2rgb(t[2]), [255,0,0,1]); },
            green(t)    { return assert.deepEqual(cmyk2rgb(t[3]), [0,255,0,1]); },
            blue(t)     { return assert.deepEqual(cmyk2rgb(t[4]), [0,0,255,1]); },
            yellow(t)   { return assert.deepEqual(cmyk2rgb(t[5]), [255,255,0,1]); },
            cyan(t)     { return assert.deepEqual(cmyk2rgb(t[6]), [0,255,255,1]); },
            magenta(t)  { return assert.deepEqual(cmyk2rgb(t[7]), [255,0,255,1]); }
        },
        'test unpacked arguments': {
            topic: [[0,0,0,1],[0,0,0,0],[0,1,1,0],[1,0,1,0],[1,1,0,0],[0,0,1,0],[1,0,0,0],[0,1,0,0]],
            black(t)    { return assert.deepEqual(cmyk2rgb.apply(null, t[0]), [0,0,0,1]); },
            white(t)    { return assert.deepEqual(cmyk2rgb.apply(null, t[1]), [255,255,255,1]); },
            red(t)      { return assert.deepEqual(cmyk2rgb.apply(null, t[2]), [255,0,0,1]); },
            green(t)    { return assert.deepEqual(cmyk2rgb.apply(null, t[3]), [0,255,0,1]); },
            blue(t)     { return assert.deepEqual(cmyk2rgb.apply(null, t[4]), [0,0,255,1]); },
            yellow(t)   { return assert.deepEqual(cmyk2rgb.apply(null, t[5]), [255,255,0,1]); },
            cyan(t)     { return assert.deepEqual(cmyk2rgb.apply(null, t[6]), [0,255,255,1]); },
            magenta(t)  { return assert.deepEqual(cmyk2rgb.apply(null, t[7]), [255,0,255,1]); }
        }
    })
    .export(module);
