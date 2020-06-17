require('es6-shim');
const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');


const rnd = function(f,d) {
    d = Math.pow(10,d);
    return Math.round(f*d) / d;
};

vows
    .describe('Testing relative luminance')

    .addBatch({

        'black': {
            topic: chroma('black'),
            'lum = 0'(topic) { return assert.equal(topic.luminance(), 0); }
        },

        'white': {
            topic: chroma('white'),
            'lum = 1'(topic) { return assert.equal(topic.luminance(), 1); }
        },

        'red': {
            topic: chroma('red'),
            'lum = 0.21'(topic) { return assert.equal(topic.luminance(), 0.2126); }
        },

        'yellow brighter than blue': {
            topic: [chroma('yellow').luminance(), chroma('blue').luminance()],
            'yellow > blue'(topic) { return assert(topic[0] > topic[1]); }
        },

        'green darker than red': {
            topic: [chroma('green').luminance(), chroma('red').luminance()],
            'green < red'(topic) { return assert(topic[0] < topic[1]); }
        },

        // setting luminance
        'set red luminance to 0.4': {
            topic: chroma('red').luminance(0.4),
            'lum = 0.4'(topic) { return assert.equal(rnd(topic.luminance(),3), 0.4); }
        },

        // setting luminance
        'set red luminance to 0': {
            topic: chroma('red').luminance(0),
            'lum = 0'(topic) { return assert.equal(rnd(topic.luminance(),2), 0); },
            'hex = #000'(topic) { return assert.equal(topic.hex(), '#000000'); }
        },

        // setting luminance
        'set black luminance to 0.5': {
            topic: chroma('black').luminance(0.5),
            'lum = 0.5'(topic) { return assert.equal(rnd(topic.luminance(), 2), 0.5); },
            'hex'(topic) { return assert.equal('#bcbcbc', topic.hex()); }
        },

            // setting luminance
        'set black luminance to 0.5 (lab)': {
            topic: chroma('black').luminance(0.5, 'lab'),
            'lum = 0.5'(topic) { return assert.equal(rnd(topic.luminance(),2), 0.5); },
            'hex'(topic) { return assert.equal('#bcbcbc', topic.hex()); }
        },

        'setting luminance returns new color': {
            topic: chroma('red'),
            'red luminance is 0.21'(topic) { return assert.equal(rnd(topic.luminance(),2), 0.21); },
            'set luminance to 0.4'(topic) { return assert.equal(topic.luminance(0.4).hex(), '#ff8686'); },
            'old luminance is still 0.21'(topic) { return assert.equal(rnd(topic.luminance(),2), 0.21); },
            'old color is still red'(topic) { return assert.equal(topic.hex(), '#ff0000'); }
        }}).export(module);
