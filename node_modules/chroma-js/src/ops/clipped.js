const Color = require('../Color');

Color.prototype.clipped = function() {
    return this._rgb._clipped || false;
}
