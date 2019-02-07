
rgb2lch = () ->
    [r,g,b] = unpack arguments
    [l,a,b] = rgb2lab r,g,b
    lab2lch l,a,b
