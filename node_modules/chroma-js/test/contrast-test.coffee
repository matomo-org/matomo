require 'es6-shim'
vows = require 'vows'
assert = require 'assert'
chroma = require '../chroma'


vows
    .describe('Testing contrast ratio')

    .addBatch

        'maximum contrast':
            topic: -> chroma.contrast 'black', 'white'
            'is 21:1': (topic) -> assert.equal topic, 21

        'minimum contrast':
            topic: -> chroma.contrast 'white', 'white'
            'is 1:1': (topic) -> assert.equal topic, 1

        'contrast between white and red':
            topic: -> chroma.contrast 'red', 'white'
            'is 4:1': (topic) -> assert.equal Math.round(topic,5), 4

    .export(module)