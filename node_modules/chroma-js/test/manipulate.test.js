require('es6-shim');
const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');


vows
    .describe('Some tests for chroma.color()')

    .addBatch({

        'modify colors': {
            topic() { return chroma('#ff0000'); },
            'darken'(topic) { return assert.equal(topic.darken().hex(), '#c20000'); },
            'darker'(topic) { return assert.equal(topic.darker().hex(), '#c20000'); },
            'darken more'(topic) { return assert.equal(topic.darker(2).hex(), '#890000'); },
            'darken too much'(topic) { return assert.equal(topic.darker(10).hex(), '#000000'); },
            'brighten'(topic) { return assert.equal(topic.brighten().hex(), '#ff5a36'); },
            'brighten too much'(topic) { return assert.equal(topic.brighten(10).hex(), '#ffffff'); },
            'brighter'(topic) { return assert.equal(topic.brighter().hex(), '#ff5a36'); },
            'saturate'(topic) { return assert.equal(topic.saturate().hex(), '#ff0000'); },
            'desaturate'(topic) { return assert.equal(topic.desaturate().hex(), '#ee3a20'); },
            'desaturate more'(topic) { return assert.equal(topic.desaturate(2).hex(), '#db5136'); },
            'desaturate too much'(topic) { return assert.equal(topic.desaturate(400).hex(), '#7f7f7f'); }
        },

        'premultiply': {
            topic: chroma('rgba(32, 48, 96, 0.5)'),
            'premultiply rgba'(topic) { return assert.deepEqual(topic.premultiply().rgba(), [16, 24, 48, 0.5]); },
            'premultiply hex'(topic) { return assert.equal(topic.premultiply().hex(), '#10183080'); },
            'premultiply hex rgb'(topic) { return assert.equal(topic.premultiply().hex('rgb'), '#101830'); },
            'premultiply num'(topic) { return assert.equal(topic.premultiply().num(), 0x101830); }
        }}).export(module);
