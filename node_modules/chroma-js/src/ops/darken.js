require('../io/lab');
const Color = require('../Color');
const LAB_CONSTANTS = require('../io/lab/lab-constants');

Color.prototype.darken = function(amount=1) {
	const me = this;
	const lab = me.lab();
	lab[0] -= LAB_CONSTANTS.Kn * amount;
	return new Color(lab, 'lab').alpha(me.alpha(), true);
}

Color.prototype.brighten = function(amount=1) {
	return this.darken(-amount);
}

Color.prototype.darker = Color.prototype.darken;
Color.prototype.brighter = Color.prototype.brighten;
