
const chroma = (...args) => {
	return new chroma.Color(...args);
};

chroma.Color = require('./Color');
chroma.version = '@@version'

module.exports = chroma;
