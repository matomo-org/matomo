require('es6-shim');
const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');
const scale = require('../src/generator/scale');


vows
    .describe('Some tests for scale()')

    .addBatch({

        'simple rgb scale (white-->black)': {
            topic: {
                f: scale(['white','black'])
            },
            'starts white'(topic) { assert.equal(topic.f(0).hex(), '#ffffff'); },
            'mid gray'(topic) { assert.equal(topic.f(0.5).hex(), '#808080'); },
            'ends black'(topic) { assert.equal(topic.f(1).hex(), '#000000'); }
        },

        'simple hsv scale (white-->black)': {
            topic: {
                f: scale(['white','black']).mode('hsv')
            },
            'starts white'(topic) { assert.equal(topic.f(0).hex(), '#ffffff'); },
            'mid gray'(topic) { assert.equal(topic.f(0.5).hex(), '#808080'); },
            'ends black'(topic) { assert.equal(topic.f(1).hex(), '#000000'); },
            'colors'(topic) { assert.deepEqual(topic.f.colors(), ['#ffffff', '#000000']); },
            'colors start and end'(topic) { assert.deepEqual(topic.f.colors(2), ['#ffffff', '#000000']); },
            'color mode'(topic) { assert.deepEqual(topic.f.colors(2, 'rgb')[1], [0,0,0]); },
            'color mode null len'(topic) { assert.equal(topic.f.colors(2, null).length, 2); },
            'color mode null'(topic) { assert(topic.f.colors(2, null)[0]._rgb); }
        },

        'simple hsv scale (white-->black), classified': {
            topic: {
                f: scale(['white','black']).classes(7).mode('hsv')
            },
            'starts white'(topic) { assert.equal(topic.f(0).hex(), '#ffffff'); },
            'mid gray'(topic) { assert.equal(topic.f(0.5).hex(), '#808080'); },
            'ends black'(topic) { assert.equal(topic.f(1).hex(), '#000000'); },
            'colors'(topic) { assert.deepEqual(topic.f.colors(7), ['#ffffff', '#d5d5d5', '#aaaaaa', '#808080', '#555555', '#2a2a2a', '#000000']); }
        },

        'simple lab scale (white-->black)': {
            topic: {
                f: scale(['white','black']).mode('lab')
            },
            'starts white'(topic) { assert.equal(topic.f(0).hex(), '#ffffff'); },
            'mid gray'(topic) { assert.equal(topic.f(0.5).hex(), '#777777'); },
            'ends black'(topic) { assert.equal(topic.f(1).hex(), '#000000'); }
        },

        'colorbrewer scale': {
            topic: {
                f: scale('RdYlGn')
            },
            'starts white'(topic) { assert.equal(topic.f(0).hex(), '#a50026'); },
            'mid gray'(topic) { assert.equal(topic.f(0.5).hex(), '#ffffbf'); },
            'ends black'(topic) { assert.equal(topic.f(1).hex(), '#006837'); }
        },

        'colorbrewer scale - domained': {
            topic: {
                f: scale('RdYlGn').domain([0, 100])
            },
            'starts white'(topic) { assert.equal(topic.f(0).hex(), '#a50026'); },
            'foo'(topic) { assert.notEqual(topic.f(10).hex(), '#ffffbf'); },
            'mid gray'(topic) { assert.equal(topic.f(50).hex(), '#ffffbf'); },
            'ends black'(topic) { assert.equal(topic.f(100).hex(), '#006837'); }
        },

        'colorbrewer scale - lowercase': {
            topic: {
                f: scale('rdylgn')
            },
            'starts white'(topic) { assert.equal(topic.f(0).hex(), '#a50026'); },
            'mid gray'(topic) { assert.equal(topic.f(0.5).hex(), '#ffffbf'); },
            'ends black'(topic) { assert.equal(topic.f(1).hex(), '#006837'); }
        },

        'colorbrewer scale - domained - classified': {
            topic: {
                f: scale('RdYlGn').domain([0, 100]).classes(5)
            },
            'starts white'(topic) { assert.equal(topic.f(0).hex(), '#a50026'); },
            '10'(topic) { assert.equal(topic.f(10).hex(), '#a50026'); },
            'mid gray'(topic) { assert.equal(topic.f(50).hex(), '#ffffbf'); },
            'ends black'(topic) { assert.equal(topic.f(100).hex(), '#006837'); },
            'get colors'(topic) { assert.deepEqual(topic.f.colors(5), ['#a50026', '#f98e52', '#ffffbf', '#86cb67', '#006837']); }
        },

        'calling domain with no arguments': {
            topic: {
                f: scale('RdYlGn').domain([0, 100]).classes(5)
            },
            'returns domain'(topic) { assert.deepEqual(topic.f.domain(), [0, 100]); },
            'returns classes'(topic) { assert.deepEqual(topic.f.classes(), [0, 20, 40, 60, 80, 100]); }
        },

        'source array keeps untouched': {
            topic: chroma.brewer.Blues.slice(0),
            'colors are an array'(colors) {
                assert.equal(colors.length, 9);
            },
            'colors are strings'(colors) {
                assert.equal(typeof colors[0], 'string');
            },
            'creating a color scale'(colors) {
                scale(colors);
                assert(true);
            },
            'colors are still strings'(colors) {
                assert.equal(typeof colors[0], 'string');
            }
        },


        'domain with same min and max': {
            topic: {
                f: scale(['white','black']).domain([1, 1])
            },
            'returns color'(topic) { assert.deepEqual(topic.f(1).hex(), '#000000'); }
        },

        'simple num scale (white-->black)': {
            topic: {
                f: scale(['white','black']).mode('num')
            },
            'starts white'(topic) { assert.equal(topic.f(0).hex(), '#ffffff'); },
            '25%'(topic) { assert.equal(topic.f(0.25).hex(), '#bfffff'); },
            '50%'(topic) { assert.equal(topic.f(0.5).hex(), '#7fffff'); },
            '75%'(topic) { assert.equal(topic.f(0.75).hex(), '#3fffff'); },
            '95%'(topic) { assert.equal(topic.f(0.95).hex(), '#0ccccc'); },
            'ends black'(topic) { assert.equal(topic.f(1).hex(), '#000000'); }
        },

        'css rgb colors': {
            topic: scale("YlGnBu")(0.3).css(),
            'have rounded rgb() values'(topic) { assert.equal(topic, 'rgb(170,222,183)'); }
        },

        'css rgba colors': {
            topic: scale("YlGnBu")(0.3).alpha(0.675).css(),
            'dont round alpha value'(topic) { assert.equal(topic, 'rgba(170,222,183,0.675)'); }
        },

        'get colors from a scale': {
            topic: {
                f: scale(['yellow','darkgreen'])
            },
            'just colors'(topic) { assert.deepEqual(topic.f.colors(), ['#ffff00', '#006400']); },
            'five hex colors'(topic) { assert.deepEqual(topic.f.colors(5), ['#ffff00','#bfd800','#80b200','#408b00','#006400']); },
            'three css colors'(topic) { assert.deepEqual(topic.f.colors(3,'css'), ['rgb(255,255,0)', 'rgb(128,178,0)', 'rgb(0,100,0)' ]); }
        },

        'get colors from a scale with more than two colors': {
            topic: {
                f: scale(['yellow','orange', 'darkgreen'])
            },
            'just origianl colors'(topic) { assert.deepEqual(topic.f.colors(), ['#ffff00', '#ffa500', '#006400']); }
        },

        'test example in readme': {
            topic: {
                f: scale('RdYlGn')
            },
            'five hex colors (new)'(topic) { assert.deepEqual(topic.f.colors(5), ['#a50026','#f98e52','#ffffbf','#86cb67','#006837']); }
        },

        'weird result': {
            topic: {
                f: scale([[ 0, 0, 0, 1 ], [ 255, 255, 255, 1 ]]).domain([0,10]).mode('rgb')
            },
            'has hex function at 0.5'(topic) { assert.equal(typeof topic.f(0.5).hex, 'function'); },
            'has hex function at 0'(topic) { assert.equal(typeof topic.f(0).hex, 'function'); }
        },

        'scale padding, simple': {
            topic: {
                f: scale('RdYlBu').padding(0.15)
            },
            '0'(topic) { assert.equal(topic.f(0).hex(), '#e64f35'); },
            '0.5'(topic) { assert.equal(topic.f(0.5).hex(), '#ffffbf'); },
            '1'(topic) { assert.equal(topic.f(1).hex(), '#5d91c3'); }
        },

        'scale padding, one-sided': {
            topic: {
                f: scale('OrRd').padding([0.2, 0])
            },
            '0'(topic) { assert.equal(topic.f(0).hex(), '#fddcaf'); },
            '0.5'(topic) { assert.equal(topic.f(0.5).hex(), '#f26d4b'); },
            '1'(topic) { assert.equal(topic.f(1).hex(), '#7f0000'); }
        },

        'colors return original colors': {
            topic: {
                f: scale(['red', 'white', 'blue'])
            },
            'same colors'(topic) { assert.deepEqual(topic.f.colors(), ['#ff0000', '#ffffff', '#0000ff']); }
        },

        'scale with one color': {
            topic: {
                f: scale(['red'])
            },
            'should return that color'(topic) { assert.equal(topic.f(0.3).hex(), '#ff0000'); }
        },

        'scale() no data color': {
            topic: {
                f: scale('OrRd')
            },
            'null --> nodata'(topic) { assert.equal(topic.f(null).hex(), '#cccccc'); },
            'undefined --> nodata'(topic) { assert.equal(topic.f(undefined).hex(), '#cccccc'); },
            'custom nodata color'(topic) { assert.equal(topic.f.nodata('#eee')(undefined).hex(), '#eeeeee'); }
        },

        'scale wrapped in a scale': {
            topic: {
                f1: scale('OrRd'),
                f: scale('OrRd').domain([0,.25,1])
            },
            'start'(topic) { assert.equal(topic.f(0).hex(), topic.f1(0).hex()) },
            'end'(topic) { assert.equal(topic.f(1).hex(), topic.f1(1).hex()) },
            'center'(topic) { assert.equal(topic.f(0.125).hex(), topic.f1(0.25).hex()) },
            'center2'(topic) { assert.equal(topic.f(0.25).hex(), topic.f1(0.5).hex()) },
            'center3'(topic) { assert.equal(topic.f(0.625).hex(), topic.f1(0.75).hex()) },
        },


    }).export(module);
