const vows = require('vows')
const assert = require('assert');
require('es6-shim');

const hex2rgb = require('../src/io/hex/hex2rgb');

// const round = (digits) => {
//     const d = Math.pow(10,digits);
//     return (v) => Math.round(v*d) / d;
// }

vows
    .describe('Testing HEX2RGB color conversions')
    .addBatch({
        'parse simple #rrggbb HEX colors': {
            topic: ['#000000','#ffffff','#ff0000','#00ff00','#0000ff','#ffff00','#00ffff','#ff00ff'],
            black(t)    { return assert.deepEqual(hex2rgb(t[0]), [0,0,0,1]); },
            white(t)    { return assert.deepEqual(hex2rgb(t[1]), [255,255,255,1]); },
            red(t)      { return assert.deepEqual(hex2rgb(t[2]), [255,0,0,1]); },
            green(t)    { return assert.deepEqual(hex2rgb(t[3]), [0,255,0,1]); },
            blue(t)     { return assert.deepEqual(hex2rgb(t[4]), [0,0,255,1]); },
            yellow(t)   { return assert.deepEqual(hex2rgb(t[5]), [255,255,0,1]); },
            cyan(t)     { return assert.deepEqual(hex2rgb(t[6]), [0,255,255,1]); },
            magenta(t)  { return assert.deepEqual(hex2rgb(t[7]), [255,0,255,1]); }
        },
        'parse simple rrggbb HEX colors without #': {
            topic: ['000000','ffffff','ff0000','00ff00','0000ff','ffff00','00ffff','ff00ff'],
            black(t)    { return assert.deepEqual(hex2rgb(t[0]), [0,0,0,1]); },
            white(t)    { return assert.deepEqual(hex2rgb(t[1]), [255,255,255,1]); },
            red(t)      { return assert.deepEqual(hex2rgb(t[2]), [255,0,0,1]); },
            green(t)    { return assert.deepEqual(hex2rgb(t[3]), [0,255,0,1]); },
            blue(t)     { return assert.deepEqual(hex2rgb(t[4]), [0,0,255,1]); },
            yellow(t)   { return assert.deepEqual(hex2rgb(t[5]), [255,255,0,1]); },
            cyan(t)     { return assert.deepEqual(hex2rgb(t[6]), [0,255,255,1]); },
            magenta(t)  { return assert.deepEqual(hex2rgb(t[7]), [255,0,255,1]); }
        },
        'parse simple short-hand HEX colors': {
            topic: ['#000','#fff','#f00','#0f0','#00f','#ff0','#0ff','#f0f'],
            black(t)    { return assert.deepEqual(hex2rgb(t[0]), [0,0,0,1]); },
            white(t)    { return assert.deepEqual(hex2rgb(t[1]), [255,255,255,1]); },
            red(t)      { return assert.deepEqual(hex2rgb(t[2]), [255,0,0,1]); },
            green(t)    { return assert.deepEqual(hex2rgb(t[3]), [0,255,0,1]); },
            blue(t)     { return assert.deepEqual(hex2rgb(t[4]), [0,0,255,1]); },
            yellow(t)   { return assert.deepEqual(hex2rgb(t[5]), [255,255,0,1]); },
            cyan(t)     { return assert.deepEqual(hex2rgb(t[6]), [0,255,255,1]); },
            magenta(t)  { return assert.deepEqual(hex2rgb(t[7]), [255,0,255,1]); }
        },
        'parse simple short-hand HEX colors without #': {
            topic: ['000','fff','f00','0f0','00f','ff0','0ff','f0f'],
            black(t)    { return assert.deepEqual(hex2rgb(t[0]), [0,0,0,1]); },
            white(t)    { return assert.deepEqual(hex2rgb(t[1]), [255,255,255,1]); },
            red(t)      { return assert.deepEqual(hex2rgb(t[2]), [255,0,0,1]); },
            green(t)    { return assert.deepEqual(hex2rgb(t[3]), [0,255,0,1]); },
            blue(t)     { return assert.deepEqual(hex2rgb(t[4]), [0,0,255,1]); },
            yellow(t)   { return assert.deepEqual(hex2rgb(t[5]), [255,255,0,1]); },
            cyan(t)     { return assert.deepEqual(hex2rgb(t[6]), [0,255,255,1]); },
            magenta(t)  { return assert.deepEqual(hex2rgb(t[7]), [255,0,255,1]); }
        },
        'parse different #rrggbbaa HEX colors': {
            topic: ['#00000000','#ffffff80','#ff000040','#00FF00C0','#FF00FFFF'],
            black(t)    { return assert.deepEqual(hex2rgb(t[0]), [0,0,0,0]); },
            white(t)    { return assert.deepEqual(hex2rgb(t[1]), [255,255,255,0.5]); },
            red(t)      { return assert.deepEqual(hex2rgb(t[2]), [255,0,0,0.25]); },
            green(t)    { return assert.deepEqual(hex2rgb(t[3]), [0,255,0,0.75]); },
            magenta(t)  { return assert.deepEqual(hex2rgb(t[4]), [255,0,255,1]); }
        },
        'parse different rrggbbaa HEX colors without #': {
            topic: ['00000000','ffffff80','ff000040','00FF00C0','FF00FFFF'],
            black(t)    { return assert.deepEqual(hex2rgb(t[0]), [0,0,0,0]); },
            white(t)    { return assert.deepEqual(hex2rgb(t[1]), [255,255,255,0.5]); },
            red(t)      { return assert.deepEqual(hex2rgb(t[2]), [255,0,0,0.25]); },
            green(t)    { return assert.deepEqual(hex2rgb(t[3]), [0,255,0,0.75]); },
            magenta(t)  { return assert.deepEqual(hex2rgb(t[4]), [255,0,255,1]); }
        },
        'parse different #rgba HEX colors': {
            topic: ['#0000','#fff8','#f004','#0F0C','#F0FF'],
            black(t)    { return assert.deepEqual(hex2rgb(t[0]), [0,0,0,0]); },
            white(t)    { return assert.deepEqual(hex2rgb(t[1]), [255,255,255,0.53]); },
            red(t)      { return assert.deepEqual(hex2rgb(t[2]), [255,0,0,0.27]); },
            green(t)    { return assert.deepEqual(hex2rgb(t[3]), [0,255,0,0.8]); },
            magenta(t)  { return assert.deepEqual(hex2rgb(t[4]), [255,0,255,1]); }
        },
        'parse different rgba HEX colors without #': {
            topic: ['0000','fff8','f004','0F0C','F0FF'],
            black(t)    { return assert.deepEqual(hex2rgb(t[0]), [0,0,0,0]); },
            white(t)    { return assert.deepEqual(hex2rgb(t[1]), [255,255,255,0.53]); },
            red(t)      { return assert.deepEqual(hex2rgb(t[2]), [255,0,0,0.27]); },
            green(t)    { return assert.deepEqual(hex2rgb(t[3]), [0,255,0,0.8]); },
            magenta(t)  { return assert.deepEqual(hex2rgb(t[4]), [255,0,255,1]); }
        },
    })
    .export(module);
