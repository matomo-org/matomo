/**
 * adds a bindGlobal method to Mousetrap that allows you to
 * bind specific keyboard shortcuts that will still work
 * inside a text input field
 *
 * usage:
 * Mousetrap.bindGlobal('ctrl+s', _saveChanges);
 */
/* global Mousetrap:true */
Mousetrap = (function(Mousetrap) {
    var _globalCallbacks = {},
        _originalStopCallback = Mousetrap.stopCallback;

    Mousetrap.stopCallback = function(e, element, combo, sequence) {
        if (_globalCallbacks[combo] || _globalCallbacks[sequence]) {
            return false;
        }

        return _originalStopCallback(e, element, combo);
    };

    Mousetrap.bindGlobal = function(keys, callback, action) {
        Mousetrap.bind(keys, callback, action);

        if (keys instanceof Array) {
            for (var i = 0; i < keys.length; i++) {
                _globalCallbacks[keys[i]] = true;
            }
            return;
        }

        _globalCallbacks[keys] = true;
    };

    return Mousetrap;
}) (Mousetrap);
