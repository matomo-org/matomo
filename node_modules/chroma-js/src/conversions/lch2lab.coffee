
lch2lab = () ->
    ###
    Convert from a qualitative parameter h and a quantitative parameter l to a 24-bit pixel. These formulas were invented by David Dalrymple to obtain maximum contrast without going out of gamut if the parameters are in the range 0-1.
    A saturation multiplier was added by Gregor Aisch
    ###
    [l,c,h] = unpack arguments
    h = h * Math.PI / 180
    [l, Math.cos(h) * c, Math.sin(h) * c]

