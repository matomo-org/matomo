const {type} = require('../../utils');

const num2rgb = (num) => {
    if (type(num) == "number" && num >= 0 && num <= 0xFFFFFF) {
        const r = num >> 16;
        const g = (num >> 8) & 0xFF;
        const b = num & 0xFF;
        return [r,g,b,1];
    }
    throw new Error("unknown num color: "+num);
}

module.exports = num2rgb;
