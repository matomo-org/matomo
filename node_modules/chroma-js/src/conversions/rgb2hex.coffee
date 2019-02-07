
rgb2hex = () ->
    [r,g,b] = unpack arguments
    u = r << 16 | g << 8 | b
    str = "000000" + u.toString(16) #.toUpperCase()
    "#" + str.substr(str.length - 6)
