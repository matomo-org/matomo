require 'es6-shim'
vows = require 'vows'
assert = require 'assert'
chroma = require '../chroma'


rnd = (f,d) ->
    d = Math.pow(10,d)
    Math.round(f*d) / d

vows
    .describe('Testing relative luminance')

    .addBatch

        'black':
            topic: -> chroma.color 'black'
            'lum = 0': (topic) -> assert.equal topic.luminance(), 0

        'white':
            topic: -> chroma.color 'white'
            'lum = 1': (topic) -> assert.equal topic.luminance(), 1

        'red':
            topic: -> chroma.color 'red'
            'lum = 0.21': (topic) -> assert.equal topic.luminance(), 0.2126

        'yellow brighter than blue':
            topic: -> [chroma.color('yellow').luminance(), chroma.color('blue').luminance()]
            'yellow > blue': (topic) -> assert topic[0] > topic[1]

        'green darker than red':
            topic: -> [chroma.color('green').luminance(), chroma.color('red').luminance()]
            'green < red': (topic) -> assert topic[0] < topic[1]

        # setting luminance
        'set red luminance to 0.4':
            topic: -> chroma.color('red').luminance 0.4
            'lum = 0.4': (topic) -> assert.equal rnd(topic.luminance(),3), 0.4

        # setting luminance
        'set red luminance to 0':
            topic: -> chroma.color('red').luminance 0
            'lum = 0': (topic) -> assert.equal rnd(topic.luminance(),2), 0
            'hex = #000': (topic) -> assert.equal topic.hex(), '#000000'

        # setting luminance
        'set black luminance to 0.5':
            topic: -> chroma.color('black').luminance 0.5
            'lum = 0.5': (topic) -> assert.equal rnd(topic.luminance(),2), 0.5
            'hex = #999': (topic) -> assert.equal '#bbbbbb', topic.hex()

            # setting luminance
        'set black luminance to 0.5 (lab)':
            topic: -> chroma.color('black').luminance 0.5, 'lab'
            'lum = 0.5': (topic) -> assert.equal rnd(topic.luminance(),2), 0.5
            'hex = #999': (topic) -> assert.equal '#bbbbbb', topic.hex()


    .export(module)