const vows = require('vows')
const assert = require('assert');
require('es6-shim');

require('../src/io/named');
const contrast = require('../src/utils/contrast');


vows
    .describe('Testing contrast ratio')
    .addBatch({
        'maximum contrast': {
            topic: contrast('black', 'white'),
            'is 21:1'(topic) { assert.equal(topic, 21) }
        },
        'minimum contrast': {
            topic: contrast('white', 'white'),
            'is 1:1'(topic) { assert.equal(topic, 1) }
        },
        'contrast between white and red': {
            topic: contrast('red', 'white'),
            'is 4:1'(topic) { assert.equal(Math.round(topic), 4) }
        },

    })
    .export(module)
