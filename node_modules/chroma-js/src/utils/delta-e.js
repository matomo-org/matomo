const Color = require('../Color');
const {sqrt, atan2, abs, cos, PI} = Math;

module.exports = function(a, b, L=1, C=1) {
    // Delta E (CMC)
    // see http://www.brucelindbloom.com/index.html?Eqn_DeltaE_CMC.html
    a = new Color(a);
    b = new Color(b);
    const [L1,a1,b1] = Array.from(a.lab());
    const [L2,a2,b2] = Array.from(b.lab());
    const c1 = sqrt((a1 * a1) + (b1 * b1));
    const c2 = sqrt((a2 * a2) + (b2 * b2));
    const sl = L1 < 16.0 ? 0.511 : (0.040975 * L1) / (1.0 + (0.01765 * L1));
    const sc = ((0.0638 * c1) / (1.0 + (0.0131 * c1))) + 0.638;
    let h1 = c1 < 0.000001 ? 0.0 : (atan2(b1, a1) * 180.0) / PI;
    while (h1 < 0) { h1 += 360; }
    while (h1 >= 360) { h1 -= 360; }
    const t = (h1 >= 164.0) && (h1 <= 345.0) ? (0.56 + abs(0.2 * cos((PI * (h1 + 168.0)) / 180.0))) : (0.36 + abs(0.4 * cos((PI * (h1 + 35.0)) / 180.0)));
    const c4 = c1 * c1 * c1 * c1;
    const f = sqrt(c4 / (c4 + 1900.0));
    const sh = sc * (((f * t) + 1.0) - f);
    const delL = L1 - L2;
    const delC = c1 - c2;
    const delA = a1 - a2;
    const delB = b1 - b2;
    const dH2 = ((delA * delA) + (delB * delB)) - (delC * delC);
    const v1 = delL / (L * sl);
    const v2 = delC / (C * sc);
    const v3 = sh;
    return sqrt((v1 * v1) + (v2 * v2) + (dH2 / (v3 * v3)));
};


