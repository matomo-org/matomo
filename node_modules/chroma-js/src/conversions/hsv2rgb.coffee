
hsv2rgb = () ->
    [h,s,v] = unpack arguments
    v *= 255
    if s is 0
        r = g = b = v
    else
        h = 0 if h is 360
        h -= 360 if h > 360
        h += 360 if h < 0
        h /= 60
        i = Math.floor h
        f = h - i
        p = v * (1 - s)
        q = v * (1 - s * f)
        t = v * (1 - s * (1 - f))
        switch i
            when 0 then [r,g,b] = [v, t, p]
            when 1 then [r,g,b] = [q, v, p]
            when 2 then [r,g,b] = [p, v, t]
            when 3 then [r,g,b] = [p, q, v]
            when 4 then [r,g,b] = [t, p, v]
            when 5 then [r,g,b] = [v, p, q]
    r = Math.round r
    g = Math.round g
    b = Math.round b
    [r, g, b]
