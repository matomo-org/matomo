require 'es6-shim'
vows = require 'vows'
assert = require 'assert'
chroma = require '../chroma'

vows
    .describe('Some tests for chroma.limits()')

    .addBatch

        'simple continuous domain':
            topic: -> chroma.limits [1,2,3,4,5], 'continuous'
            'domain': (topic) -> assert.deepEqual topic, [1,5]

        'continuous domain, negative values':
            topic: -> chroma.limits [1,-2, -3,4,5], 'continuous'
            'domain': (topic) -> assert.deepEqual topic, [-3,5]

        'continuous domain, null values':
            topic: -> chroma.limits [1, undefined, null, 4, 5], 'continuous'
            'domain': (topic) -> assert.deepEqual topic, [1,5]

        'equidistant domain':
            topic: -> chroma.limits [0,10], 'equidistant', 5
            'domain': (topic) -> assert.deepEqual topic, [0, 2, 4, 6, 8, 10]

        'equidistant domain, NaN values':
            topic: -> chroma.limits [0,9,3,6,3,5,undefined,Number.NaN,10], 'equidistant', 5
            'domain': (topic) -> assert.deepEqual topic, [0, 2, 4, 6, 8, 10]

        'logarithmic domain':
            topic: -> chroma.limits [1,10000], 'log', 4
            'domain': (topic) -> assert.deepEqual topic, [1, 10, 100, 1000, 10000]

        'logarithmic domain - non-positive values':
            topic: -> [-1,10000]
            'domain': (topic) ->
                assert.throws () ->
                    chroma.limits topic, 'log', 4
                , 'Logarithmic scales are only possible for values > 0'

        'quantiles domain':
            topic: -> chroma.limits [1,2,3,4,5,10,20,100], 'quantiles', 2
            'domain': (topic) -> assert.deepEqual topic, [1, 5, 100]

    .export(module)