## Changelog

### 2.0.3
* hsl2rgb will, like other x2rgb conversions now set the default alpha to 1

### 2.0.2
* use a more mangle-safe check for Color class constructor to fix issues with uglifyjs and terser

### 2.0.1
* added `chroma.valid()` for checking if a color can be parsed by chroma.js

### 2.0.0
* chroma.js has been ported from CoffeeScript to ES6! This means you can now import parts of chroma in your projects!
* changed HCG input space from [0..360,0..100,0..100] to [0..360,0..1,0..1] (to be in line with HSL)
* added new object unpacking (e.g. `hsl2rgb({h,s,l})`)
* changed default interpolation to `lrgb` in mix/interpolate and average.
* if colors can't be parsed correctly, chroma will now throw Errors instead of silently failing with console.errors

### 1.4.1
* chroma.scale() now interprets `null` as NaN and returns the fallback color. Before it had interpreted `null` as `0`
* added `scale.nodata()` to allow customizing the previously hard-coded fallback (aka "no data") color #cccccc


### 1.4.0
* color.hex() now automatically sets the mode to 'rgba' if the colors alpha channel is < 1. so `chroma('rgba(255,0,0,.5)').hex()` will now return `"#ff000080"` instead of `"#ff0000"`. if this is not what you want, you must explicitly set the mode to `rgb` using `.hex("rgb")`.
* bugfix in chroma.average in LRGB mode ([#187](https://github.com/gka/chroma.js/issues/187))
* chroma.scale now also works with just one color ([#180](https://github.com/gka/chroma.js/issues/180))


### 1.3.5
* added LRGB interpolation

### 1.3.4
* passing *null* as mode in scale.colors will return chroma objects

### 1.3.3

* added [color.clipped](https://gka.github.io/chroma.js/#color-clipped)
* added [chroma.distance](https://gka.github.io/chroma.js/#chroma-distance)
* added [chroma.deltaE](https://gka.github.io/chroma.js/#chroma-deltae)
* [color.set](https://gka.github.io/chroma.js/#color-set) now returns a new chroma instance
* chroma.scale now allows [disabling of internal cache](https://gka.github.io/chroma.js/#scale-cache)
* [chroma.average](https://gka.github.io/chroma.js/#chroma-average) now works with any color mode
* added unit tests for color conversions
* use hex colors as default string representation
* RGB channels are now stored as floats internally for higher precision
* bugfix with cubehelix and constant lightness
* bugfix in chroma.limits quantiles
* bugfix when running scale.colors(1)
* bugfix in hsi2rgb color conversion

### 1.2.2

* scale.colors() now returns the original colors instead of just min/max range

### 1.2.0

* added chroma.average for averaging colors

### 1.1.0

* refactored chroma.scale
* changed behaviour of scale.domain
* added scale.classes
* added scale.padding

### 1.0.2

* standardized alpha channel construction
* chroma.bezier automatically returns chroma.scale

### 1.0.1

* added simple color output to chroma.scale().colors()

### 1.0.0

* numeric interpolation does what it should
* refactored and modularized code base
* changed argument order of Color::interpolate
