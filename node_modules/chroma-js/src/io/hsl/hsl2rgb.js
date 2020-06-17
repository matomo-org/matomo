const {unpack} = require('../../utils');
const {round} = Math;

const hsl2rgb = (...args) => {
    args = unpack(args, 'hsl');
    const [h,s,l] = args;
    let r,g,b;
    if (s === 0) {
        r = g = b = l*255;
    } else {
        const t3 = [0,0,0];
        const c = [0,0,0];
        const t2 = l < 0.5 ? l * (1+s) : l+s-l*s;
        const t1 = 2 * l - t2;
        const h_ = h / 360;
        t3[0] = h_ + 1/3;
        t3[1] = h_;
        t3[2] = h_ - 1/3;
        for (let i=0; i<3; i++) {
            if (t3[i] < 0) t3[i] += 1;
            if (t3[i] > 1) t3[i] -= 1;
            if (6 * t3[i] < 1)
                c[i] = t1 + (t2 - t1) * 6 * t3[i];
            else if (2 * t3[i] < 1)
                c[i] = t2;
            else if (3 * t3[i] < 2)
                c[i] = t1 + (t2 - t1) * ((2 / 3) - t3[i]) * 6;
            else
                c[i] = t1;
        }
        [r,g,b] = [round(c[0]*255),round(c[1]*255),round(c[2]*255)];
    }
    if (args.length > 3) {
        // keep alpha channel
        return [r,g,b,args[3]];
    }
    return [r,g,b,1];
}

module.exports = hsl2rgb;
