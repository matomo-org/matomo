
require('es6-shim');

const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');

const test = vows.describe('Testing color conversions');

for (let k in chroma.colors) {
    test.addBatch({
        k: {
            topic: chroma.colors[k],
            'to hsl and back': function(t) {
                assert.equal(chroma.hsl(chroma(t).hsl()).hex(), t);
            },
            'to cmyk and back': function(t) {
                assert.equal(chroma.cmyk(chroma(t).cmyk()).hex(), t);
            },
            'to css and back': function(t) {
                assert.equal(chroma.css(chroma(t).css()).hex(), t);
            },
            'to hsi and back': function(t) {
                assert.equal(chroma.hsi(chroma(t).hsi()).hex(), t);
            },
            'to hsv and back': function(t) {
                assert.equal(chroma.hsv(chroma(t).hsv()).hex(), t);
            },
            'to lab and back': function(t) {
                assert.equal(chroma.lab(chroma(t).lab()).hex(), t);
            },
            'to lch and back': function(t) {
                assert.equal(chroma.lch(chroma(t).lch()).hex(), t);
            },
            'to num and back': function(t) {
                assert.equal(chroma.num(chroma(t).num()).hex(), t);
            }
        }
    });
}

test["export"](module);
