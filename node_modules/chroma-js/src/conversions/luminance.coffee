
luminance = (r,g,b) ->
    # relative luminance
    # see http://www.w3.org/TR/2008/REC-WCAG20-20081211/#relativeluminancedef
    [r,g,b] = unpack arguments
    r = luminance_x r
    g = luminance_x g
    b = luminance_x b
    0.2126 * r + 0.7152 * g + 0.0722 * b


luminance_x = (x) ->
    x /= 255
    if x <= 0.03928 then x/12.92 else Math.pow((x+0.055)/1.055, 2.4)

