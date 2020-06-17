require('es6-shim');

const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');


const round = function(digits) {
    var d;
    d = Math.pow(10, digits);
    return function(v) {
        return Math.round(v * d) / d;
    };
};

vows.describe('Some tests for chroma.color()').addBatch({
    'hsv black': {
        topic: chroma('black').hsv(),
        'hue is NaN': function(topic) {
            return assert(isNaN(topic[0]));
        },
        'but hue is defined': function(topic) {
            return assert(topic[0] != null);
        }
    },
    'toString': {
        topic: chroma('greenyellow'),
        'explicit': function(topic) {
            return assert.equal(topic.toString(), '#adff2f');
        },
        'implicit': function(topic) {
            return assert.equal('' + topic, '#adff2f');
        },
        'implicit2': function(topic) {
            return assert.equal(String(topic), '#adff2f');
        }
    },
    'constructing numeric color': {
        topic: chroma.num(0xadff2f),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'greenyellow');
        },
        'alpha is 100%': function(topic) {
            return assert.equal(topic.alpha(), 1);
        }
    },
    'normalize hue': {
        topic: chroma.rgb(0, 255, 255).lch(),
        'hue > 0': function(topic) {
            return assert(topic[2] >= 0);
        },
        'hue < 360': function(topic) {
            return assert(topic[2] <= 360);
        }
    },
    'lab conversion red': {
        topic: chroma('red').lab().map(round(3)),
        'is right': function(topic) {
            return assert.deepEqual(topic, [53.241, 80.092, 67.203]);
        }
    },
    'lab conversion blue': {
        topic: chroma('blue').lab().map(round(3)),
        'is right': function(topic) {
            return assert.deepEqual(topic, [32.297, 79.188, -107.86]);
        }
    },
    'lab conversion green': {
        topic: chroma('green').lab().map(round(3)),
        'is right': function(topic) {
            return assert.deepEqual(topic, [46.227, -51.698, 49.897]);
        }
    },
    'lab conversion yellow': {
        topic: chroma('yellow').lab().map(round(3)),
        'is right': function(topic) {
            return assert.deepEqual(topic, [97.139, -21.554, 94.478]);
        }
    },
    'lab conversion magenta': {
        topic: chroma('magenta').lab().map(round(3)),
        'is right': function(topic) {
            return assert.deepEqual(topic, [60.324, 98.234, -60.825]);
        }
    },
    'hueless css hsl colors': {
        topic: [chroma('black'), chroma('gray'), chroma('white')],
        'black': function(topic) {
            return assert.equal(topic[0].css('hsl'), 'hsl(0,0%,0%)');
        },
        'gray': function(topic) {
            return assert.equal(topic[1].css('hsl'), 'hsl(0,0%,50.2%)');
        },
        'white': function(topic) {
            return assert.equal(topic[2].css('hsl'), 'hsl(0,0%,100%)');
        }
    },
    'hcl.h is NaN for hue-less colors': {
        topic: [chroma('black'), chroma('gray'), chroma('white')],
        'black': function(topic) {
            return assert.isNaN(topic[0].hcl()[0]);
        },
        'gray': function(topic) {
            return assert.isNaN(topic[1].hcl()[0]);
        },
        'white': function(topic) {
            return assert.isNaN(topic[2].hcl()[0]);
        }
    },
    'lab-rgb precision': {
        topic: [74, 24, 78],
        'to_rgb_to_lab': function(topic) {
            return assert.deepEqual(chroma.rgb(chroma.lab(topic).rgb(false)).lab().map(round(3)), topic);
        }
    },
    'cmyk-rgb precision': {
        topic: [0, 1, 1, 0.02],
        'to_rgb_to_cmyk': function(topic) {
            return assert.deepEqual(chroma.rgb(chroma.cmyk(topic).rgb(false)).cmyk().map(round(3)), topic);
        }
    },
    'auto-detect rgba in hex output': {
        topic: ['rgba(255,0,0,1)', 'rgba(255,0,0,0.5)'],
        'rgb if alpha == 1': function(topic) {
            return assert.equal(chroma(topic[0]).hex(), '#ff0000');
        },
        'rgba if alpha != 1': function(topic) {
            return assert.equal(chroma(topic[1]).hex(), '#ff000080');
        }
    }
})["export"](module);
