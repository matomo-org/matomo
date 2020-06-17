require('es6-shim');
const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');


vows
    .describe('Some tests for random colors')

    .addBatch({

        'random colors': {
            topic: chroma.random(),
            'valid hex code'(topic) { return assert(/^#[0-9a-f]{6}$/i.test(topic.hex())); }
        }}).export(module);
