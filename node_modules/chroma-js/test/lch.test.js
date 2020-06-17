require('es6-shim');
const vows = require('vows');
const assert = require('assert');
const chroma = require('../index');

vows
    .describe('Some tests for chroma.lch()')

    .addBatch({

        'lch grayscale': {
            topic: (((() => {
                const result = [];
                for (let l of [0,100,25,50,75]) {
                    result.push([l,0,0]);
                }
                return result;
            })())),
            'black'(t) { return assert.equal(chroma.lch(t[0]).hex(), '#000000'); },
            'white'(t) { return assert.equal(chroma.lch(t[1]).hex(), '#ffffff'); },
            'gray 1'(t) { return assert.equal(chroma.lch(t[2]).hex(), '#3b3b3b'); },
            'gray 2'(t) { return assert.equal(chroma.lch(t[3]).hex(), '#777777'); },
            'gray 3'(t) { return assert.equal(chroma.lch(t[4]).hex(), '#b9b9b9'); }
        },

        'lch hues': {
            topic: (([0,60,120,180,240,300].map((h) => [50,70,h]))),
            'red-ish'(t) { return assert.equal(chroma.lch(t[0]).hex(), '#dc2c7a'); },
            'yellow-ish'(t) { return assert.equal(chroma.lch(t[1]).hex(), '#bd5c00'); },
            'green-ish'(t) { return assert.equal(chroma.lch(t[2]).hex(), '#548400'); },
            'teal-ish'(t) { return assert.equal(chroma.lch(t[3]).hex(), '#009175'); },
            'blue-ish'(t) { return assert.equal(chroma.lch(t[4]).hex(), '#008cde'); },
            'purple-ish'(t) { return assert.equal(chroma.lch(t[5]).hex(), '#6f67df'); }
        },

        'clipping': {
            topic: (((() => {
                const result1 = [];
                for (l of [20,40,60,80,100]) {
                    result1.push(chroma.hcl(50, 40, l));
                }
                return result1;
            })())),
            '20-clipped'(t) { return assert.equal(t[0].clipped(), true); },
            '40-not clipped'(t) { return assert.equal(t[1].clipped(), false); },
            '60-not clipped'(t) { return assert.equal(t[2].clipped(), false); },
            '80-clipped'(t) { return assert.equal(t[3].clipped(), true); },
            '100-clipped'(t) { return assert.equal(t[4].clipped(), true); }
        }}).export(module);
