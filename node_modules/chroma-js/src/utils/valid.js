const Color = require('../Color');

module.exports = (...args) => {
    try {
        new Color(...args);
        return true;
    } catch (e) {
        return false;
    }
};
