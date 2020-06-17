const type = require('./type');

module.exports = (args) => {
    if (args.length < 2) return null;
    const l = args.length-1;
    if (type(args[l]) == 'string') return args[l].toLowerCase();
    return null;
};
