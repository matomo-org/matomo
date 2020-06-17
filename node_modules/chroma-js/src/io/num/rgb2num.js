const {unpack} = require('../../utils');

const rgb2num = (...args) => {
    const [r,g,b] = unpack(args, 'rgb');
    return (r << 16) + (g << 8) + b;
}

module.exports = rgb2num;
