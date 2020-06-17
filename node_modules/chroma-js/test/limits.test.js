require('es6-shim');
const vows = require('vows');
const assert = require('assert');
const {limits} = require('../src/utils/analyze');

vows
    .describe('Some tests for chroma.limits()')

    .addBatch({

        'simple continuous domain': {
            topic: limits([1,2,3,4,5], 'continuous'),
            'domain'(topic) { return assert.deepEqual(topic, [1,5]); }
        },

        'continuous domain, negative values': {
            topic: limits([1,-2, -3,4,5], 'continuous'),
            'domain'(topic) { return assert.deepEqual(topic, [-3,5]); }
        },

        'continuous domain, null values': {
            topic: limits([1, undefined, null, 4, 5], 'continuous'),
            'domain'(topic) { return assert.deepEqual(topic, [1,5]); }
        },

        'equidistant domain': {
            topic: limits([0,10], 'equidistant', 5),
            'domain'(topic) { return assert.deepEqual(topic, [0, 2, 4, 6, 8, 10]); }
        },

        'equidistant domain, NaN values': {
            topic: limits([0,9,3,6,3,5,undefined,Number.NaN,10], 'equidistant', 5),
            'domain'(topic) { return assert.deepEqual(topic, [0, 2, 4, 6, 8, 10]); }
        },

        'logarithmic domain': {
            topic: limits([1,10000], 'log', 4),
            'domain'(topic) { return assert.deepEqual(topic, [1, 10, 100, 1000, 10000]); }
        },

        'logarithmic domain - non-positive values': {
            topic: [-1,10000],
            'domain'(topic) {
                return assert.throws(() => limits(topic, 'log', 4)
                , 'Logarithmic scales should only be possible for values > 0');
            }
        },

        'quantiles domain': {
            topic: limits([1,2,3,4,5,10,20,100], 'quantiles', 2),
            'domain'(topic) { return assert.deepEqual(topic, [1, 4.5, 100]); }
        },

        'quantiles not enough values': {
            topic: limits([0,1], 'quantiles', 5),
            'domain'(topic) { return assert.deepEqual(topic, [0,0.2,0.4,0.6,0.8,1]); }
        }})

    .export(module);
