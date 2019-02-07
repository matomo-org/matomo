
rgb2hsl = (r,g,b) ->
    if r != undefined and r.length >= 3
        [r,g,b] = r
    r /= 255
    g /= 255
    b /= 255

    min = Math.min(r, g, b)
    max = Math.max(r, g, b)

    l = (max + min) / 2

    if max == min
        s = 0
        h = Number.NaN
    else
        s = if l < 0.5 then (max - min) / (max + min) else (max - min) / (2 - max - min)

    if r == max then h = (g - b) / (max - min)
    else if (g == max) then h = 2 + (b - r) / (max - min)
    else if (b == max) then h = 4 + (r - g) / (max - min)

    h *= 60;
    h += 360 if h < 0
    [h,s,l]
