/**
 * @author trixta
 * @version 1.2
 */
(function($){

    var mwheelI = {
            pos: [-260, -260]
        },
        minDif 	= 3,
        doc 	= document,
        root 	= doc.documentElement,
        body 	= doc.body,
        longDelay, shortDelay
        ;

    function unsetPos(){
        if(this === mwheelI.elem){
            mwheelI.pos = [-260, -260];
            mwheelI.elem = false;
            minDif = 3;
        }
    }

    $.event.special.mwheelIntent = {
        setup: function(){
            var jElm = $(this).bind('mousewheel', $.event.special.mwheelIntent.handler);
            if( this !== doc && this !== root && this !== body ){
                jElm.bind('mouseleave', unsetPos);
            }
            jElm = null;
            return true;
        },
        teardown: function(){
            $(this)
                .unbind('mousewheel', $.event.special.mwheelIntent.handler)
                .unbind('mouseleave', unsetPos)
            ;
            return true;
        },
        handler: function(e, d){
            var pos = [e.clientX, e.clientY];
            if( this === mwheelI.elem || Math.abs(mwheelI.pos[0] - pos[0]) > minDif || Math.abs(mwheelI.pos[1] - pos[1]) > minDif ){
                mwheelI.elem = this;
                mwheelI.pos = pos;
                minDif = 250;

                clearTimeout(shortDelay);
                shortDelay = setTimeout(function(){
                    minDif = 10;
                }, 200);
                clearTimeout(longDelay);
                longDelay = setTimeout(function(){
                    minDif = 3;
                }, 1500);
                e = $.extend({}, e, {type: 'mwheelIntent'});
                return $.event.dispatch.apply(this, arguments);
            }
        }
    };
    $.fn.extend({
        mwheelIntent: function(fn) {
            return fn ? this.bind("mwheelIntent", fn) : this.trigger("mwheelIntent");
        },

        unmwheelIntent: function(fn) {
            return this.unbind("mwheelIntent", fn);
        }
    });

    $(function(){
        body = doc.body;
        //assume that document is always scrollable, doesn't hurt if not
        $(doc).bind('mwheelIntent.mwheelIntentDefault', $.noop);
    });
})(jQuery);
