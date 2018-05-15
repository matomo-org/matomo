// Add Page Visibility API support to old browsers by focus/blur hack.
//
// Include this script _before_ Visibility.js.
//
// Note, that this hack doesn’t correctly emulate Page Visibility API:
// when user change focus from browser to another window (browser and your
// page may stay visible), this hack will decide, that you page is hidden.
//
// For Firefox 5–9 it will be better to use MozVisibility hack without
// this issue. See <https://github.com/private-face/mozvisibility>.
;(function (document) {
    "use strict";

    if ( document.visibilityState || document.webkitVisibilityState ) {
         return;
    }

    document.hidden = false;
    document.visibilityState = 'visible';

    var event = null
    var i = 0
    var fireEvent = function () {
        if( document.createEvent ) {
            if ( !event ) {
                event = document.createEvent('HTMLEvents');
                event.initEvent('visibilitychange', true, true);
            }
            document.dispatchEvent(event);
        } else {
            if ( typeof(Visibility) == 'object' ) {
                Visibility._change.call(Visibility, { });
            }
        }
    }

    var onFocus = function () {
        document.hidden = false;
        document.visibilityState = 'visible';
        fireEvent();
    };
    var onBlur  = function () {
        document.hidden = true;
        document.visibilityState = 'hidden';
        fireEvent();
    }

    if ( document.addEventListener ) {
        window.addEventListener('focus', onFocus, true);
        window.addEventListener('blur',  onBlur,  true);
    } else {
        document.attachEvent('onfocusin',  onFocus);
        document.attachEvent('onfocusout', onBlur);
    }
})(document);
