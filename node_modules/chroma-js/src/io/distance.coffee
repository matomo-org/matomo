# simple Euclidean distance
chroma.distance = (a, b, mode='lab') ->
    # Delta E (CIE 1976)
    # see http://www.brucelindbloom.com/index.html?Equations.html
    a = new Color a if type(a) in ['string', 'number']
    b = new Color b if type(b) in ['string', 'number']
    l1 = a.get mode
    l2 = b.get mode
    sum_sq = 0
    for i of l1
        d = (l1[i] || 0) - (l2[i] || 0)
        sum_sq += d*d
    Math.sqrt sum_sq
