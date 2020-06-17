//
// interpolates between a set of colors uzing a bezier spline
//

// @requires utils lab
const Color = require('../Color');
require('../io/lab');
const scale = require('./scale');

const bezier = function(colors) {
    let I, lab0, lab1, lab2;
    colors = colors.map(c => new Color(c));
    if (colors.length === 2) {
        // linear interpolation
        [lab0, lab1] = colors.map(c => c.lab());
        I = function(t) {
            const lab = ([0, 1, 2].map((i) => lab0[i] + (t * (lab1[i] - lab0[i]))));
            return new Color(lab, 'lab');
        };
    } else if (colors.length === 3) {
        // quadratic bezier interpolation
        [lab0, lab1, lab2] = colors.map(c => c.lab());
        I = function(t) {
            const lab = ([0, 1, 2].map((i) => ((1-t)*(1-t) * lab0[i]) + (2 * (1-t) * t * lab1[i]) + (t * t * lab2[i])));
            return new Color(lab, 'lab');
        };
    } else if (colors.length === 4) {
        // cubic bezier interpolation
        let lab3;
        [lab0, lab1, lab2, lab3] = colors.map(c => c.lab());
        I = function(t) {
            const lab = ([0, 1, 2].map((i) => ((1-t)*(1-t)*(1-t) * lab0[i]) + (3 * (1-t) * (1-t) * t * lab1[i]) + (3 * (1-t) * t * t * lab2[i]) + (t*t*t * lab3[i])));
            return new Color(lab, 'lab');
        };
    } else if (colors.length === 5) {
        const I0 = bezier(colors.slice(0, 3));
        const I1 = bezier(colors.slice(2, 5));
        I = function(t) {
            if (t < 0.5) {
                return I0(t*2);
            } else {
                return I1((t-0.5)*2);
            }
        };
    }
    return I;
};

module.exports = (colors) => {
    const f = bezier(colors);
    f.scale = () => scale(f);
    return f;
}
