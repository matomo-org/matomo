const chroma = require('./src/chroma');

// feel free to comment out anything to rollup
// a smaller chroma.js built

// io --> convert colors
require('./src/io/css');
require('./src/io/hex');
require('./src/io/hsl');
require('./src/io/lab');
require('./src/io/rgb');

// operators --> modify existing Colors
require('./src/ops/alpha');
require('./src/ops/darken');
require('./src/ops/get');
require('./src/ops/mix');
require('./src/ops/set');

// interpolators
require('./src/interpolator/lrgb');

// generators -- > create new colors
chroma.mix = chroma.interpolate = require('./src/generator/mix');

// other utility methods
chroma.valid = require('./src/utils/valid');

module.exports = chroma;
