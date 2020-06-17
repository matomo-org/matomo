
require('es6-shim');

const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');

vows.describe('Tests for the alpha channel').addBatch({
    'setting & getting alpha channel': {
        topic: chroma('red'),
        'no arguments gets alpha': function(topic) {
            return assert.equal(topic.alpha(), 1);
        },
        'setting alpha to 0.5': function(topic) {
            return assert.equal(topic.alpha(0.5).alpha(), 0.5);
        },
        'alpha is unchanged': function(topic) {
            return assert.equal(topic.alpha(), 1);
        }
    },
    'interpolating alpha channel': {
        topic: chroma.mix(chroma('white').alpha(0), chroma('black').alpha(1), 0.3, 'rgb'),
        'hex is #b3b3b3': function(topic) {
            return assert.equal(topic.hex('rgb'), '#b3b3b3');
        },
        'hex with alpha': function(topic) {
            return assert.equal(topic.hex(), '#b3b3b34d');
        },
        'alpha is 30%': function(topic) {
            return assert.equal(topic.alpha(), 0.3);
        }
    },
    'constructing rgba color': {
        topic: new chroma.Color(255, 0, 0, 0.5, 'rgb'),
        'alpha is 50%': function(topic) {
            return assert.equal(topic.alpha(), 0.5);
        }
    },
    'constructing rgba color, rgb shorthand': {
        topic: chroma.rgb(255, 0, 0, 0.5),
        'alpha is 50%': function(topic) {
            return assert.equal(topic.alpha(), 0.5);
        }
    },
    'constructing rgba color, hsl shorthand': {
        topic: chroma.hsl(0, 1, 0.5).alpha(0.5),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 50%': function(topic) {
            return assert.equal(topic.alpha(), 0.5);
        }
    },
    'parsing hex rgba colors': {
        topic: chroma('#ff00004d'),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 30%': function(topic) {
            return assert.equal(topic.alpha(), 0.3);
        },
        'rgba output': function(topic) {
            return assert.deepEqual(topic.rgba(), [255, 0, 0, 0.3]);
        }
    },
    'parsing rgba colors': {
        topic: chroma.css('rgba(255,0,0,.3)'),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 30%': function(topic) {
            return assert.equal(topic.alpha(), 0.3);
        },
        'rgba output': function(topic) {
            return assert.deepEqual(topic.rgba(), [255, 0, 0, 0.3]);
        }
    },
    'parsing rgba colors (percentage)': {
        topic: chroma.css('rgba(100%,0%,0%,0.2)'),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 20%': function(topic) {
            return assert.equal(topic.alpha(), 0.2);
        },
        'rgb output': function(topic) {
            return assert.deepEqual(topic.rgb(), [255, 0, 0]);
        },
        'rgba output': function(topic) {
            return assert.deepEqual(topic.rgba(), [255, 0, 0, 0.2]);
        }
    },
    'parsing hsla colors': {
        topic: chroma.css('hsla(0,100%,50%,0.25)'),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 25%': function(topic) {
            return assert.equal(topic.alpha(), 0.25);
        },
        'rgb output': function(topic) {
            return assert.deepEqual(topic.rgb(), [255, 0, 0]);
        },
        'rgba output': function(topic) {
            return assert.deepEqual(topic.rgba(), [255, 0, 0, 0.25]);
        }
    },
    'constructing hsla color': {
        topic: chroma(0, 1, 0.5, 0.25, 'hsl'),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 25%': function(topic) {
            return assert.equal(topic.alpha(), 0.25);
        }
    },
    'constructing hsva color': {
        topic: chroma(0, 1, 1, 0.25, 'hsv'),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 25%': function(topic) {
            return assert.equal(topic.alpha(), 0.25);
        }
    },
    'constructing hsia color': {
        topic: chroma(0, 1, 0.3333334, 0.25, 'hsi'),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 25%': function(topic) {
            return assert.equal(topic.alpha(), 0.25);
        }
    },
    'constructing laba color': {
        topic: chroma(53.24079414130722, 80.09245959641109, 67.20319651585301, 0.25, 'lab'),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 25%': function(topic) {
            return assert.equal(topic.alpha(), 0.25);
        }
    },
    'constructing lcha color': {
        topic: chroma(53.24079414130722, 104.55176567686985, 39.99901061253297, 0.25, 'lch'),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 25%': function(topic) {
            return assert.equal(topic.alpha(), 0.25);
        }
    },
    'constructing cmyka color': {
        topic: chroma(0, 1, 1, 0, 0.25, 'cmyk'),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 25%': function(topic) {
            return assert.equal(topic.alpha(), 0.25);
        }
    },
    'gl output': {
        topic: chroma.gl(1, 0, 0, 0.25),
        'color is red': function(topic) {
            return assert.equal(topic.name(), 'red');
        },
        'alpha is 25%': function(topic) {
            return assert.equal(topic.alpha(), 0.25);
        },
        'gloutput': function(topic) {
            return assert.deepEqual(topic.gl(), [1, 0, 0, 0.25]);
        }
    },
    'rgba css output': {
        topic: chroma.css('hsla(0,100%,50%,0.25)'),
        'cssoutput': function() {
            return function(topic) {
                return assert.equal(topic.css(), 'rgba(255,0,0,0.25)');
            };
        }
    },
    'hex output': {
        topic: chroma.gl(1, 0, 0, 0.25),
        'hex': function(topic) {
            return assert.equal(topic.hex(), '#ff000040');
        },
        'rgb': function(topic) {
            return assert.equal(topic.hex('rgb'), '#ff0000');
        },
        'rgba': function(topic) {
            return assert.equal(topic.hex('rgba'), '#ff000040');
        },
        'argb': function(topic) {
            return assert.equal(topic.hex('argb'), '#40ff0000');
        }
    },
    'num output': {
        topic: chroma.gl(1, 0, 0, 0.25),
        'num ignores alpha': function(topic) {
            return assert.equal(topic.num(), 0xff0000);
        }
    },
    'setting alpha returns new instance': {
        topic: chroma('red'),
        'set alpha to 0.5': function(topic) {
            topic.alpha(0.5);
            return assert.equal(topic.alpha(), 1);
        }
    }
})
.export(module);
