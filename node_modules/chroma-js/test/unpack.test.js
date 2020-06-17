const vows = require('vows')
const assert = require('assert');
require('es6-shim');

const unpack = require('../src/utils/unpack');

// const round = (digits) => {
//     const d = Math.pow(10,digits);
//     return (v) => Math.round(v*d) / d;
// }

vows
    .describe('Testing unpack')
    .addBatch({
        'parse simple CMYK colors': {
            args()    { return assert.deepEqual(unpack([1,2,3,4]), [1,2,3,4]); },
            array()    { return assert.deepEqual(unpack([[1,2,3,4]]), [1,2,3,4]); },
            object()    { return assert.deepEqual(unpack([{c:1,m:2,y:3,k:4}], 'cmyk'), [1,2,3,4]); },
        }
    })
    .export(module);
