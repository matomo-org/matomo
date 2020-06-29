require 'es6-shim'
vows = require 'vows'
assert = require 'assert'
chroma = require '../chroma'


vows
    .describe('Some tests for chroma.scale()')

    .addBatch

        'simple rgb scale (white-->black)':
            topic: -> chroma.scale ['white','black']
            'starts white': (topic) -> assert.equal topic(0).hex(), '#ffffff'
            'mid gray': (topic) -> assert.equal topic(0.5).hex(), '#7f7f7f'
            'ends black': (topic) -> assert.equal topic(1).hex(), '#000000'

        'simple hsv scale (white-->black)':
            topic: -> chroma.scale(['white','black']).mode('hsv')
            'starts white': (topic) -> assert.equal topic(0).hex(), '#ffffff'
            'mid gray': (topic) -> assert.equal topic(0.5).hex(), '#808080'
            'ends black': (topic) -> assert.equal topic(1).hex(), '#000000'
            'colors': (topic) -> assert.deepEqual topic.colors(), ['#ffffff', '#000000']

        'simple hsv scale (white-->black), classified':
            topic: -> chroma.scale(['white','black']).domain([0, 1], 7).mode('hsv')
            'starts white': (topic) -> assert.equal topic(0).hex(), '#ffffff'
            'mid gray': (topic) -> assert.equal topic(0.5).hex(), '#808080'
            'ends black': (topic) -> assert.equal topic(1).hex(), '#000000'
            'colors': (topic) -> assert.deepEqual topic.colors(), ['#ffffff', '#d5d5d5', '#aaaaaa', '#808080', '#555555', '#2a2a2a', '#000000']

        'simple lab scale (white-->black)':
            topic: -> chroma.scale(['white','black']).mode('lab')
            'starts white': (topic) -> assert.equal topic(0).hex(), '#ffffff'
            'mid gray': (topic) -> assert.equal topic(0.5).hex(), '#777777'
            'ends black': (topic) -> assert.equal topic(1).hex(), '#000000'

        'colorbrewer scale':
            topic: -> chroma.scale 'RdYlGn'
            'starts white': (topic) -> assert.equal topic(0).hex(), '#a50026'
            'mid gray': (topic) -> assert.equal topic(0.5).hex(), '#ffffbf'
            'ends black': (topic) -> assert.equal topic(1).hex(), '#006837'

        'colorbrewer scale - domained':
            topic: -> chroma.scale('RdYlGn').domain([0, 100])
            'starts white': (topic) -> assert.equal topic(0).hex(), '#a50026'
            'foo': (topic) -> assert.notEqual topic(10).hex(), '#ffffbf'
            'mid gray': (topic) -> assert.equal topic(50).hex(), '#ffffbf'
            'ends black': (topic) -> assert.equal topic(100).hex(), '#006837'

        'colorbrewer scale - domained - classified':
            topic: -> chroma.scale('RdYlGn').domain([0, 100], 5)
            'starts white': (topic) -> assert.equal topic(0).hex(), '#a50026'
            '10': (topic) -> assert.equal topic(10).hex(), '#a50026'
            'mid gray': (topic) -> assert.equal topic(50).hex(), '#ffffbf'
            'ends black': (topic) -> assert.equal topic(100).hex(), '#006837'
            'get colors': (topic) -> assert.deepEqual topic.colors(), ['#a50026', '#f88d52', '#ffffbf', '#86cb66', '#006837']

        'calling domain with no arguments':
            topic: -> chroma.scale('RdYlGn').domain([0, 100], 5)
            'returns domain': (topic) -> assert.deepEqual topic.domain(), [0, 20, 40, 60, 80, 100]

        # 'check spread':
        #     topic: ->
        #         cs = new chroma._ColorScale()
        #         cs.domain [0, 0.5, 1]
        #         cs
        #     'spread zero': (cs) ->
        #         assert.equal cs.classifyValue(0.25), 0.25
        #         assert.equal cs.classifyValue(0.75), 0.75
        #     'spread one': (cs) ->
        #         cs._spread = 1
        #         assert.equal cs.classifyValue(0.25), 0
        #         assert.equal cs.classifyValue(0.75), 1
        #         #assert.deepEqual res, [0.33, 0.33, 0.33, 0.33, 0.80, 100]

        'source array keeps untouched':
            topic: chroma.brewer.Blues.slice(0)
            'colors are an array': (colors) ->
                assert.equal colors.length, 9
            'colors are strings': (colors) ->
                assert.equal typeof colors[0], 'string'
            'creating a color scale': (colors) ->
                chroma.scale(colors)
                assert true
            'colors are still strings': (colors) ->
                assert.equal typeof colors[0], 'string'


        'domain with same min and max':
            topic: -> chroma.scale(['white','black']).domain([1, 1], 5)
            'returns color': (topic) -> assert.deepEqual topic(1).hex(), '#000000'

        # 'fixed color scale':
        #     topic: ->
        #         cs = new chroma.ColorScale()
        #         cs.range ['white', 'black']
        #         cs.domain [0, 0.5, 0.75, 1]
        #         cs
        #     'returns domain': (topic) -> assert.deepEqual topic.domain(), [0, 20, 40, 60, 80, 100]

    .export(module)