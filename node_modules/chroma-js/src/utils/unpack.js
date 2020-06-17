const type = require('./type');

module.exports = (args, keyOrder=null) => {
	// if called with more than 3 arguments, we return the arguments
    if (args.length >= 3) return Array.prototype.slice.call(args);
    // with less than 3 args we check if first arg is object
    // and use the keyOrder string to extract and sort properties
	if (type(args[0]) == 'object' && keyOrder) {
		return keyOrder.split('')
			.filter(k => args[0][k] !== undefined)
			.map(k => args[0][k]);
	}
	// otherwise we just return the first argument
	// (which we suppose is an array of args)
    return args[0];
};
