
css2rgb = (css) ->
    css = css.toLowerCase()
    # named X11 colors
    if chroma.colors? and chroma.colors[css]
        return hex2rgb chroma.colors[css]
    # rgb(250,20,0)
    if m = css.match /rgb\(\s*(\-?\d+),\s*(\-?\d+)\s*,\s*(\-?\d+)\s*\)/
        rgb = m.slice 1,4
        for i in [0..2]
            rgb[i] = +rgb[i]
        rgb[3] = 1  # default alpha
    # rgba(250,20,0,0.4)
    else if m = css.match /rgba\(\s*(\-?\d+),\s*(\-?\d+)\s*,\s*(\-?\d+)\s*,\s*([01]|[01]?\.\d+)\)/
        rgb = m.slice 1,5
        for i in [0..3]
            rgb[i] = +rgb[i]
    # rgb(100%,0%,0%)
    else if m = css.match /rgb\(\s*(\-?\d+(?:\.\d+)?)%,\s*(\-?\d+(?:\.\d+)?)%\s*,\s*(\-?\d+(?:\.\d+)?)%\s*\)/
        rgb = m.slice 1,4
        for i in [0..2]
            rgb[i] = Math.round rgb[i] * 2.55
        rgb[3] = 1  # default alpha
    # rgba(100%,0%,0%,0.4)
    else if m = css.match /rgba\(\s*(\-?\d+(?:\.\d+)?)%,\s*(\-?\d+(?:\.\d+)?)%\s*,\s*(\-?\d+(?:\.\d+)?)%\s*,\s*([01]|[01]?\.\d+)\)/
        rgb = m.slice 1,5
        for i in [0..2]
            rgb[i] = Math.round rgb[i] * 2.55
        rgb[3] = +rgb[3]
    # hsl(0,100%,50%)
    else if m = css.match /hsl\(\s*(\-?\d+(?:\.\d+)?),\s*(\-?\d+(?:\.\d+)?)%\s*,\s*(\-?\d+(?:\.\d+)?)%\s*\)/
        hsl = m.slice 1,4
        hsl[1] *= 0.01
        hsl[2] *= 0.01
        rgb = hsl2rgb hsl
        rgb[3] = 1
    # hsla(0,100%,50%,0.5)
    else if m = css.match /hsla\(\s*(\-?\d+(?:\.\d+)?),\s*(\-?\d+(?:\.\d+)?)%\s*,\s*(\-?\d+(?:\.\d+)?)%\s*,\s*([01]|[01]?\.\d+)\)/
        hsl = m.slice 1,4
        hsl[1] *= 0.01
        hsl[2] *= 0.01
        rgb = hsl2rgb hsl
        rgb[3] = +m[4]  # default alpha = 1
    rgb
