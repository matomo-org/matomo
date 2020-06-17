const Color = require('../Color');
const digits = '0123456789abcdef';

const {floor,random} = Math;

module.exports = () => {
    let code = '#';
    for (let i=0; i<6; i++) {
        code += digits.charAt(floor(random() * 16));
    }
    return new Color(code, 'hex');
}
