require 'es6-shim'
vows = require 'vows'
assert = require 'assert'
chroma = require '../chroma'

vows
    .describe('Some tests for chroma.analyze()')

    .addBatch
        'analyze an array of numbers':
            topic: -> chroma.analyze [1,2,2,3,4,5]
            'sum is 17': (topic) -> assert.equal topic.sum, 17
            'count is 6': (topic) -> assert.equal topic.count, 6
            'maximum is 5': (topic) -> assert.equal topic.max, 5
            'minumum is 1': (topic) -> assert.equal topic.min, 1
            'domain is [1,5]': (topic) -> assert.deepEqual topic.domain, [1,5]

        'analyze an object of numbers':
            topic: -> chroma.analyze {a: 1, b: 2, c: 2, d: 3, e: 4, f: 5}
            'sum is 17': (topic) -> assert.equal topic.sum, 17
            'count is 6': (topic) -> assert.equal topic.count, 6
            'maximum is 5': (topic) -> assert.equal topic.max, 5
            'minumum is 1': (topic) -> assert.equal topic.min, 1
            'domain is [1,5]': (topic) -> assert.deepEqual topic.domain, [1,5]

        'analyze an array of objects':
            topic: -> chroma.analyze [{ k: 1 }, { k: 2 }, { k: 2 }, { k: 3 }, { k: 4 }, { k: 5 }], 'k'
            'sum is 17': (topic) -> assert.equal topic.sum, 17
            'count is 6': (topic) -> assert.equal topic.count, 6
            'maximum is 5': (topic) -> assert.equal topic.max, 5
            'minumum is 1': (topic) -> assert.equal topic.min, 1
            'domain is [1,5]': (topic) -> assert.deepEqual topic.domain, [1,5]

        'analyze an object of objects':
            topic: -> chroma.analyze { a: { k: 1 }, b: { k: 2 }, c: { k: 2 }, d: { k: 3 }, e: { k: 4 }, f: { k: 5 }}, 'k'
            'sum is 17': (topic) -> assert.equal topic.sum, 17
            'count is 6': (topic) -> assert.equal topic.count, 6
            'maximum is 5': (topic) -> assert.equal topic.max, 5
            'minumum is 1': (topic) -> assert.equal topic.min, 1
            'domain is [1,5]': (topic) -> assert.deepEqual topic.domain, [1,5]

    .export(module)