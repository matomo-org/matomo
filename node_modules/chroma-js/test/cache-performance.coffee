require 'es6-shim'
vows = require 'vows'
assert = require 'assert'
chroma = require '../chroma'



s = chroma.scale('RdYlGn')
    .mode('lab')
    .domain([0,100000], 10)

t0 = new Date().getTime()

for i in [1..100000]
    s(i).hex()

t1 = new Date().getTime()

console.log (t1 - t0) + "ms"
