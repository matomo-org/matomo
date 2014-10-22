/**
 * adds a pause and unpause method to Mousetrap
 * this allows you to enable or disable keyboard shortcuts
 * without having to reset Mousetrap and rebind everything
 */
/* global Mousetrap:true */
Mousetrap = (function(Mousetrap) {
    var self = Mousetrap,
        _originalStopCallback = self.stopCallback,
        enabled = true;

    self.stopCallback = function(e, element, combo) {
        if (!enabled) {
            return true;
        }

        return _originalStopCallback(e, element, combo);
    };

    self.pause = function() {
        enabled = false;
    };

    self.unpause = function() {
        enabled = true;
    };

    return self;
}) (Mousetrap);
