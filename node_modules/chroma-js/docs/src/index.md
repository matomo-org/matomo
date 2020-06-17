
# chroma.js

**chroma.js** is a [small-ish](https://bundlephobia.com/result?p=chroma-js) zero-dependency JavaScript library ([13.5kB](https://bundlephobia.com/result?p=chroma-js)) for all kinds of color conversions and color scales.

[![Build Status](https://api.travis-ci.com/gka/chroma.js.svg?branch=master)](https://travis-ci.com/gka/chroma.js)

## Quick-start

Here are a couple of things chroma.js can do for you:

* read colors from a wide range of formats
* analyze and manipulate colors
* convert colors into wide range of formats
* linear and bezier interpolation in different color spaces

Here's an example for a simple read / manipulate / output chain:

```js
chroma('pink').darken().saturate(2).hex()
```

Aside from that, chroma.js can also help you **generate nice colors** using various methods, for instance to be [used](https://www.vis4.net/blog/posts/avoid-equidistant-hsv-colors/) in color palette for maps or data visualization.

```js
chroma.scale(['#fafa6e','#2A4858'])
    .mode('lch').colors(6)
```

chroma.js has a lot more to offer, but that's the gist of it.

## API

### chroma
#### (*color*)

The first step is to get your color into chroma.js. That's what the generic constructor ``chroma()`` does. This function attempts to guess the format of the input color for you. For instance, it will recognize any named color from the W3CX11 specification:

```js
chroma('hotpink')
```

If there's no matching named color, chroma.js checks for a **hexadecimal string**. It ignores case, the `#` sign is optional, and it can  recognize the shorter three letter format as well. So, any of these are valid hexadecimal representations: `#ff3399`, `FF3399`, `#f39`, etc.

```js
chroma('#ff3399');
chroma('F39');
```

In addition to hex strings, **hexadecimal numbers** (in fact, just any number between `0` and `16777215`) will be recognized, too.

```js
chroma(0xff3399)
```

You also can pass RGB values individually. Each parameter must be within `0..255`. You can pass the numbers as individual arguments or as an array.

```js
chroma(0xff, 0x33, 0x99);
chroma(255, 51, 153);
chroma([255, 51, 153]);
```

You can construct colors from different color spaces by passing the name of color space as the last argument. Here we define the same color in HSL by passing the h*ue angle (0-360) and percentages for *s*aturation and *l*ightness:

```js
chroma(330, 1, 0.6, 'hsl')
```

**New (since 2.0):** you can also construct colors by passing an plain JS object with attributes corresponding to a color space supported by chroma.js:

```js
chroma({ h:120, s:1, l:0.75});
chroma({ l:80, c:25, h:200 });
chroma({ c:1, m:0.5, y:0, k:0.2});
```

### chroma.valid

Also new: you can use `chroma.valid` to try if a color argument can be correctly parsed as color by chroma.js:

```js
chroma.valid('red');
chroma.valid('bread');
chroma.valid('#F0000D');
chroma.valid('#FOOOOD');
```


### chroma.hsl
#### (hue, saturation, lightness)

Alternatively, every color space has its own constructor function under the `chroma` namespace. For a list of all supported color spaces, check the [appendix](#supported-color-spaces-and-output-formats).

```js
chroma.hsl(330, 1, 0.6)
```

### chroma.hsv
#### (hue, saturation, value)

### chroma.lab
#### (Lightness, a, b)

### chroma.lch
#### (Lightness, chroma, hue)

The range for `lightness` and `chroma` depend on the hue, but go roughly from 0..100-150. The range for `hue` is 0..360.

```js
chroma.lch(80, 40, 130);
chroma(80, 40, 130, 'lch');
```

### chroma.hcl
#### (hue, chroma, lightness)

You can use **hcl** instead of Lch. Lightness and hue channels are switched to be more consistent with HSL.

```js
chroma.hcl(130, 40, 80);
chroma(130, 40, 80, 'hcl');
```

### chroma.cmyk
#### (cyan, magenta, yellow, black)

Each between 0 and 1.

```js
chroma.cmyk(0.2, 0.8, 0, 0);
chroma(0.2, 0.8, 0, 0, 'cmyk');
```

### chroma.gl
#### (red, green, blue, [alpha])

**GL** is a variant of RGB(A), with the only difference that the components are normalized to the range of `0..1`.

```js
chroma.gl(0.6, 0, 0.8);
chroma.gl(0.6, 0, 0.8, 0.5);
chroma(0.6, 0, 0.8, 'gl');
```

### chroma.temperature
#### (K)

Returns a color from the [color temperature](http://www.zombieprototypes.com/?p=210) scale. Based on [Neil Bartlett's implementation](https://github.com/neilbartlett/color-temperature).

```js
chroma.temperature(2000); // candle light
chroma.temperature(3500); // sunset
chroma.temperature(6500); // daylight
```

The effective temperature range goes from `0` to about `30000` Kelvin,

```js
f = function(i) {
    return chroma.temperature(i * 30000)
}
```

### chroma.mix
#### (color1, color2, ratio=0.5, mode='lrgb')

Mixes two colors. The mix *ratio* is a value between 0 and 1.

```js
chroma.mix('red', 'blue');
chroma.mix('red', 'blue', 0.25);
chroma.mix('red', 'blue', 0.75);
```

The color mixing produces different results based the color space used for interpolation.

```js
chroma.mix('red', 'blue', 0.5, 'rgb');
chroma.mix('red', 'blue', 0.5, 'hsl');
chroma.mix('red', 'blue', 0.5, 'lab');
chroma.mix('red', 'blue', 0.5, 'lch');
chroma.mix('red', 'blue', 0.5, 'lrgb');
```


### chroma.average
#### (colors, mode='lrgb', weights=[])

Similar to `chroma.mix`, but accepts more than two colors. Simple averaging of R,G,B components and the alpha channel.

```js
colors = ['#ddd', 'yellow', 'red', 'teal'];
chroma.average(colors); // lrgb
chroma.average(colors, 'rgb');
chroma.average(colors, 'lab');
chroma.average(colors, 'lch');
```

Also works with alpha channels.

```js
chroma.average(['red', 'rgba(0,0,0,0.5)']).css();
```

As of version 2.1 you can also provide an array of `weights` to
compute a **weighted average** of colors.

```js
colors = ['#ddd', 'yellow', 'red', 'teal'];
chroma.average(colors, 'lch'); // unweighted
chroma.average(colors, 'lch', [1,1,2,1]);
chroma.average(colors, 'lch', [1.5,0.5,1,2.3]);
```


### chroma.blend
#### (color1, color2, mode)

Blends two colors using RGB channel-wise blend functions. Valid blend modes are `multiply`, `darken`, `lighten`, `screen`, `overlay`, `burn`, and `dodge`.

```js
chroma.blend('4CBBFC', 'EEEE22', 'multiply');
chroma.blend('4CBBFC', 'EEEE22', 'darken');
chroma.blend('4CBBFC', 'EEEE22', 'lighten');
```

### chroma.random
#### ()

Creates a random color by generating a [random hexadecimal string](https://github.com/gka/chroma.js/blob/master/src/generator/random.coffee#L3-L7).

```js
chroma.random();
chroma.random();
chroma.random();
```

### chroma.contrast
#### (color1, color2)

Computes the WCAG contrast ratio between two colors. A minimum contrast of 4.5:1 [is recommended](http://www.w3.org/TR/WCAG20-TECHS/G18.html) to ensure that text is still readable against a background color.

```js
// contrast smaller than 4.5 = too low
chroma.contrast('pink', 'hotpink');
// contrast greater than 4.5 = high enough
chroma.contrast('pink', 'purple');
```

### chroma.distance
#### (color1, color2, mode='lab')

Computes the [Euclidean distance](https://en.wikipedia.org/wiki/Euclidean_distance#Three_dimensions) between two colors in a given color space (default is `Lab`).

```js
chroma.distance('#fff', '#ff0', 'rgb');
chroma.distance('#fff', '#f0f', 'rgb');
chroma.distance('#fff', '#ff0');
chroma.distance('#fff', '#f0f');
```

### chroma.deltaE
#### (reference, sample, L=1, C=1)

Computes [color difference](https://en.wikipedia.org/wiki/Color_difference#CMC_l:c_.281984.29) as developed by the Colour Measurement Committee of the Society of Dyers and Colourists (CMC) in 1984. The implementation is adapted from [Bruce Lindbloom](https://web.archive.org/web/20160306044036/http://www.brucelindbloom.com/javascript/ColorDiff.js). The parameters L and C are weighting factors for lightness and chromaticity.

```js
chroma.deltaE('#ededee', '#edeeed');
chroma.deltaE('#ececee', '#eceeec');
chroma.deltaE('#e9e9ee', '#e9eee9');
chroma.deltaE('#e4e4ee', '#e4eee4');
chroma.deltaE('#e0e0ee', '#e0eee0');

```

### chroma.brewer

chroma.brewer is an map of [ColorBrewer scales](http://colorbrewer2.org/) that are included in chroma.js for convenience. chroma.scale uses the colors to construct.

```js
chroma.brewer.OrRd
```

### chroma.limits
#### (data, mode, n)

A helper function that computes class breaks for you, based on data. It supports the modes _equidistant_ (e), _quantile_ (q), _logarithmic_ (l), and _k-means_ (k). Let's take a few numbers as sample data.

```js
var data = [2.0,3.5,3.6,3.8,3.8,4.1,4.3,4.4,
            4.6,4.9,5.2,5.3,5.4,5.7,5.8,5.9,
            6.2,6.5,6.8,7.2,8];
```

**equidistant** breaks are computed by dividing the total range of the data into _n_ groups of equal size.

```js
chroma.limits(data, 'e', 4);
```

In the **quantile** mode, the input domain is divided by quantile ranges.

```js
chroma.limits(data, 'q', 4);
```

**logarithmic** breaks are equidistant breaks but on a logarithmic scale.

```js
chroma.limits(data, 'l', 4);
```

**k-means** break is using the 1-dimensional [k-means clustering](https://en.wikipedia.org/wiki/K-means_clustering) algorithm to find (roughly) _n_ groups of "similar" values. Note that this k-means implementation does not guarantee to find exactly _n_ groups.

```js
chroma.limits(data, 'k', 4);
```

## color

### color.alpha
#### (a)

Get and set the color opacity using ``color.alpha``.

```js
chroma('red').alpha(0.5);
chroma('rgba(255,0,0,0.35)').alpha();
```

### color.darken
#### (value=1)

Once loaded, chroma.js can change colors. One way we already saw above, you can change the lightness.

```js
chroma('hotpink').darken();
chroma('hotpink').darken(2);
chroma('hotpink').darken(2.6);
```

### color.brighten
#### (value=1)

Similar to `darken`, but the opposite direction

```js
chroma('hotpink').brighten();
chroma('hotpink').brighten(2);
chroma('hotpink').brighten(3);
```

### color.saturate
#### (value=1)

Changes the saturation of a color by manipulating the Lch chromaticity.

```js
chroma('slategray').saturate();
chroma('slategray').saturate(2);
chroma('slategray').saturate(3);
```

### color.desaturate
#### (value=1)

Similar to `saturate`, but the opposite direction.

```js
chroma('hotpink').desaturate();
chroma('hotpink').desaturate(2);
chroma('hotpink').desaturate(3);
```


### color.set
#### (channel, value)

Changes a single channel and returns the result a new `chroma` object.

```js
// change hue to 0 deg (=red)
chroma('skyblue').set('hsl.h', 0);
// set chromaticity to 30
chroma('hotpink').set('lch.c', 30);
```

Relative changes work, too:

```js
// half Lab lightness
chroma('orangered').set('lab.l', '*0.5');
// double Lch saturation
chroma('darkseagreen').set('lch.c', '*2');
```

### color.get
#### (channel)

Returns a single channel value.

```js
chroma('orangered').get('lab.l');
chroma('orangered').get('hsl.l');
chroma('orangered').get('rgb.g');
```

### color.luminance
#### ([lum, mode='rgb'])

If called without arguments color.luminance returns the relative brightness, according to the [WCAG definition](http://www.w3.org/TR/2008/REC-WCAG20-20081211/#relativeluminancedef). Normalized to `0` for darkest black and `1` for lightest white.

```js
chroma('white').luminance();
chroma('aquamarine').luminance();
chroma('hotpink').luminance();
chroma('darkslateblue').luminance();
chroma('black').luminance();
```

chroma.js also allows you to **adjust the luminance** of a color. The source color will be interpolated with black or white until the correct luminance is found.

```js
// set lumincance to 50% for all colors
chroma('white').luminance(0.5);
chroma('aquamarine').luminance(0.5);
chroma('hotpink').luminance(0.5);
chroma('darkslateblue').luminance(0.5);
```

By default, this interpolation is done in RGB, but you can interpolate in different color spaces by passing them as second argument:

```js
chroma('aquamarine').luminance(0.5); // rgb
chroma('aquamarine').luminance(0.5, 'lab');
chroma('aquamarine').luminance(0.5, 'hsl');
```

### color.hex
#### (mode='auto|rgb|rgba')

Finally, chroma.js allows you to output colors in various color spaces and formats. Most often you will want to output the color as hexadecimal string.

```js
chroma('orange').hex()
```

**Note** that as of version 1.4.0 the default mode is "auto" which means that the hex string will include the alpha channel if it's less than 1. If you don't want the alpha channel to be included you must explicitly set the mode to "rgb" now:

```js
chroma('orange').hex();
chroma('orange').alpha(0.5).hex();
chroma('orange').alpha(0.5).hex('rgb');
```

### color.name

Returns the named color. Falls back to hexadecimal RGB string, if the color isn't present.

```js
chroma('#ffa500').name();
chroma('#ffa505').name();
```

### color.css

Returns a `RGB()` or `HSL()` string representation that can be used as CSS-color definition.

```js
chroma('teal').css();
chroma('teal').alpha(0.5).css();
chroma('teal').css('hsl');
```

### color.rgb
#### (round=true)

Returns an array with the `red`, `green`, and `blue` component, each as number within the range `0..255`. Chroma internally stores RGB channels as floats but rounds the numbers before returning them. You can pass `false` to prevent the rounding.

```js
chroma('orange').rgb();
chroma('orange').darken().rgb();
chroma('orange').darken().rgb(false);
```

### color.rgba
#### (round=true)

Just like `color.rgb` but adds the alpha channel to the returned array.

```js
chroma('orange').rgba();
chroma('hsla(20, 100%, 40%, 0.5)').rgba();
```

### color.hsl

Returns an array with the `hue`, `saturation`, and `lightness` component. Hue is the color angle in degree (`0..360`), saturation and lightness are within `0..1`. Note that for hue-less colors (black, white, and grays), the hue component will be NaN.

```js
chroma('orange').hsl();
chroma('white').hsl();
```

### color.hsv

Returns an array with the `hue`, `saturation`, and `value` components. Hue is the color angle in degree (`0..360`), saturation and value are within `0..1`. Note that for hue-less colors (black, white, and grays), the hue component will be NaN.

```js
chroma('orange').hsv();
chroma('white').hsv();
```

### color.hsi

Returns an array with the `hue`, `saturation`, and `intensity` components, each as number between 0 and 255. Note that for hue-less colors (black, white, and grays), the hue component will be NaN.

```js
chroma('orange').hsi();
chroma('white').hsi();
```

### color.lab

Returns an array with the **L**, **a**, and **b** components.

```js
chroma('orange').lab()
```


### color.lch

Returns an array with the **Lightness**, **chroma**, and **hue** components.

```js
chroma('skyblue').lch()
```

### color.hcl

Alias of [lch](#color-lch), but with the components in reverse order.

```js
chroma('skyblue').hcl()
```

### color.num

Returns the numeric representation of the hexadecimal RGB color.

```js
chroma('#000000').num();
chroma('#0000ff').num();
chroma('#00ff00').num();
chroma('#ff0000').num();
```

### color.temperature

Estimate the temperature in Kelvin of any given color, though this makes the only sense for colors from the [temperature gradient](#chroma-temperature) above.

```js
chroma('#ff3300').temperature();
chroma('#ff8a13').temperature();
chroma('#ffe3cd').temperature();
chroma('#cbdbff').temperature();
chroma('#b3ccff').temperature();
```


### color.gl

Like RGB, but in the channel range of `[0..1]` instead of `[0..255]`

```js
chroma('33cc00').gl();
```

### color.clipped

When converting colors from CIELab color spaces to RGB the color channels get clipped to the range of `[0..255]`. Colors outside that range may exist in nature but are not displayable on RGB monitors (such as ultraviolet). you can use color.clipped to test if a color has been clipped or not.

```js
[c = chroma.hcl(50, 40, 20), c.clipped()];
[c = chroma.hcl(50, 40, 40), c.clipped()];
[c = chroma.hcl(50, 40, 60), c.clipped()];
[c = chroma.hcl(50, 40, 80), c.clipped()];
[c = chroma.hcl(50, 40, 100), c.clipped()];
```

As a bonus feature you can access the unclipped RGB components using `color._rgb._unclipped`.

```js
chroma.hcl(50, 40, 100).rgb();
chroma.hcl(50, 40, 100)._rgb._unclipped;
```

## color scales

### chroma.scale
#### (colors=['white','black'])

A color scale, created with `chroma.scale`, is a function that maps numeric values to a color palette. The default scale has the domain `0..1` and goes from white to black.

```js
f = chroma.scale();
f(0.25);
f(0.5);
f(0.75);
```

You can pass an array of colors to `chroma.scale`. Any color that can be read by `chroma()` will work here, too. If you pass more than two colors, they will be evenly distributed along the gradient.


```js
chroma.scale(['yellow', '008ae5']);
chroma.scale(['yellow', 'red', 'black']);
```

### scale.domain
#### (domain)

You can change the input domain to match your specific use case.

```js
// default domain is [0,1]
chroma.scale(['yellow', '008ae5']);
// set domain to [0,100]
chroma.scale(['yellow', '008ae5']).domain([0,100]);
```

You can use the domain to set the exact positions of each color.

```js
// default domain is [0,1]
chroma.scale(['yellow', 'lightgreen', '008ae5'])
    .domain([0,0.25,1]);
```


### scale.mode
#### (mode)

As with `chroma.mix`, the result of the color interpolation will depend on the color mode in which the channels are interpolated. The default mode is `RGB`:

```js
chroma.scale(['yellow', '008ae5']);
```

This is often fine, but sometimes, two-color `RGB` gradients goes through kind of grayish colors, and `Lab` interpolation produces better results:

```js
chroma.scale(['yellow', 'navy']);
chroma.scale(['yellow', 'navy']).mode('lab');
```

Also note how the RGB interpolation can get very dark around the center. You can achieve better results using [linear RGB interpolation](https://www.youtube.com/watch?v=LKnqECcg6Gw):

```js
chroma.scale(['#f00', '#0f0']);
chroma.scale(['#f00', '#0f0']).mode('lrgb');
```

Other useful interpolation modes could be `HSL` or `Lch`, though both tend to produce too saturated / glowing gradients.

```js
chroma.scale(['yellow', 'navy']).mode('lab');
chroma.scale(['yellow', 'navy']).mode('hsl');
chroma.scale(['yellow', 'navy']).mode('lch');
```

### scale.gamma

Gamma-correction can be used to "shift" a scale's center more the the beginning (gamma < 1) or end (gamma > 1), typically used to "even" the lightness gradient. Default is 1.

```js
chroma.scale('YlGn').gamma(0.5);
chroma.scale('YlGn').gamma(1);
chroma.scale('YlGn').gamma(2);
```

### scale.correctLightness

This makes sure the lightness range is spread evenly across a color scale. Especially useful when working with [multi-hue color scales](https://www.vis4.net/blog/2013/09/mastering-multi-hued-color-scales/), where simple gamma correction can't help you very much.

```js
chroma.scale(['black','red','yellow','white']);

chroma.scale(['black','red','yellow','white'])
    .correctLightness();
```

### scale.cache
#### (true|false)

By default `chroma.scale` instances will cache each computed value => color pair. You can turn off the cache by setting

```js
chroma.scale(['yellow', '008ae5']).cache(false);
```

### scale.padding
#### (pad)

Reduces the color range by cutting of a fraction of the gradient on both sides. If you pass a single number, the same padding will be applied to both ends.

```js
chroma.scale('RdYlBu');
chroma.scale('RdYlBu').padding(0.15);
chroma.scale('RdYlBu').padding(0.3);
chroma.scale('RdYlBu').padding(-0.15);
```

Alternatively you can specify the padding for each sides individually by passing an array of two numbers.

```js
chroma.scale('OrRd');
chroma.scale('OrRd').padding([0.2, 0]);
```


### scale.colors
#### (num, format='hex')

You can call `scale.colors(n)` to quickly grab `n` equi-distant colors from a color scale. If called with no arguments, `scale.colors` returns the original array of colors used to create the scale.

```js
chroma.scale('OrRd').colors(5);
chroma.scale(['white', 'black']).colors(12);
```

If you want to return `chroma` instances just pass *null* as `format`.

### scale.classes
#### (numOrArray)

If you want the scale function to return a distinct set of colors instead of a continuous gradient, you can use `scale.classes`. If you pass a number the scale will broken into equi-distant classes:

```js
// continuous
chroma.scale('OrRd');
// class breaks
chroma.scale('OrRd').classes(5);
chroma.scale('OrRd').classes(8);
```

You can also define custom class breaks by passing them as array:

```js
chroma.scale('OrRd').classes([0,0.3,0.55,0.85,1]);
```

### scale.nodata
#### (color)

When you pass a non-numeric value like `null` or `undefined` to a chroma.scale, "#cccccc" is returned as fallback or "no data" color. You can change the no-data color:

```js
chroma.scale('OrRd')(null);
chroma.scale('OrRd')(undefined);
chroma.scale('OrRd').nodata('#eee')(null);
```

### chroma.brewer

chroma.js includes the definitions from [ColorBrewer2.org](http://colorbrewer2.org/). Read more about these colors [in the corresponding paper](http://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.361.6082&rep=rep1&type=pdf) by Mark Harrower and Cynthia A. Brewer.

```js
chroma.scale('YlGnBu');
chroma.scale('Spectral');
```

To reverse the colors you could simply reverse the domain:

```js
chroma.scale('Spectral').domain([1,0]);
```

You can access the colors directly using `chroma.brewer`.

```js
chroma.brewer.OrRd
```

### chroma.bezier
#### (colors)

`chroma.bezier` returns a function that [bezier-interpolates between colors](https://www.vis4.net/blog/posts/mastering-multi-hued-color-scales/) in `Lab` space. The input range of the function is `[0..1]`.

```js
// linear interpolation
chroma.scale(['yellow', 'red', 'black']);
// bezier interpolation
chroma.bezier(['yellow', 'red', 'black']);
```

You can convert an bezier interpolator into a chroma.scale instance

```js
chroma.bezier(['yellow', 'red', 'black'])
    .scale()
    .colors(5);
```

## cubehelix
### chroma.cubehelix
#### (start=300, rotations=-1.5, hue=1, gamma=1, lightness=[0,1])

Dave Green's [cubehelix color scheme](http://www.mrao.cam.ac.uk/~dag/CUBEHELIX/)!!


```js
// use the default helix...
chroma.cubehelix();
// or customize it
chroma.cubehelix()
    .start(200)
    .rotations(-0.5)
    .gamma(0.8)
    .lightness([0.3, 0.8]);
```

### cubehelix.start
#### (hue)

**start** color for [hue rotation](http://en.wikipedia.org/wiki/Hue#/media/File:HueScale.svg), default=`300`

```js
chroma.cubehelix().start(300);
chroma.cubehelix().start(200);
```

### cubehelix.rotations
#### (num)

number (and direction) of hue rotations (e.g. 1=`360°`, 1.5=`540°``), default=-1.5

```js
chroma.cubehelix().rotations(-1.5);
chroma.cubehelix().rotations(0.5);
chroma.cubehelix().rotations(3);
```

### cubehelix.hue
#### (numOrRange)

hue controls how saturated the colour of all hues are. either single value or range, default=1

```js
chroma.cubehelix();
chroma.cubehelix().hue(0.5);
chroma.cubehelix().hue([1,0]);
```

### cubehelix.gamma
#### (factor)

gamma factor can be used to emphasise low or high intensity values, default=1

```js
chroma.cubehelix().gamma(1);
chroma.cubehelix().gamma(0.5);
```

### cubehelix.lightness
#### (range)

lightness range: default: [0,1]  (black -> white)

```js
chroma.cubehelix().lightness([0,1]);
chroma.cubehelix().lightness([1,0]);
chroma.cubehelix().lightness([0.3,0.7]);
```


### cubehelix.scale

You can call `cubehelix.scale()` to use the cube-helix through the `chroma.scale` interface.

```js
chroma.cubehelix()
    .start(200)
    .rotations(-0.35)
    .gamma(0.7)
    .lightness([0.3, 0.8])
  .scale() // convert to chroma.scale
    .correctLightness()
    .colors(5);
```


