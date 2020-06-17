// test all the snippets in docs/
require('es6-shim');

const vows = require('vows');
const assert = require('assert');
const chroma = require('../chroma.min');

const DOCS = require('fs').readFileSync(__dirname+'/../docs/src/index.md', 'utf-8');

const snippets = DOCS.match(/^```js$\n(^[^`].+$\n)+/gm)
    .map(s => { return s.split('\n').slice(1).join('\n'); });

var data = [2.0,3.5,3.6,3.8,3.8,4.1,4.3,4.4,
            4.6,4.9,5.2,5.3,5.4,5.7,5.8,5.9,
            6.2,6.5,6.8,7.2,8];

const tests = {};
snippets.forEach((code, i) => {
    if (code.indexOf('function') > -1) return;
    if (code.indexOf('### ') > -1) return;
    tests[`run code snippet ${i}`] = {
        topic: function() {
            return function() {
                eval(code)
            }
        },
        'no errors thrown'(topic) {
            assert.doesNotThrow(topic, Error, code);
        }
    }
});

vows.describe('Tests all snippets in the documentation')
    .addBatch(tests)
    .export(module);

