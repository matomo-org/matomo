const Color = require('../Color');
const {type} = require('../utils');
const {pow} = Math;

const EPS = 1e-7;
const MAX_ITER = 20;

Color.prototype.luminance = function(lum) {
    if (lum !== undefined && type(lum) === 'number') {
        if (lum === 0) {
            // return pure black
            return new Color([0,0,0,this._rgb[3]], 'rgb');
        }
        if (lum === 1) {
            // return pure white
            return new Color([255,255,255,this._rgb[3]], 'rgb');
        }
        // compute new color using...
        let cur_lum = this.luminance();
        let mode = 'rgb';
        let max_iter = MAX_ITER;

        const test = (low, high) => {
            const mid = low.interpolate(high, 0.5, mode);
            const lm = mid.luminance();
            if (Math.abs(lum - lm) < EPS || !max_iter--) {
                // close enough
                return mid;
            }
            return lm > lum ? test(low, mid) : test(mid, high);
        }

        const rgb = (cur_lum > lum ? test(new Color([0,0,0]), this) : test(this, new Color([255,255,255]))).rgb();
        return new Color([...rgb,this._rgb[3]]);
    }
    return rgb2luminance(...(this._rgb).slice(0,3));
}


const rgb2luminance = (r,g,b) => {
    // relative luminance
    // see http://www.w3.org/TR/2008/REC-WCAG20-20081211/#relativeluminancedef
    r = luminance_x(r);
    g = luminance_x(g);
    b = luminance_x(b);
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

const luminance_x = (x) => {
    x /= 255;
    return x <= 0.03928 ? x/12.92 : pow((x+0.055)/1.055, 2.4);
}
