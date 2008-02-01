/*
 * jQuery blockUI plugin
 * Version 1.33  (09/14/2007)
 * @requires jQuery v1.1.1
 *
 * $Id$
 *
 * Examples at: http://malsup.com/jquery/block/
 * Copyright (c) 2007 M. Alsup
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 */
 (function($) {
/**
 * blockUI provides a mechanism for blocking user interaction with a page (or parts of a page).
 * This can be an effective way to simulate synchronous behavior during ajax operations without
 * locking the browser.  It will prevent user operations for the current page while it is
 * active ane will return the page to normal when it is deactivate.  blockUI accepts the following
 * two optional arguments:
 *
 *   message (String|Element|jQuery): The message to be displayed while the UI is blocked. The message
 *              argument can be a plain text string like "Processing...", an HTML string like
 *              "<h1><img src="busy.gif" /> Please wait...</h1>", a DOM element, or a jQuery object.
 *              The default message is "<h1>Please wait...</h1>"
 *
 *   css (Object):  Object which contains css property/values to override the default styles of
 *              the message.  Use this argument if you wish to override the default
 *              styles.  The css Object should be in a format suitable for the jQuery.css
 *              function.  For example:
 *              $.blockUI({
 *                    backgroundColor: '#ff8',
 *                    border: '5px solid #f00,
 *                    fontWeight: 'bold'
 *              });
 *
 * The default blocking message used when blocking the entire page is "<h1>Please wait...</h1>"
 * but this can be overridden by assigning a value to $.blockUI.defaults.pageMessage in your
 * own code.  For example:
 *
 *      $.blockUI.defaults.pageMessage = "<h1>Bitte Wartezeit</h1>";
 *
 * The default message styling can also be overridden.  For example:
 *
 *      $.extend($.blockUI.defaults.pageMessageCSS, { color: '#00a', backgroundColor: '#0f0' });
 *
 * The default styles work well for simple messages like "Please wait", but for longer messages
 * style overrides may be necessary.
 *
 * @example  $.blockUI();
 * @desc prevent user interaction with the page (and show the default message of 'Please wait...')
 *
 * @example  $.blockUI( { backgroundColor: '#f00', color: '#fff'} );
 * @desc prevent user interaction and override the default styles of the message to use a white on red color scheme
 *
 * @example  $.blockUI('Processing...');
 * @desc prevent user interaction and display the message "Processing..." instead of the default message
 *
 * @name blockUI
 * @param String|jQuery|Element message Message to display while the UI is blocked
 * @param Object css Style object to control look of the message
 * @cat Plugins/blockUI
 */
$.blockUI = function(msg, css, opts) {
    $.blockUI.impl.install(window, msg, css, opts);
};

// expose version number so other plugins can interogate
$.blockUI.version = 1.33;

/**
 * unblockUI removes the UI block that was put in place by blockUI
 *
 * @example  $.unblockUI();
 * @desc unblocks the page
 *
 * @name unblockUI
 * @cat Plugins/blockUI
 */
$.unblockUI = function(opts) {
    $.blockUI.impl.remove(window, opts);
};

/**
 * Blocks user interaction with the selected elements.  (Hat tip: Much of
 * this logic comes from Brandon Aaron's bgiframe plugin.  Thanks, Brandon!)
 * By default, no message is displayed when blocking elements.
 *
 * @example  $('div.special').block();
 * @desc prevent user interaction with all div elements with the 'special' class.
 *
 * @example  $('div.special').block('Please wait');
 * @desc prevent user interaction with all div elements with the 'special' class
 * and show a message over the blocked content.
 *
 * @name block
 * @type jQuery
 * @param String|jQuery|Element message Message to display while the element is blocked
 * @param Object css Style object to control look of the message
 * @cat Plugins/blockUI
 */
$.fn.block = function(msg, css, opts) {
    return this.each(function() {
		if (!this.$pos_checked) {
            if ($.css(this,"position") == 'static')
                this.style.position = 'relative';
            if ($.browser.msie) this.style.zoom = 1; // force 'hasLayout' in IE
            this.$pos_checked = 1;
        }
        $.blockUI.impl.install(this, msg, css, opts);
    });
};

/**
 * Unblocks content that was blocked by "block()"
 *
 * @example  $('div.special').unblock();
 * @desc unblocks all div elements with the 'special' class.
 *
 * @name unblock
 * @type jQuery
 * @cat Plugins/blockUI
 */
$.fn.unblock = function(opts) {
    return this.each(function() {
        $.blockUI.impl.remove(this, opts);
    });
};

/**
 * displays the first matched element in a "display box" above a page overlay.
 *
 * @example  $('#myImage').displayBox();
 * @desc displays "myImage" element in a box
 *
 * @name displayBox
 * @type jQuery
 * @cat Plugins/blockUI
 */
$.fn.displayBox = function(css, fn, isFlash) {
    var msg = this[0];
    if (!msg) return;
    var $msg = $(msg);
    css = css || {};

    var w = $msg.width()  || $msg.attr('width')  || css.width  || $.blockUI.defaults.displayBoxCSS.width;
    var h = $msg.height() || $msg.attr('height') || css.height || $.blockUI.defaults.displayBoxCSS.height ;
    if (w[w.length-1] == '%') {
        var ww = document.documentElement.clientWidth || document.body.clientWidth;
        w = parseInt(w) || 100;
        w = (w * ww) / 100;
    }
    if (h[h.length-1] == '%') {
        var hh = document.documentElement.clientHeight || document.body.clientHeight;
        h = parseInt(h) || 100;
        h = (h * hh) / 100;
    }

    var ml = '-' + parseInt(w)/2 + 'px';
    var mt = '-' + parseInt(h)/2 + 'px';

    // supress opacity on overlay if displaying flash content on mac/ff platform
    var ua = navigator.userAgent.toLowerCase();
    var opts = {
        displayMode: fn || 1,
        noalpha: isFlash && /mac/.test(ua) && /firefox/.test(ua)
    };

    $.blockUI.impl.install(window, msg, { width: w, height: h, marginTop: mt, marginLeft: ml }, opts);
};


// override these in your code to change the default messages and styles
$.blockUI.defaults = {
    // the message displayed when blocking the entire page
    pageMessage:    '<h1>Please wait...</h1>',
    // the message displayed when blocking an element
    elementMessage: '', // none
    // styles for the overlay iframe
    overlayCSS:  { backgroundColor: '#fff', opacity: '0.5' },
    // styles for the message when blocking the entire page
    pageMessageCSS:    { width:'250px', margin:'-50px 0 0 -125px', top:'50%', left:'50%', textAlign:'center', color:'#000', backgroundColor:'#fff', border:'3px solid #aaa' },
    // styles for the message when blocking an element
    elementMessageCSS: { width:'250px', padding:'10px', textAlign:'center', backgroundColor:'#fff'},
    // styles for the displayBox
    displayBoxCSS: { width: '400px', height: '400px', top:'50%', left:'50%' },
    // allow body element to be stetched in ie6
    ie6Stretch: 1,
    // supress tab nav from leaving blocking content?
    allowTabToLeave: 0,
    // Title attribute for overlay when using displayBox
    closeMessage: 'Click to close',
    // use fadeOut effect when unblocking (can be overridden on unblock call)
    fadeOut:  1,
    // fadeOut transition time in millis
    fadeTime: 400
};

// the gory details
$.blockUI.impl = {
    box: null,
    boxCallback: null,
    pageBlock: null,
    pageBlockEls: [],
    op8: window.opera && window.opera.version() < 9,
    ie6: $.browser.msie && /MSIE 6.0/.test(navigator.userAgent),
    install: function(el, msg, css, opts) {
        opts = opts || {};
        this.boxCallback = typeof opts.displayMode == 'function' ? opts.displayMode : null;
        this.box = opts.displayMode ? msg : null;
        var full = (el == window);

        // use logical settings for opacity support based on browser but allow overrides via opts arg
        var noalpha = this.op8 || $.browser.mozilla && /Linux/.test(navigator.platform);
        if (typeof opts.alphaOverride != 'undefined')
            noalpha = opts.alphaOverride == 0 ? 1 : 0;

        if (full && this.pageBlock) this.remove(window, {fadeOut:0});
        // check to see if we were only passed the css object (a literal)
        if (msg && typeof msg == 'object' && !msg.jquery && !msg.nodeType) {
            css = msg;
            msg = null;
        }
        msg = msg ? (msg.nodeType ? $(msg) : msg) : full ? $.blockUI.defaults.pageMessage : $.blockUI.defaults.elementMessage;
        if (opts.displayMode)
            var basecss = jQuery.extend({}, $.blockUI.defaults.displayBoxCSS);
        else
            var basecss = jQuery.extend({}, full ? $.blockUI.defaults.pageMessageCSS : $.blockUI.defaults.elementMessageCSS);
        css = jQuery.extend(basecss, css || {});
        var f = ($.browser.msie) ? $('<iframe class="blockUI" style="z-index:1000;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="javascript:false;"></iframe>')
                                 : $('<div class="blockUI" style="display:none"></div>');
        var w = $('<div class="blockUI" style="z-index:1001;cursor:wait;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>');
        var m = full ? $('<div class="blockUI blockMsg" style="z-index:1002;cursor:wait;padding:0;position:fixed"></div>')
                     : $('<div class="blockUI" style="display:none;z-index:1002;cursor:wait;position:absolute"></div>');
        w.css('position', full ? 'fixed' : 'absolute');
        if (msg) m.css(css);
        if (!noalpha) w.css($.blockUI.defaults.overlayCSS);
        if (this.op8) w.css({ width:''+el.clientWidth,height:''+el.clientHeight }); // lame
        if ($.browser.msie) f.css('opacity','0.0');

        $([f[0],w[0],m[0]]).appendTo(full ? 'body' : el);

        // ie7 must use absolute positioning in quirks mode and to account for activex issues (when scrolling)
        var expr = $.browser.msie && (!$.boxModel || $('object,embed', full ? null : el).length > 0);
        if (this.ie6 || expr) {
            // stretch content area if it's short
            if (full && $.blockUI.defaults.ie6Stretch && $.boxModel)
                $('html,body').css('height','100%');

            // fix ie6 problem when blocked element has a border width
            if ((this.ie6 || !$.boxModel) && !full) {
                var t = this.sz(el,'borderTopWidth'), l = this.sz(el,'borderLeftWidth');
                var fixT = t ? '(0 - '+t+')' : 0;
                var fixL = l ? '(0 - '+l+')' : 0;
            }

            // simulate fixed position
            $.each([f,w,m], function(i,o) {
                var s = o[0].style;
                s.position = 'absolute';
                if (i < 2) {
                    full ? s.setExpression('height','document.body.scrollHeight > document.body.offsetHeight ? document.body.scrollHeight : document.body.offsetHeight + "px"')
                         : s.setExpression('height','this.parentNode.offsetHeight + "px"');
                    full ? s.setExpression('width','jQuery.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"')
                         : s.setExpression('width','this.parentNode.offsetWidth + "px"');
                    if (fixL) s.setExpression('left', fixL);
                    if (fixT) s.setExpression('top', fixT);
                }
                else {
                    if (full) s.setExpression('top','(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"');
                    s.marginTop = 0;
                }
            });
        }
        if (opts.displayMode) {
            w.css('cursor','default').attr('title', $.blockUI.defaults.closeMessage);
            m.css('cursor','default');
            $([f[0],w[0],m[0]]).removeClass('blockUI').addClass('displayBox');
            $().click($.blockUI.impl.boxHandler).bind('keypress', $.blockUI.impl.boxHandler);
        }
        else
            this.bind(1, el);
        m.append(msg).show();
        if (msg.jquery) msg.show();
        if (opts.displayMode) return;
        if (full) {
            this.pageBlock = m[0];
            this.pageBlockEls = $(':input:enabled:visible',this.pageBlock);
            setTimeout(this.focus, 20);
        }
        else this.center(m[0]);
    },
    remove: function(el, opts) {
        var o = $.extend({}, $.blockUI.defaults, opts);
        this.bind(0, el);
        var full = el == window;
        var els = full ? $('body').children().filter('.blockUI') : $('.blockUI', el);
        if (full) this.pageBlock = this.pageBlockEls = null;

        if (o.fadeOut) {
            els.fadeOut(o.fadeTime, function() {
                if (this.parentNode) this.parentNode.removeChild(this);
            });
        }
        else els.remove();
    },
    boxRemove: function(el) {
        $().unbind('click',$.blockUI.impl.boxHandler).unbind('keypress', $.blockUI.impl.boxHandler);
        if (this.boxCallback)
            this.boxCallback(this.box);
        $('body .displayBox').hide().remove();
    },
    // event handler to suppress keyboard/mouse events when blocking
    handler: function(e) {
        if (e.keyCode && e.keyCode == 9) {
            if ($.blockUI.impl.pageBlock && !$.blockUI.defaults.allowTabToLeave) {
                var els = $.blockUI.impl.pageBlockEls;
                var fwd = !e.shiftKey && e.target == els[els.length-1];
                var back = e.shiftKey && e.target == els[0];
                if (fwd || back) {
                    setTimeout(function(){$.blockUI.impl.focus(back)},10);
                    return false;
                }
            }
        }
        if ($(e.target).parents('div.blockMsg').length > 0)
            return true;
        return $(e.target).parents().children().filter('div.blockUI').length == 0;
    },
    boxHandler: function(e) {
        if ((e.keyCode && e.keyCode == 27) || (e.type == 'click' && $(e.target).parents('div.blockMsg').length == 0))
            $.blockUI.impl.boxRemove();
        return true;
    },
    // bind/unbind the handler
    bind: function(b, el) {
        var full = el == window;
        // don't bother unbinding if there is nothing to unbind
        if (!b && (full && !this.pageBlock || !full && !el.$blocked)) return;
        if (!full) el.$blocked = b;
        var $e = $(el).find('a,:input');
        $.each(['mousedown','mouseup','keydown','keypress','click'], function(i,o) {
            $e[b?'bind':'unbind'](o, $.blockUI.impl.handler);
        });
    },
    focus: function(back) {
        if (!$.blockUI.impl.pageBlockEls) return;
        var e = $.blockUI.impl.pageBlockEls[back===true ? $.blockUI.impl.pageBlockEls.length-1 : 0];
        if (e) e.focus();
    },
    center: function(el) {
		var p = el.parentNode, s = el.style;
        var l = ((p.offsetWidth - el.offsetWidth)/2) - this.sz(p,'borderLeftWidth');
        var t = ((p.offsetHeight - el.offsetHeight)/2) - this.sz(p,'borderTopWidth');
        s.left = l > 0 ? (l+'px') : '0';
        s.top  = t > 0 ? (t+'px') : '0';
    },
    sz: function(el, p) { return parseInt($.css(el,p))||0; }
};

})(jQuery);
