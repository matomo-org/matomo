

###
interpolates between a set of colors uzing a bezier spline
###
bezier = (colors) ->
    colors = (chroma(c) for c in colors)
    if colors.length == 2
        # linear interpolation
        [lab0, lab1] = (c.lab() for c in colors)
        I = (t) ->
            lab = (lab0[i] + t * (lab1[i] - lab0[i]) for i in [0..2])
            chroma.lab lab...
    else if colors.length == 3
        # quadratic bezier interpolation
        [lab0, lab1, lab2] = (c.lab() for c in colors)
        I = (t) ->
            lab = ((1-t)*(1-t) * lab0[i] + 2 * (1-t) * t * lab1[i] + t * t * lab2[i] for i in [0..2])
            chroma.lab lab...
    else if colors.length == 4
        # cubic bezier interpolation
        [lab0, lab1, lab2, lab3] = (c.lab() for c in colors)
        I = (t) ->
            lab = ((1-t)*(1-t)*(1-t) * lab0[i] + 3 * (1-t) * (1-t) * t * lab1[i] + 3 * (1-t) * t * t * lab2[i] + t*t*t * lab3[i] for i in [0..2])
            chroma.lab lab...
    else if colors.length == 5
        I0 = bezier colors[0..2]
        I1 = bezier colors[2..4]
        I = (t) ->
            if t < 0.5
                I0 t*2
            else
                I1 (t-0.5)*2
    I

chroma.interpolate.bezier = bezier