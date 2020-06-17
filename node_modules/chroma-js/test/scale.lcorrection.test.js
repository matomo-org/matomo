require('es6-shim');
const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');


vows
    .describe('Testing lightess correction')

    .addBatch({

        'simple two color linear interpolation': {
            topic: {
                f: chroma.scale(['white', 'black']).mode('lab')
            },
            'center L is 50'(topic) {
                assert.equal(Math.round(topic.f(0.5).lab()[0]), 50);
            }
        },

        'hot - w/o correction': {
            topic: {
                f: chroma.scale(['white', 'yellow', 'red', 'black']).mode('lab')
            },
            'center L is 74'(topic) {
                assert.equal(Math.round(topic.f(0.5).lab()[0]), 74);
            }
        },

        'hot - with correction': {
            topic: {
                f: chroma.scale(['white', 'yellow', 'red', 'black']).mode('lab').correctLightness(true)
            },
            'center L is 50'(topic) {
                assert.equal(Math.round(topic.f(0.5).lab()[0]), 50);
            }
        },

        'hot - w/o correction - domained [0,100]': {
            topic: {
                f: chroma.scale(['white', 'yellow', 'red', 'black']).domain([0,100]).mode('lab')
            },
            'center L is 74'(topic) {
                assert.equal(Math.round(topic.f(50).lab()[0]), 74);
            }
        },

        'hot - with correction - domained [0,100]': {
            topic: {
                f: chroma.scale(['white', 'yellow', 'red', 'black'])
                    .domain([0,100]).mode('lab').correctLightness(true)
            },
            'center L is 50'(topic) {
                assert.equal(Math.round(topic.f(50).lab()[0]), 50);
            }
        },

        'hot - w/o correction - domained [0,20,40,60,80,100]': {
            topic: {
                f: chroma.scale(['white', 'yellow', 'red', 'black'])
                    .domain([0,20,40,60,80,100]).mode('lab')
            },
            'center L is 74'(topic) {
                assert.equal(Math.round(topic.f(50).lab()[0]), 74);
            }
        },

        'hot - with correction - domained [0,20,40,60,80,100]': {
            topic: {
                f: chroma.scale(['white', 'yellow', 'red', 'black'])
                    .domain([0,20,40,60,80,100]).mode('lab')
                    .correctLightness(true)
            },
            'center L is 50'(topic) {
                assert.equal(Math.round(topic.f(50).lab()[0]), 50);
            }
        }}).export(module);
