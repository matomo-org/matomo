// minimal multi-purpose interface

// @requires utils color analyze

const chroma = require('../chroma');
const {type} = require('../utils');

const {pow} = Math;

module.exports = function(colors) {

    // constructor
    let _mode = 'rgb';
    let _nacol = chroma('#ccc');
    let _spread = 0;
    // const _fixed = false;
    let _domain = [0, 1];
    let _pos = [];
    let _padding = [0,0];
    let _classes = false;
    let _colors = [];
    let _out = false;
    let _min = 0;
    let _max = 1;
    let _correctLightness = false;
    let _colorCache = {};
    let _useCache = true;
    let _gamma = 1;

    // private methods

    const setColors = function(colors) {
        colors = colors || ['#fff', '#000'];
        if (colors && type(colors) === 'string' && chroma.brewer &&
            chroma.brewer[colors.toLowerCase()]) {
            colors = chroma.brewer[colors.toLowerCase()];
        }
        if (type(colors) === 'array') {
            // handle single color
            if (colors.length === 1) {
                colors = [colors[0], colors[0]];
            }
            // make a copy of the colors
            colors = colors.slice(0);
            // convert to chroma classes
            for (let c=0; c<colors.length; c++) {
                colors[c] = chroma(colors[c]);
            }
            // auto-fill color position
            _pos.length = 0;
            for (let c=0; c<colors.length; c++) {
                _pos.push(c/(colors.length-1));
            }
        }
        resetCache();
        return _colors = colors;
    };

    const getClass = function(value) {
        if (_classes != null) {
            const n = _classes.length-1;
            let i = 0;
            while (i < n && value >= _classes[i]) {
                i++;
            }
            return i-1;
        }
        return 0;
    };

    let tMapLightness = t => t;
    let tMapDomain = t => t;

    // const classifyValue = function(value) {
    //     let val = value;
    //     if (_classes.length > 2) {
    //         const n = _classes.length-1;
    //         const i = getClass(value);
    //         const minc = _classes[0] + ((_classes[1]-_classes[0]) * (0 + (_spread * 0.5)));  // center of 1st class
    //         const maxc = _classes[n-1] + ((_classes[n]-_classes[n-1]) * (1 - (_spread * 0.5)));  // center of last class
    //         val = _min + ((((_classes[i] + ((_classes[i+1] - _classes[i]) * 0.5)) - minc) / (maxc-minc)) * (_max - _min));
    //     }
    //     return val;
    // };

    const getColor = function(val, bypassMap) {
        let col, t;
        if (bypassMap == null) { bypassMap = false; }
        if (isNaN(val) || (val === null)) { return _nacol; }
        if (!bypassMap) {
            if (_classes && (_classes.length > 2)) {
                // find the class
                const c = getClass(val);
                t = c / (_classes.length-2);
            } else if (_max !== _min) {
                // just interpolate between min/max
                t = (val - _min) / (_max - _min);
            } else {
                t = 1;
            }
        } else {
            t = val;
        }

        // domain map
        t = tMapDomain(t);

        if (!bypassMap) {
            t = tMapLightness(t);  // lightness correction
        }

        if (_gamma !== 1) { t = pow(t, _gamma); }

        t = _padding[0] + (t * (1 - _padding[0] - _padding[1]));

        t = Math.min(1, Math.max(0, t));

        const k = Math.floor(t * 10000);

        if (_useCache && _colorCache[k]) {
            col = _colorCache[k];
        } else {
            if (type(_colors) === 'array') {
                //for i in [0.._pos.length-1]
                for (let i=0; i<_pos.length; i++) {
                    const p = _pos[i];
                    if (t <= p) {
                        col = _colors[i];
                        break;
                    }
                    if ((t >= p) && (i === (_pos.length-1))) {
                        col = _colors[i];
                        break;
                    }
                    if (t > p && t < _pos[i+1]) {
                        t = (t-p)/(_pos[i+1]-p);
                        col = chroma.interpolate(_colors[i], _colors[i+1], t, _mode);
                        break;
                    }
                }
            } else if (type(_colors) === 'function') {
                col = _colors(t);
            }
            if (_useCache) { _colorCache[k] = col; }
        }
        return col;
    };

    var resetCache = () => _colorCache = {};

    setColors(colors);

    // public interface

    const f = function(v) {
        const c = chroma(getColor(v));
        if (_out && c[_out]) { return c[_out](); } else { return c; }
    };

    f.classes = function(classes) {
        if (classes != null) {
            if (type(classes) === 'array') {
                _classes = classes;
                _domain = [classes[0], classes[classes.length-1]];
            } else {
                const d = chroma.analyze(_domain);
                if (classes === 0) {
                    _classes = [d.min, d.max];
                } else {
                    _classes = chroma.limits(d, 'e', classes);
                }
            }
            return f;
        }
        return _classes;
    };


    f.domain = function(domain) {
        if (!arguments.length) {
            return _domain;
        }
        _min = domain[0];
        _max = domain[domain.length-1];
        _pos = [];
        const k = _colors.length;
        if ((domain.length === k) && (_min !== _max)) {
            // update positions
            for (let d of Array.from(domain)) {
                _pos.push((d-_min) / (_max-_min));
            }
        } else {
            for (let c=0; c<k; c++) {
                _pos.push(c/(k-1));
            }
            if (domain.length > 2) {
                // set domain map
                const tOut = domain.map((d,i) => i/(domain.length-1));
                const tBreaks = domain.map(d => (d - _min) / (_max - _min));
                if (!tBreaks.every((val, i) => tOut[i] === val)) {
                    tMapDomain = (t) => {
                        if (t <= 0 || t >= 1) return t;
                        let i = 0;
                        while (t >= tBreaks[i+1]) i++;
                        const f = (t - tBreaks[i]) / (tBreaks[i+1] - tBreaks[i]);
                        const out = tOut[i] + f * (tOut[i+1] - tOut[i])
                        return out;
                    }
                }

            }
        }
        _domain = [_min, _max];
        return f;
    };

    f.mode = function(_m) {
        if (!arguments.length) {
            return _mode;
        }
        _mode = _m;
        resetCache();
        return f;
    };

    f.range = function(colors, _pos) {
        setColors(colors, _pos);
        return f;
    };

    f.out = function(_o) {
        _out = _o;
        return f;
    };

    f.spread = function(val) {
        if (!arguments.length) {
            return _spread;
        }
        _spread = val;
        return f;
    };

    f.correctLightness = function(v) {
        if (v == null) { v = true; }
        _correctLightness = v;
        resetCache();
        if (_correctLightness) {
            tMapLightness = function(t) {
                const L0 = getColor(0, true).lab()[0];
                const L1 = getColor(1, true).lab()[0];
                const pol = L0 > L1;
                let L_actual = getColor(t, true).lab()[0];
                const L_ideal = L0 + ((L1 - L0) * t);
                let L_diff = L_actual - L_ideal;
                let t0 = 0;
                let t1 = 1;
                let max_iter = 20;
                while ((Math.abs(L_diff) > 1e-2) && (max_iter-- > 0)) {
                    (function() {
                        if (pol) { L_diff *= -1; }
                        if (L_diff < 0) {
                            t0 = t;
                            t += (t1 - t) * 0.5;
                        } else {
                            t1 = t;
                            t += (t0 - t) * 0.5;
                        }
                        L_actual = getColor(t, true).lab()[0];
                        return L_diff = L_actual - L_ideal;
                    })();
                }
                return t;
            };
        } else {
            tMapLightness = t => t;
        }
        return f;
    };

    f.padding = function(p) {
        if (p != null) {
            if (type(p) === 'number') {
                p = [p,p];
            }
            _padding = p;
            return f;
        } else {
            return _padding;
        }
    };

    f.colors = function(numColors, out) {
        // If no arguments are given, return the original colors that were provided
        if (arguments.length < 2) { out = 'hex'; }
        let result = [];

        if (arguments.length === 0) {
            result = _colors.slice(0);

        } else if (numColors === 1) {
            result = [f(0.5)];

        } else if (numColors > 1) {
            const dm = _domain[0];
            const dd = _domain[1] - dm;
            result = __range__(0, numColors, false).map(i => f( dm + ((i/(numColors-1)) * dd) ));

        } else { // returns all colors based on the defined classes
            colors = [];
            let samples = [];
            if (_classes && (_classes.length > 2)) {
                for (let i = 1, end = _classes.length, asc = 1 <= end; asc ? i < end : i > end; asc ? i++ : i--) {
                    samples.push((_classes[i-1]+_classes[i])*0.5);
                }
            } else {
                samples = _domain;
            }
            result = samples.map(v => f(v));
        }

        if (chroma[out]) {
            result = result.map(c => c[out]());
        }
        return result;
    };

    f.cache = function(c) {
        if (c != null) {
            _useCache = c;
            return f;
        } else {
            return _useCache;
        }
    };

    f.gamma = function(g) {
        if (g != null) {
            _gamma = g;
            return f;
        } else {
            return _gamma;
        }
    };

    f.nodata = function(d) {
        if (d != null) {
            _nacol = chroma(d);
            return f;
        } else {
            return _nacol;
        }
    };

    return f;
};

function __range__(left, right, inclusive) {
  let range = [];
  let ascending = left < right;
  let end = !inclusive ? right : ascending ? right + 1 : right - 1;
  for (let i = left; ascending ? i < end : i > end; ascending ? i++ : i--) {
    range.push(i);
  }
  return range;
}
