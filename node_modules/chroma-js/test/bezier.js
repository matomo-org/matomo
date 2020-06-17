require('es6-shim');
const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');


vows
    .describe('Testing bezier interpolation')

    .addBatch({

        'simple two color linear interpolation': {
            topic: {
                f: chroma.bezier(['white', 'black'])
            },
            'starts from white'(topic) { assert.equal(topic.f(0).hex(), '#ffffff'); },
            'ends in black'(topic) { assert.equal(topic.f(1).hex(), '#000000'); },
            'center is grey'(topic) { assert.equal(topic.f(0.5).hex(), '#777777'); }
        },

        'three color quadratic bezier interpolation': {
            topic: {
                f: chroma.bezier(['white', 'red', 'black'])
            },
            'starts from white'(topic) { assert.equal(topic.f(0).hex(), '#ffffff'); },
            'ends in black'(topic) { assert.equal(topic.f(1).hex(), '#000000'); },
            'center is a greyish red'(topic) { assert.equal(topic.f(0.5).hex(), '#c45c44'); }
        },

        'four color cubic bezier interpolation': {
            topic: {
                f: chroma.bezier(['white', 'yellow', 'red', 'black'])
            },
            'starts from white'(topic) { assert.equal(topic.f(0).hex(), '#ffffff'); },
            'ends in black'(topic) { assert.equal(topic.f(1).hex(), '#000000'); },
            '1st quarter'(topic) { assert.equal(topic.f(0.25).hex(), '#ffe085'); },
            'center'(topic) { assert.equal(topic.f(0.5).hex(), '#e69735'); },
            '3rd quarter'(topic) { assert.equal(topic.f(0.75).hex(), '#914213'); }
        },

        'five color diverging quadratic bezier interpolation': {
            topic: {
                f: chroma.bezier(['darkred', 'orange', 'snow', 'lightgreen', 'royalblue'])
            },
            'starts from darkred'(topic) { assert.equal(topic.f(0).hex(), '#8b0000'); },
            'ends in royalblue'(topic) { assert.equal(topic.f(1).hex(), '#4169e1'); },
            'center is snow'(topic) { assert.equal(topic.f(0.5).hex(), '#fffafa'); },
            '1st quarter'(topic) { assert.equal(topic.f(0.25).hex(), '#e9954e'); },
            '3rd quarter'(topic) { assert.equal(topic.f(0.75).hex(), '#a6cfc1'); }
        },

        'using bezier in a chroma.scale': {
            topic: {
                f: chroma.scale(
                    chroma.bezier(['darkred', 'orange', 'snow', 'lightgreen', 'royalblue'])
                ).domain([0,1],5).out('hex')
            },
            'starts from darkred'(topic) { assert.equal(topic.f(0), '#8b0000'); },
            'ends in royalblue'(topic) { assert.equal(topic.f(1), '#4169e1'); },
            'center is snow'(topic) { assert.equal(topic.f(0.5), '#fffafa'); },
            '1st quarter'(topic) { assert.equal(topic.f(0.25), '#e9954e'); },
            '3rd quarter'(topic) { assert.equal(topic.f(0.75), '#a6cfc1'); }
        }})
    .export(module);
