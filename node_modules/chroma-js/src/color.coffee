###*
    chroma.js

    Copyright (c) 2011-2013, Gregor Aisch
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this
      list of conditions and the following disclaimer.

    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.

    * The name Gregor Aisch may not be used to endorse or promote products
      derived from this software without specific prior written permission.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
    AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
    IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    DISCLAIMED. IN NO EVENT SHALL GREGOR AISCH OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
    INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
    BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
    DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
    OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
    NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
    EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

    @source: https://github.com/gka/chroma.js
###


class Color

    constructor: () ->
        me = @

        args = []
        for arg in arguments
            args.push arg if arg?

        if args.length == 0  # Color()
            [x,y,z,a,m] = [255,0,255,1,'rgb']

        else if type(args[0]) == "array"  # Color([255,0,0], 'rgb')
            # unpack array args
            if args[0].length == 3
                [x,y,z] = args[0]
                a = 1
            else if args[0].length == 4
                [x,y,z,a] = args[0]
            else
                throw 'unknown input argument'
            m = args[1] ? 'rgb'

        else if type(args[0]) == "string"  # Color('#ff0000')
            # named color, hex color, css color
            x = args[0]
            m = 'hex'

        else if type(args[0]) == "object"   # Color(Color)
            [x,y,z,a] = args[0]._rgb
            m = 'rgb'

        else if args.length >= 3
            x = args[0]
            y = args[1]
            z = args[2]

        if args.length == 3
            m = 'rgb'
            a = 1

        else if args.length == 4
            if type(args[3]) == "string"
                m = args[3]
                a = 1
            else if type(args[3]) == "number"
                m = 'rgb'
                a = args[3]

        else if args.length == 5
            a = args[3]
            m = args[4]

        a ?= 1

        # create color
        if m == 'rgb'
            me._rgb = [x,y,z,a]
        else if m == 'gl'
            me._rgb = [x*255,y*255,z*255,a]
        else if m == 'hsl'
            me._rgb = hsl2rgb x,y,z
            me._rgb[3] = a
        else if m == 'hsv'
            me._rgb = hsv2rgb x,y,z
            me._rgb[3] = a
        else if m == 'hex'
            me._rgb = hex2rgb x
        else if m == 'lab'
            me._rgb = lab2rgb x,y,z
            me._rgb[3] = a
        else if m == 'lch'
            me._rgb = lch2rgb x,y,z
            me._rgb[3] = a
        else if m == 'hsi'
            me._rgb = hsi2rgb x,y,z
            me._rgb[3] = a

        me_rgb = clip_rgb me._rgb

    rgb: ->
        @_rgb.slice 0,3

    rgba: ->
        @_rgb

    hex: ->
        rgb2hex @_rgb

    toString: ->
        @name()

    hsl: ->
        rgb2hsl @_rgb

    hsv: ->
        rgb2hsv @_rgb

    lab: ->
        rgb2lab @_rgb

    lch: ->
        rgb2lch @_rgb

    hsi: ->
        rgb2hsi @_rgb

    gl: ->
        [@_rgb[0]/255, @_rgb[1]/255, @_rgb[2]/255, @_rgb[3]]

    luminance: (lum, mode='rgb') ->
        return luminance @_rgb if !arguments.length
        # set luminance
        if lum == 0 then @_rgb = [0,0,0,@_rgb[3]]
        if lum == 1 then @_rgb = [255,255,255,@_rgb[3]]
        cur_lum = luminance @_rgb
        eps = 1e-7
        max_iter = 20
        test = (l,h) ->
            m = l.interpolate(0.5, h, mode)
            lm = m.luminance()
            if Math.abs(lum - lm) < eps or not max_iter--
                return m
            if lm > lum
                return test(l, m)
            return test(m, h)
        @_rgb = (if cur_lum > lum then test(new Color('black'), @) else test(@, new Color('white'))).rgba()
        @

    name: ->
        h = @hex()
        for k of chroma.colors
            if h == chroma.colors[k]
                return k
        h

    alpha: (alpha) ->
        if arguments.length
            @_rgb[3] = alpha
            return @
        @_rgb[3]

    css: (mode='rgb') ->
        me = @
        rgb = me._rgb
        if mode.length == 3 and rgb[3] < 1
            mode += 'a'
        if mode == 'rgb'
            mode+'('+rgb.slice(0,3).map(Math.round).join(',')+')'
        else if mode == 'rgba'
            mode+'('+rgb.slice(0,3).map(Math.round).join(',')+','+rgb[3]+')'
        else if mode == 'hsl' or mode == 'hsla'
            hsl = me.hsl()
            rnd = (a) -> Math.round(a*100)/100
            hsl[0] = rnd(hsl[0])
            hsl[1] = rnd(hsl[1]*100) + '%'
            hsl[2] = rnd(hsl[2]*100) + '%'
            if mode.length == 4
                hsl[3] = rgb[3]
            mode + '(' + hsl.join(',') + ')'

    interpolate: (f, col, m) ->
        ###
        interpolates between colors
        f = 0 --> me
        f = 1 --> col
        ###
        me = @
        m ?= 'rgb'
        col = new Color(col) if type(col) == "string"

        if m == 'hsl' or m == 'hsv' or m == 'lch' or m == 'hsi'
            if m == 'hsl'
                xyz0 = me.hsl()
                xyz1 = col.hsl()
            else if m == 'hsv'
                xyz0 = me.hsv()
                xyz1 = col.hsv()
            else if m == 'hsi'
                xyz0 = me.hsi()
                xyz1 = col.hsi()
            else if m == 'lch'
                xyz0 = me.lch()
                xyz1 = col.lch()

            if m.substr(0, 1) == 'h'
                [hue0, sat0, lbv0] = xyz0
                [hue1, sat1, lbv1] = xyz1
            else
                [lbv0, sat0, hue0] = xyz0
                [lbv1, sat1, hue1] = xyz1


            if not isNaN(hue0) and not isNaN(hue1)
                if hue1 > hue0 and hue1 - hue0 > 180
                    dh = hue1-(hue0+360)
                else if hue1 < hue0 and hue0 - hue1 > 180
                    dh = hue1+360-hue0
                else
                    dh = hue1 - hue0
                hue = hue0+f*dh
            else if not isNaN(hue0)
                hue = hue0
                sat = sat0 if (lbv1 == 1 or lbv1 == 0) and m != 'hsv'
            else if not isNaN(hue1)
                hue = hue1
                sat = sat1 if (lbv0 == 1 or lbv0 == 0) and m != 'hsv'
            else
                hue = Number.NaN

            sat ?= sat0 + f*(sat1 - sat0)
            lbv = lbv0 + f*(lbv1-lbv0)

            if m.substr(0, 1) == 'h'
                res = new Color hue, sat, lbv, m
            else
                res = new Color lbv, sat, hue, m

        else if m == 'rgb'
            xyz0 = me._rgb
            xyz1 = col._rgb
            res = new Color(
                xyz0[0]+f*(xyz1[0]-xyz0[0]),
                xyz0[1] + f*(xyz1[1]-xyz0[1]),
                xyz0[2] + f*(xyz1[2]-xyz0[2]),
                m
            )

        else if m == 'lab'
            xyz0 = me.lab()
            xyz1 = col.lab()
            res = new Color(
                xyz0[0]+f*(xyz1[0]-xyz0[0]),
                xyz0[1] + f*(xyz1[1]-xyz0[1]),
                xyz0[2] + f*(xyz1[2]-xyz0[2]),
                m
            )
        else
            throw "color mode "+m+" is not supported"
        # interpolate alpha at last
        res.alpha me.alpha() + f * (col.alpha() - me.alpha())
        res

    premultiply: ->
        rgb = @rgb()
        a = @alpha()
        chroma(rgb[0]*a, rgb[1]*a, rgb[2]*a, a)

    darken: (amount=20) ->
        me = @
        lch = me.lch()
        lch[0] -= amount
        chroma.lch(lch).alpha(me.alpha())

    darker: (amount) ->
        @darken amount

    brighten: (amount=20) ->
        @darken -amount

    brighter: (amount) ->
        @brighten amount

    saturate: (amount=20) ->
        me = @
        lch = me.lch()
        lch[1] += amount
        chroma.lch(lch).alpha(me.alpha())

    desaturate: (amount=20) ->
        @saturate -amount



