
lch2rgb = (l,c,h) ->
    [L,a,b] = lch2lab l,c,h
    [r,g,b] = lab2rgb L,a,b
    [limit(r,0,255), limit(g,0,255), limit(b,0,255)]
