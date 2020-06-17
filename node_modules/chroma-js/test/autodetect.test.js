
require('es6-shim');

const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');


vows.describe('Some tests for chroma.color()').addBatch({
    'named colors': {
        topic: chroma('red'),
        'hex': function(topic) {
            return assert.equal(topic.hex(), '#ff0000');
        },
        'rgb': function(topic) {
            return assert.deepEqual(topic.rgb(), [255, 0, 0]);
        }
    },
    'hex colors': {
        topic: chroma('#f00'),
        'name': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'hex': function(topic) {
            return assert.equal(topic.hex(), '#ff0000');
        },
        'hex rgba': function(topic) {
            return assert.equal(topic.hex('rgba'), '#ff0000ff');
        },
        'hex argb': function(topic) {
            return assert.equal(topic.hex('argb'), '#ffff0000');
        },
        'rgb': function(topic) {
            return assert.deepEqual(topic.rgb(), [255, 0, 0]);
        }
    },
    'hex color, no #': {
        topic: chroma('F00'),
        'name': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'hex': function(topic) {
            return assert.equal(topic.hex(), '#ff0000');
        },
        'rgb': function(topic) {
            return assert.deepEqual(topic.rgb(), [255, 0, 0]);
        }
    },
    'css color rgb': {
        topic: chroma('rgb(255,0,0)'),
        'hex': function(topic) {
            return assert.equal(topic.hex(), '#ff0000');
        }
    },
    'rgba css color': {
        topic: chroma('rgba(128,0,128,0.5)'),
        'hex': function(topic) {
            return assert.equal(topic.hex(), '#80008080');
        },
        'hex rgb': function(topic) {
            return assert.equal(topic.hex('rgb'), '#800080');
        },
        'alpha': function(topic) {
            return assert.equal(topic.alpha(), 0.5);
        },
        'css': function(topic) {
            return assert.equal(topic.css(), 'rgba(128,0,128,0.5)');
        }
    },
    'hsla css color': {
        topic: chroma('hsla(240,100%,50%,0.5)'),
        'hex': function(topic) {
            return assert.equal(topic.hex(), '#0000ff80');
        },
        'hex rgb': function(topic) {
            return assert.equal(topic.hex('rgb'), '#0000ff');
        },
        'alpha': function(topic) {
            return assert.equal(topic.alpha(), 0.5);
        },
        'css': function(topic) {
            return assert.equal(topic.css(), 'rgba(0,0,255,0.5)');
        }
    },
    'hsla color': {
        topic: chroma('lightsalmon'),
        'css (default)': function(topic) {
            return assert.equal(topic.css(), 'rgb(255,160,122)');
        },
        'css (rgb)': function(topic) {
            return assert.equal(topic.css('rgb'), 'rgb(255,160,122)');
        },
        'css (hsl)': function(topic) {
            return assert.equal(chroma(topic.css('hsl')).name(), 'lightsalmon');
        },
        'css (rgb-css)': function(topic) {
            return assert.equal(chroma(topic.css('rgb')).name(), 'lightsalmon');
        }
    },
    'rgb color': {
        topic: chroma(255, 0, 0),
        'hex': function(topic) {
            return assert.equal(topic.hex(), '#ff0000');
        }
    },
    'hsv black': {
        topic: chroma('black').hsv(),
        'hue is NaN': function(topic) {
            return assert(isNaN(topic[0]));
        },
        'but hue is defined': function(topic) {
            return assert(topic[0] != null);
        }
    },
    'hsl black': {
        topic: chroma('black').hsl(),
        'hue is NaN': function(topic) {
            return assert(isNaN(topic[0]));
        },
        'but hue is defined': function(topic) {
            return assert(topic[0] != null);
        },
        'sat is 0': function(topic) {
            return assert.equal(topic[1], 0);
        },
        'lightness is 0': function(topic) {
            return assert.equal(topic[2], 0);
        }
    },
    'constructing with array, but no mode': {
        topic: chroma([255, 0, 0]),
        'falls back to rgb': function(topic) {
            return assert.equal(topic.hex(), chroma([255, 0, 0], 'rgb').hex());
        }
    },
    'num color': {
        topic: [chroma(0xff0000), chroma(0x000000), chroma(0xffffff), chroma(0x31ff98), chroma('red')],
        'hex': function(topic) {
            return assert.equal(topic[0].hex(), '#ff0000');
        },
        'num': function(topic) {
            return assert.equal(topic[0].num(), 0xff0000);
        },
        'hex-black': function(topic) {
            return assert.equal(topic[1].hex(), '#000000');
        },
        'num-black': function(topic) {
            return assert.equal(topic[1].num(), 0x000000);
        },
        'hex-white': function(topic) {
            return assert.equal(topic[2].hex(), '#ffffff');
        },
        'num-white': function(topic) {
            return assert.equal(topic[2].num(), 0xffffff);
        },
        'hex-rand': function(topic) {
            return assert.equal(topic[3].hex(), '#31ff98');
        },
        'num-rand': function(topic) {
            return assert.equal(topic[3].num(), 0x31ff98);
        },
        'rum-red': function(topic) {
            return assert.equal(topic[4].num(), 0xff0000);
        }
    }
})["export"](module);
