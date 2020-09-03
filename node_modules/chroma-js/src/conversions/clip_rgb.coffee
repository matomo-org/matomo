
clip_rgb = (rgb) ->
    for i of rgb
        if i < 3
            rgb[i] = 0 if rgb[i] < 0
            rgb[i] = 255 if rgb[i] > 255
        else if i == 3
            rgb[i] = 0 if rgb[i] < 0
            rgb[i] = 1 if rgb[i] > 1
    rgb
