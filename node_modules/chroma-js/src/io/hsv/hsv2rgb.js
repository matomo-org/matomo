const {unpack} = require('../../utils');
const {floor} = Math;

const hsv2rgb = (...args) => {
    args = unpack(args, 'hsv');
    let [h,s,v] = args;
    let r,g,b;
    v *= 255;
    if (s === 0) {
        r = g = b = v;
    } else {
        if (h === 360) h = 0;
        if (h > 360) h -= 360;
        if (h < 0) h += 360;
        h /= 60;

        const i = floor(h);
        const f = h - i;
        const p = v * (1 - s);
        const q = v * (1 - s * f);
        const t = v * (1 - s * (1 - f));

        switch (i) {
            case 0: [r,g,b] = [v, t, p]; break
            case 1: [r,g,b] = [q, v, p]; break
            case 2: [r,g,b] = [p, v, t]; break
            case 3: [r,g,b] = [p, q, v]; break
            case 4: [r,g,b] = [t, p, v]; break
            case 5: [r,g,b] = [v, p, q]; break
        }
    }
    return [r,g,b,args.length > 3?args[3]:1];
}

module.exports = hsv2rgb;

