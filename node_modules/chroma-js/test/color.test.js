const vows = require('vows')
const assert = require('assert');
require('es6-shim');

require('../index');
const Color = require('../src/Color');

const hexColors = {};
['#ff9900', '#FF9900', '#F90', 'f90', 'FF9900', 'FF9900F0', 'F90F', '#F90F'].forEach(hex => {
    hexColors[`detect hex ${hex}`] = {
        topic() { return () => { return new Color(hex) } },
        check: {
            noErrThrown(topic) { assert.doesNotThrow(topic); },
            hexCode(topic) { assert.strictEqual(topic().hex('rgb'), '#ff9900');}
        }
    }
});

vows
    .describe('Testing Color')
    .addBatch({
        're-use existing color instance': {
            same() {
                const c0 = new Color('red');
                return assert.strictEqual(c0, new Color(c0));
            },
        },
        'autodetect named colors': {
            topic() { return () => { return new Color('mediumslateblue') } },
            'check': {
                noErrThrown(topic) { assert.doesNotThrow(topic); },
                hexCode(topic) { assert.strictEqual(topic().hex(), '#7b68ee') }
            }
        },
        'throw err on wrong color name': {
            topic() { return () => { return new Color('fakecolor') } },
            'check': {
                errThrown(topic) { assert.throws(topic); }
            }
        },
        'autodetect correct hex colors': { hexColors }
    })
    .export(module);
