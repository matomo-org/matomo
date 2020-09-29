require 'es6-shim'
vows = require 'vows'
assert = require 'assert'
chroma = require '../chroma'


vows
    .describe('Some tests for chroma.color()')

    .addBatch

        'named colors':
            topic: chroma 'red'
            'hex': (topic) -> assert.equal topic.hex(), '#ff0000'
            'rgb': (topic) -> assert.deepEqual topic.rgb(), [255,0,0]

        'hex colors':
            topic: chroma '#f00'
            'name': (topic) -> assert.equal topic.name(), 'red'
            'hex': (topic) -> assert.equal topic.hex(), '#ff0000'
            'rgb': (topic) -> assert.deepEqual topic.rgb(), [255,0,0]

        'hex color, no #':
            topic: chroma 'F00'
            'name': (topic) -> assert.equal topic.name(), 'red'
            'hex': (topic) -> assert.equal topic.hex(), '#ff0000'
            'rgb': (topic) -> assert.deepEqual topic.rgb(), [255,0,0]

        'gl color':
            topic: chroma.gl 1,0,0
            'name': (topic) -> assert.equal topic.name(), 'red'
            'hex': (topic) -> assert.equal topic.hex(), '#ff0000'
            'rgb': (topic) -> assert.deepEqual topic.rgb(), [255,0,0]

        'gl color w/ alpha':
            topic: chroma.gl 0,0,1,0.5
            'rgba': (topic) -> assert.deepEqual topic.rgba(), [0,0,255,0.5]

        'modify colors':
            topic: chroma 'F00'
            'darken': (topic) -> assert.equal topic.darken(10).hex(), '#dd0000'
            'darker': (topic) -> assert.equal topic.darker(10).hex(), '#dd0000'
            'brighten': (topic) -> assert.equal topic.brighten(10).hex(), '#ff3e20'
            'brighter': (topic) -> assert.equal topic.brighter(10).hex(), '#ff3e20'
            'saturate': (topic) -> assert.equal topic.saturate().hex(), '#ff0000'
            'desaturate': (topic) -> assert.equal topic.desaturate().hex(), '#ec3d23'

        'parsing css color rgb':
            topic: chroma 'rgb(255,0,0)'
            'hex': (topic) -> assert.equal topic.hex(), '#ff0000'

        'parsing rgba css color':
            topic: chroma 'rgba(128,0,128,0.5)'
            'hex': (topic) -> assert.equal topic.hex(), '#800080'
            'alpha': (topic) -> assert.equal topic.alpha(), 0.5
            'css': (topic) -> assert.equal topic.css(), 'rgba(128,0,128,0.5)'

        'parsing hsla css color':
            topic: chroma 'hsla(240,100%,50%,0.5)'
            'hex': (topic) -> assert.equal topic.hex(), '#0000ff'
            'alpha': (topic) -> assert.equal topic.alpha(), 0.5
            'css': (topic) -> assert.equal topic.css(), 'rgba(0,0,255,0.5)'

        'hsla color':
            topic: chroma 'lightsalmon'
            'css (default)': (topic) -> assert.equal topic.css(), 'rgb(255,160,122)'
            'css (rgb)': (topic) -> assert.equal topic.css('rgb'), 'rgb(255,160,122)'
            'css (hsl)': (topic) -> assert.equal chroma(topic.css('hsl')).name(), 'lightsalmon'
            'css (rgb-css)': (topic) -> assert.equal chroma(topic.css('rgb')).name(), 'lightsalmon'

        'rgb color':
            topic: chroma 255,0,0
            'hex': (topic) -> assert.equal topic.hex(), '#ff0000'

        'hsv black':
            topic: chroma('black').hsv()
            'hue is NaN': (topic) -> assert isNaN topic[0]
            'but hue is defined': (topic) -> assert topic[0]?

        'interpolate in hsv':
            topic: chroma.interpolate 'white', 'black', 0.5, 'hsv'
            'hex': (topic) -> assert.equal topic.hex(), '#808080'

        'hsl black':
            topic: chroma('black').hsl()
            'hue is NaN': (topic) -> assert isNaN topic[0]
            'but hue is defined': (topic) -> assert topic[0]?
            'sat is 0': (topic) -> assert.equal topic[1], 0
            'lightness is 0': (topic) -> assert.equal topic[2], 0

        'interpolate in hsl':
            topic: chroma.interpolate 'lightyellow', 'navy', 0.5, 'hsl'
            'hex': (topic) -> assert.equal topic.hex(), '#31ff98'

        'premultiply':
            topic: chroma 'rgba(32, 48, 96, 0.5)'
            'premultiply rgba': (topic) -> assert.deepEqual topic.premultiply().rgba(), [16, 24, 48, 0.5]
            'premultiply hex': (topic) -> assert.equal topic.premultiply().hex(), '#101830'

        'toString':
            topic: chroma '#adff2f'
            'explicit': (topic) -> assert.equal topic.toString(), 'greenyellow'
            'implicit': (topic) -> assert.equal ''+topic, 'greenyellow'
            'implicit2': (topic) -> assert.equal String(topic), 'greenyellow'

        'constructing with array, but no mode':
            topic: chroma [255, 0, 0]
            'falls back to rgb': (topic) -> assert.equal topic.hex(), chroma([255, 0, 0],'rgb').hex()

        'css rgb colors':
            topic: chroma.scale("YlGnBu")(0.3).css()
            'have rounded rgb() values': (topic) -> assert.equal topic, 'rgb(170,222,183)'

        'css rgba colors':
            topic: chroma.scale("YlGnBu")(0.3).alpha(0.675).css()
            'dont round alpha value': (topic) -> assert.equal topic, 'rgba(170,222,183,0.675)'

    .export(module)
