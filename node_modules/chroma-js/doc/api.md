# Initializing colors

## chroma(a, b, c, [a], [mode])

Generic color factory. Returns an instance of chroma.Color. mode defaults to "rgb".

The following calls all return the same color (red #ff0000):

```javascript
chroma("red");
chroma("#ff0000");
chroma("#f00");
chroma("FF0000");
chroma(255, 0, 0);
chroma([255, 0, 0]);
chroma(0, 1, 0.5, 'hsl');
chroma([0, 1, 0.5], 'hsl');
chroma(0, 1, 1, 'hsv');
chroma("rgb(255,0,0)");
chroma("rgb(100%,0%,0%)");
chroma("hsl(0,100%,50%)");
chroma(53.24, 80.09, 67.20, 'lab');
chroma(53.24, 104.55, 40, 'lch');
chroma(1, 0, 0, 'gl');
```


## chroma.hex() / chroma.css()

Returns a color from a hex code or css color. Alias: **chroma.css()**

```javascript
chroma.hex("#ff0000");
chroma.hex("red");
chroma.hex("rgb(255, 0, 0)");
```
## chroma.*xyz*()

Creates a chroma.Color instance from a specific color space. Shortcut to *chroma(…, mode)*.

```javascript
chroma.rgb(255, 0, 0);
chroma.hsl(0, 1, 0.5);
chroma.hsv(120, 0.5, 0.5);
chroma.lab(53.24, 80.09, 67.20);
chroma.lch(53.24, 104.55, 40);
chroma.gl(1, 0, 0);
```

## chroma.interpolate(color1, color2, f, mode)

Colors can be also be interpolates between two other colors in a given mode.

```
chroma.interpolate('white', 'black', 0)  // #ffffff
chroma.interpolate('white', 'black', 1)  // #000000
chroma.interpolate('white', 'black', 0.5)  // #7f7f7f
chroma.interpolate('white', 'black', 0.5, 'hsv')  // #808080
chroma.interpolate('white', 'black', 0.5, 'lab')  // #777777
```

This also works with colors with alpha channel:

```
chroma.interpolate('rgba(0,0,0,0)', 'rgba(255,0,0,1)', 0.5).css()  //"rgba(127.5,0,0,0.5)"
```

## chroma.interpolate.bezier(colors)

Colors can be also be interpolates between two other colors in a given mode.

```
bezInterpolator = chroma.interpolate.bezier(['white', 'yellow', 'red', 'black']);
bezInterpolator(0).hex()  // #ffffff
bezInterpolator(0.33).hex()  // #ffcc67
bezInterpolator(0.66).hex()  // #b65f1a
bezInterpolator(1).hex()  // #000000
```

# Working with chroma.colors

Here's what you can do with it:

* [color.hex|css|rgb|hsv|hsl|lab|lch()](#colorxxx)
* [color.alpha()](#coloralpha)
* [color.darker()](#colordarkeramount)
* [color.brighter()](#colorbrighteramount)
* [color.saturate()](#colorsaturateamount)
* [color.desaturate()](#colordesaturateamount)
* [color.luminance()](#colorluminance)

### color.*xxx*()

Returns the color components for a specific color space:

```javascript
chroma('red').hex()  // "#FF0000""
chroma('red').rgb()  // [255, 0, 0]
chroma('red').hsv()  // [0, 1, 1]
chroma('red').hsl()  // [0, 1, 0.5]
chroma('red').lab()  // [53.2407, 80.0924, 67.2031]
chroma('red').lch()  // [53.2407, 104.5517, 39.9990]
chroma('red').rgba() // [255, 0, 0, 1]
chroma('red').css()  // "rgb(255,0,0)"
chroma('red').alpha(0.7).css()  // "rgba(255,0,0,0.7)"
chroma('red').css('hsl')        // "hsl(0,100%,50%)"
chroma('red').alpha(0.7).css('hsl')  // "hsla(0,100%,50%,0.7)"
chroma('blue').css('hsla') // "hsla(240,100%,50%,1)"
```

### color.alpha()

Returns or sets the colors alpha value.

```
var red = chroma('red');
red.alpha(0.5);
red.css();  // rgba(255,0,0,0.5);
```

### color.darker(*amount*)

Decreases the lightness of the color in *Lab* color space.

```javascript
chroma('red').darken().hex()  // #BC0000
```

### color.brighter(*amount*)

```javascript
chroma('red').brighten().hex()  // #FF603B
```

### color.saturate(*amount*)

Returns a more saturated variation of the color.

```javascript
chroma('#eecc99').saturate().hex() // #fcc973
```

### color.desaturate(*amount*)

Returns a less saturated variation of the color.

```javascript
chroma('red').desaturate().hex() // #ec3d23
```

### color.luminance()

Returns the [relative luminance](http://www.w3.org/TR/WCAG20/#relativeluminancedef) of the color, which is a value between 0 (black) and 1 (white).

```javascript
chroma('black').luminance() // 0
chroma('white').luminance() // 1
chroma('red').luminance() // 0.2126
```

As of version 0.6.2 you can also set the luminance directly:

```javascript
chroma('#ff0000').luminance(0.4).hex() // #ff8585"
```

# Working with color scales

## chroma.scale()

Creates a color scale function from the given set of colors.

```javascript
var scale = chroma.scale(['lightyellow', 'navy']);
scale(0.5);  // #7F7FB0
```

Need some advice for good colors? How about using a pre-defined [ColorBrewer](http://colorbrewer2.com) scale:

```javascript
chroma.scale('RdYlBu');
```

### scale.out()

By default the color scale functions return instances of chroma.Color.

```javascript
var col = scale(0.5);
col.hex();  // #7F7FB0
col.rgb();  // [127.5, 127.5, 176]
```

Using **scale.out()** you can configure the color scale to automatically return colors in the desired format.

```javascript
scale = chroma.scale(['lightyellow', 'navy']).out('hex');
scale(0.5);  // "#7F7FB0"
```

### scale.mode()

Specify in which color space the colors should be interpolated. Defaults to "rgb". You can use any of the following spaces:

```javascript
var scale = chroma.scale(['lightyellow', 'navy']);
scale.mode('hsv')(0.5);  // #54C08A
scale.mode('hsl')(0.5);  // #31FF98
scale.mode('lab')(0.5);  // #967CB2
scale.mode('lch')(0.5);  // #D26662
```

### scale.domain()

You can specify the input range of your data (defaults to [0..1]).

```javascript
var scale = chroma.scale(['lightyellow', 'navy']).domain([0, 400]);
scale(200);  // #7F7FB0
```

Instead of just passing the minimum and maximum values you can specify custom "stops". chroma.js would now return a distinct set of four different colors:

```javascript
var scale = chroma.scale(['lightyellow', 'navy'])
.domain([0, 100, 200, 300, 400]);
scale(98);  // #7F7FB0
scale(99);  // #7F7FB0
scale(100);  // #AAAAC0
scale(101);  // #AAAAC0
```

If you don't want to pick the stops by hand, you can auto-generate a set of *N* equidistant input classes:

```javascript
chroma.scale(['#eee', '#900']).domain([0, 400], 7);
```

Don't like linear scales? How about logarithmic stops?

```javascript
chroma.scale(['#eee', '#900']).domain([1, 1000000], 7, 'log');
```

For more advanced techniques you need the actual dataset

```javascript
chroma.scale(['#eee', '#900']).domain(values, 5, 'quantiles');
chroma.scale(['#eee', '#900']).domain(values, 5, 'k-means');
```

Calling .domain() with no arguments will return the current domain.

```
chroma.scale(['white', 'red']).domain([0, 100], 4).domain() // [0, 25, 50, 75, 100]
```

### scale.range()

If you need to change the color range after initializing the color scale.

```javascript
chroma.scale().range(['lightyellow', 'navy']);
```

### scale.correctLightness()

As of version 0.5.2 chroma.scale supports automatic lightness correction of color scales.

**Important note:** The lightness correction only works for sequential color scales, where the input colors are ordered by lightness. So this won’t work for diverging color scales, yet.

```javascript
chroma.scale(['lightyellow', 'navy']).correctLightness(true);
```

### scale.colors(mode='hex')

If your color scale has set a distinct number of classes, scale.colors() can be used to retreive all possible colors generated by this scale.

```javascript
chroma.scale('RdYlGn').domain([0,1], 5).colors()
// returns ['#a50026', '#f88d52', '#ffffbf', '#86cb66', '#006837']
```

# Useful methods

## chroma.luminance

Shortcut for the color.luminance()

```javascript
chroma.luminance('black') // 0
chroma.luminance('white') // 1
chroma.luminance('#ff0000') // 0.2126
```

## chroma.contrast(a, b)

Returns the [contrast ratio](http://www.w3.org/TR/WCAG20/#contrast-ratiodef) between two given colors. According to the [Web Content Accessibility Guidelines](http://www.w3.org/TR/WCAG20) the contrast between background and small text [should be at least](http://www.w3.org/TR/WCAG20/#visual-audio-contrast-contrast) 4.5 : 1.

```javascript
chroma.contrast('white', 'navy')  // 16.00 – ok
chroma.contrast('white', 'yellow')  // 1.07 – not ok!
```
