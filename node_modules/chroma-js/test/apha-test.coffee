require 'es6-shim'
vows = require 'vows'
assert = require 'assert'
chroma = require '../chroma'


vows
    .describe('Tests for the alpha channel')

    .addBatch

        'setting & getting alpha channel':
            topic: -> chroma 'red'
            'default alpha is 1': (topic) -> assert.equal topic.alpha(), 1
            'setting alpha to 0.5': (topic) -> assert.equal topic.alpha(0.5), topic
            'alpha is now 0.5': (topic) -> assert.equal topic.alpha(), 0.5

        'interpolating alpha channel':
            topic: -> chroma.mix chroma.color('white').alpha(0), chroma.color('black').alpha(1), 0.3
            'color is grey': (topic) -> assert.equal topic.hex(), '#b2b2b2'
            'alpha is 50%': (topic) -> assert.equal topic.alpha(), 0.3

        'constructing rgba color':
            topic: -> new chroma 255,0,0,0.5,'rgb'
            'alpha is 50%': (topic) -> assert.equal topic.alpha(), 0.5

        'constructing rgba color, rgb shorthand':
            topic: -> chroma.rgb(255,0,0,0.5)
            'alpha is 50%': (topic) -> assert.equal topic.alpha(), 0.5

        'constructing rgba color, hsl shorthand':
            topic: -> chroma.hsl 0,1,0.5,0.5
            'color is red': (topic) -> assert.equal topic.name(), 'red'
            'alpha is 50%': (topic) -> assert.equal topic.alpha(), 0.5

        'parsing rgba colors':
            topic: -> chroma.css 'rgba(255,0,0,.3)'
            'color is red': (topic) -> assert.equal topic.name(), 'red'
            'alpha is 30%': (topic) -> assert.equal topic.alpha(), 0.3
            'rgba output': (topic) -> assert.deepEqual topic.rgba(), [255,0,0,0.3]

        'parsing rgba colors (percentage)':
            topic: -> chroma.css 'rgba(100%,0%,0%,0.2)'
            'color is red': (topic) -> assert.equal topic.name(), 'red'
            'alpha is 20%': (topic) -> assert.equal topic.alpha(), 0.2
            'rgb output': (topic) -> assert.deepEqual topic.rgb(), [255,0,0]
            'rgba output': (topic) -> assert.deepEqual topic.rgba(), [255,0,0,0.2]

        'parsing hsla colors':
            topic: -> chroma.css 'hsla(0,100%,50%,0.25)'
            'color is red': (topic) -> assert.equal topic.name(), 'red'
            'alpha is 25%': (topic) -> assert.equal topic.alpha(), 0.25
            'rgb output': (topic) -> assert.deepEqual topic.rgb(), [255,0,0]
            'rgba output': (topic) -> assert.deepEqual topic.rgba(), [255,0,0,0.25]

        'gl output':
            topic: -> chroma.gl 1, 0, 0, 0.25
            'gloutput': (topic) -> assert.deepEqual topic.gl(), [1, 0, 0, 0.25]

        'rgba css output':
            topic: -> chroma.css 'hsla(0,100%,50%,0.25)'
            'cssoutput': -> (topic) -> assert.equal topic.css(), 'rgba(255,0,0,0.25)'

    .export(module)