const Color = require('../Color');
const {type} = require('../utils');

Color.prototype.set = function(mc, value, mutate=false) {
    const [mode,channel] = mc.split('.');
    const src = this[mode]();
    if (channel) {
        const i = mode.indexOf(channel);
        if (i > -1) {
            if (type(value) == 'string') {
                switch(value.charAt(0)) {
                    case '+': src[i] += +value; break;
                    case '-': src[i] += +value; break;
                    case '*': src[i] *= +(value.substr(1)); break;
                    case '/': src[i] /= +(value.substr(1)); break;
                    default: src[i] = +value;
                }
            } else if (type(value) === 'number') {
                src[i] = value;
            } else {
                throw new Error(`unsupported value for Color.set`);
            }
            const out = new Color(src, mode);
            if (mutate) {
                this._rgb = out._rgb;
                return this;
            }
            return out;
        }
        throw new Error(`unknown channel ${channel} in mode ${mode}`);
    } else {
        return src;
    }
}
