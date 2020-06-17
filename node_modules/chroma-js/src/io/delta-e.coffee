chroma.deltaE = (a, b, L=1, C=1) ->
    # Delta E (CMC)
    # see http://www.brucelindbloom.com/index.html?Eqn_DeltaE_CMC.html
    a = new Color a if type(a) in ['string', 'number']
    b = new Color b if type(b) in ['string', 'number']
    [L1,a1,b1] = a.lab()
    [L2,a2,b2] = b.lab()
    c1 = sqrt(a1 * a1 + b1 * b1)
    c2 = sqrt(a2 * a2 + b2 * b2)
    sl = if L1 < 16.0 then 0.511 else (0.040975 * L1) / (1.0 + 0.01765 * L1)
    sc = (0.0638 * c1) / (1.0 + 0.0131 * c1) + 0.638
    h1 = if c1 < 0.000001 then 0.0 else (atan2(b1, a1) * 180.0) / PI
    h1 += 360 while h1 < 0
    h1 -= 360 while h1 >= 360
    t = if (h1 >= 164.0) && (h1 <= 345.0) then (0.56 + abs(0.2 * cos((PI * (h1 + 168.0)) / 180.0))) else (0.36 + abs(0.4 * cos((PI * (h1 + 35.0)) / 180.0)))
    c4 = c1 * c1 * c1 * c1
    f = sqrt(c4 / (c4 + 1900.0))
    sh = sc * (f * t + 1.0 - f)
    delL = L1 - L2
    delC = c1 - c2
    delA = a1 - a2
    delB = b1 - b2
    dH2 = delA * delA + delB * delB - delC * delC
    v1 = delL / (L * sl)
    v2 = delC / (C * sc)
    v3 = sh
    sqrt(v1 * v1 + v2 * v2 + (dH2 / (v3 * v3)))
    

