
require('es6-shim');

const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');

vows.describe('Testing blend modes').addBatch({
    'multiply 1': {
        topic: chroma.blend('red', '#5a9f37', 'multiply'),
        'is #5a0000': function(topic) {
            return assert.equal(topic.hex(), '#5a0000');
        }
    },
    'multiply 2': {
        topic: chroma.blend('#33b16f', '#857590', 'multiply'),
        'is #1a513e': function(topic) {
            return assert.equal(topic.hex(), '#1b513f');
        }
    },
    'screen': {
        topic: chroma.blend('#b83d31', '#0da671', 'screen'),
        'is #bbbb8c': function(topic) {
            return assert.equal(topic.hex(), '#bcbb8c');
        }
    },
    'overlay': {
        topic: chroma.blend('#b83d31', '#0da671', 'overlay'),
        'is #784f2b': function(topic) {
            return assert.equal(topic.hex(), '#784f2b');
        }
    }
})["export"](module);
