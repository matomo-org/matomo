
lab2lch = () ->
    [l, a, b] = unpack arguments
    c = Math.sqrt(a * a + b * b)
    h = Math.atan2(b, a) / Math.PI * 180
    [l, c, h]

