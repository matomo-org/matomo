require('es6-shim');

const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');


vows.describe('Some tests for chroma.num()').addBatch({
    'number output': {
        topic: chroma.hsl(0, 1, 0.5, 0.5),
        'numoutput': function() {
            return function(topic) { assert.equal(topic.num(), 0xff0000); };
        }
    },
    'num color': {
        topic: [chroma(0xff0000), chroma(0x000000), chroma(0xffffff), chroma(0x31ff98), chroma('red')],
        'hex': function(topic) { assert.equal(topic[0].hex(), '#ff0000'); },
        'num': function(topic) { assert.equal(topic[0].num(), 0xff0000); },
        'hex-black': function(topic) { assert.equal(topic[1].hex(), '#000000'); },
        'num-black': function(topic) { assert.equal(topic[1].num(), 0x000000); },
        'hex-white': function(topic) { assert.equal(topic[2].hex(), '#ffffff'); },
        'num-white': function(topic) { assert.equal(topic[2].num(), 0xffffff); },
        'hex-rand': function(topic) { assert.equal(topic[3].hex(), '#31ff98'); },
        'num-rand': function(topic) { assert.equal(topic[3].num(), 0x31ff98); },
        'rum-red': function(topic) { assert.equal(topic[4].num(), 0xff0000); }
    }
})["export"](module);
