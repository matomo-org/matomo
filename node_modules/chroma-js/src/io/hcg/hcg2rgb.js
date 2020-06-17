const {unpack} = require('../../utils');
const {floor} = Math;

/*
 * this is basically just HSV with some minor tweaks
 *
 * hue.. [0..360]
 * chroma .. [0..1]
 * grayness .. [0..1]
 */

const hcg2rgb = (...args) => {
    args = unpack(args, 'hcg');
    let [h,c,_g] = args;
    let r,g,b;
    _g = _g * 255;
    const _c = c * 255;
    if (c === 0) {
        r = g = b = _g
    } else {
        if (h === 360) h = 0;
        if (h > 360) h -= 360;
        if (h < 0) h += 360;
        h /= 60;
        const i = floor(h);
        const f = h - i;
        const p = _g * (1 - c);
        const q = p + _c * (1 - f);
        const t = p + _c * f;
        const v = p + _c;
        switch (i) {
            case 0: [r,g,b] = [v, t, p]; break
            case 1: [r,g,b] = [q, v, p]; break
            case 2: [r,g,b] = [p, v, t]; break
            case 3: [r,g,b] = [p, q, v]; break
            case 4: [r,g,b] = [t, p, v]; break
            case 5: [r,g,b] = [v, p, q]; break
        }
    }
    return [r, g, b, args.length > 3 ? args[3] : 1];
}

module.exports = hcg2rgb;
