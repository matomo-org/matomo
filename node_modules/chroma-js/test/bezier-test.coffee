require 'es6-shim'
vows = require 'vows'
assert = require 'assert'
chroma = require '../chroma'


vows
    .describe('Testing bezier interpolation')

    .addBatch

        'simple two color linear interpolation':
            topic: -> chroma.interpolate.bezier ['white', 'black']
            'starts from white': (topic) -> assert.equal topic(0).hex(), '#ffffff'
            'ends in black': (topic) -> assert.equal topic(1).hex(), '#000000'
            'center is grey': (topic) -> assert.equal topic(0.5).hex(), '#777777'

        'three color quadratic bezier interpolation':
            topic: -> chroma.interpolate.bezier ['white', 'red', 'black']
            'starts from white': (topic) -> assert.equal topic(0).hex(), '#ffffff'
            'ends in black': (topic) -> assert.equal topic(1).hex(), '#000000'
            'center is a greyish red': (topic) -> assert.equal topic(0.5).hex(), '#c45c44'

        'four color cubic bezier interpolation':
            topic: -> chroma.interpolate.bezier ['white', 'yellow', 'red', 'black']
            'starts from white': (topic) -> assert.equal topic(0).hex(), '#ffffff'
            'ends in black': (topic) -> assert.equal topic(1).hex(), '#000000'
            '1st quarter': (topic) -> assert.equal topic(0.25).hex(), '#ffe085'
            'center': (topic) -> assert.equal topic(0.5).hex(), '#e69735'
            '3rd quarter': (topic) -> assert.equal topic(0.75).hex(), '#914213'

        'five color diverging quadratic bezier interpolation':
            topic: -> chroma.interpolate.bezier ['darkred', 'orange', 'snow', 'lightgreen', 'royalblue']
            'starts from darkred': (topic) -> assert.equal topic(0).hex(), '#8b0000'
            'ends in royalblue': (topic) -> assert.equal topic(1).hex(), '#4169e1'
            'center is snow': (topic) -> assert.equal topic(0.5).hex(), '#fffafa'
            '1st quarter': (topic) -> assert.equal topic(0.25).hex(), '#e9954e'
            '3rd quarter': (topic) -> assert.equal topic(0.75).hex(), '#a6cfc1'

        'using bezier in a chroma.scale':
            topic: ->
                bez = chroma.interpolate.bezier ['darkred', 'orange', 'snow', 'lightgreen', 'royalblue']
                chroma.scale(bez).domain([0,1],5).out('hex')
            'starts from darkred': (topic) -> assert.equal topic(0), '#8b0000'
            'ends in royalblue': (topic) -> assert.equal topic(1), '#4169e1'
            'center is snow': (topic) -> assert.equal topic(0.5), '#fffafa'
            '1st quarter': (topic) -> assert.equal topic(0.25), '#e9954e'
            '3rd quarter': (topic) -> assert.equal topic(0.75), '#a6cfc1'


    .export(module)