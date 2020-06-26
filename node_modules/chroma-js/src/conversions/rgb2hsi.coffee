
rgb2hsi = () ->
    ###
    borrowed from here:
    http://hummer.stanford.edu/museinfo/doc/examples/humdrum/keyscape2/rgb2hsi.cpp
    ###
    [r,g,b] = unpack arguments
    TWOPI = Math.PI*2
    r /= 255
    g /= 255
    b /= 255
    min = Math.min(r,g,b)
    i = (r+g+b) / 3
    s = 1 - min/i
    if s == 0
        h = 0
    else
        h = ((r-g)+(r-b)) / 2
        h /= Math.sqrt((r-g)*(r-g) + (r-b)*(g-b))
        h = Math.acos(h)
        if b > g
            h = TWOPI - h
        h /= TWOPI
    [h*360,s,i]

