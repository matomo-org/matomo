
rgb2hsv = () ->
    [r,g,b] = unpack arguments
    min = Math.min(r, g, b)
    max = Math.max(r, g, b)
    delta = max - min
    v = max / 255.0
    if max == 0
        h = Number.NaN
        s = 0
    else
        s = delta / max
        if r is max then h = (g - b) / delta
        if g is max then h = 2+(b - r) / delta
        if b is max then h = 4+(r - g) / delta
        h *= 60;
        if h < 0 then h += 360
    [h, s, v]
